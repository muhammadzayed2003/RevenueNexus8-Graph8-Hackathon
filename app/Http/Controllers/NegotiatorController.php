<?php

namespace App\Http\Controllers;

use App\Models\Graph8Record;
use App\Models\Recommendation;
use App\Models\Simulation;
use App\Services\Graph8Service;
use App\Services\NegotiationSimulationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class NegotiatorController extends Controller
{
    public function index(Request $request): View
    {
        $simulations = Simulation::query()
            ->where('user_id', $request->user()->id)
            ->where('module', 'negotiator8')
            ->latest()
            ->take(10)
            ->get();

        $selectedSimulation = null;

        if ($request->filled('simulation')) {
            $selectedSimulation = Simulation::query()
                ->where('user_id', $request->user()->id)
                ->where('module', 'negotiator8')
                ->findOrFail($request->integer('simulation'));
        }

        $deals = Graph8Record::query()
            ->where('record_type', 'deal')
            ->whereNotNull('external_id')
            ->orderBy('name')
            ->get();

        return view('modules.negotiator.index', [
            'simulations' => $simulations,
            'selectedSimulation' => $selectedSimulation,
            'deals' => $deals,
        ]);
    }

    public function store(
        Request $request,
        NegotiationSimulationService $simulationService,
        Graph8Service $graph8Service
    ): RedirectResponse {
        $validated = $request->validate([
            'deal_record_id' => [
                'nullable',
                'integer',
                Rule::exists('graph8_records', 'id')
                    ->where('record_type', 'deal'),
            ],
            'buyer' => ['required', 'string', 'max:255'],
            'product_name' => ['required', 'string', 'max:255'],
            'product_details' => ['required', 'string', 'max:10000'],
            'commercial_terms' => ['required', 'string', 'max:5000'],
            'allowed_concessions' => ['required', 'string', 'max:5000'],
            'non_negotiables' => ['nullable', 'string', 'max:5000'],
            'proposed_price' => [
                'required',
                'numeric',
                'min:0',
                'gte:minimum_acceptable',
            ],
            'minimum_acceptable' => [
                'required',
                'numeric',
                'min:0',
            ],
            'buyer_message' => ['required', 'string', 'max:5000'],
            'objective' => ['required', 'string', 'max:255'],
            'rounds' => ['required', 'integer', 'in:3,5,7'],
        ]);

        $dealRecord = null;
        $liveDeal = null;

        if (! empty($validated['deal_record_id'])) {
            $dealRecord = Graph8Record::query()
                ->where('record_type', 'deal')
                ->findOrFail($validated['deal_record_id']);

            try {
                $response = $graph8Service->findDeal(
                    (string) $dealRecord->external_id
                );

                $liveDeal = $response['data'] ?? null;

                if (
                    ! is_array($liveDeal)
                    || $liveDeal === []
                    || array_is_list($liveDeal)
                ) {
                    throw new RuntimeException(
                        'graph8 returned an unexpected deal response.'
                    );
                }
            } catch (Throwable $exception) {
                report($exception);

                return back()
                    ->withInput()
                    ->withErrors([
                        'deal_record_id' =>
                            'Unable to load the selected graph8 deal: '
                            .$exception->getMessage(),
                    ]);
            }
        }

        $allowed = collect(
            preg_split(
                '/\r\n|\r|\n/',
                $validated['allowed_concessions']
            )
        )
            ->map(fn ($line) => trim($line))
            ->filter(fn ($line) => $line !== '')
            ->unique()
            ->values();

        if (
            $allowed->count() === 1
            && strtolower($allowed->first()) === 'none'
        ) {
            $allowed = collect();
        }

        $context = [
            'scenario' => [
                'buyer' => $validated['buyer'],
                'product_name' => $validated['product_name'],
                'product_details' => $validated['product_details'],
                'commercial_terms' => $validated['commercial_terms'],
                'allowed_concessions' => $allowed->all(),
                'non_negotiables' => $validated['non_negotiables'] ?? null,
                'proposed_price' => $validated['proposed_price'],
                'minimum_acceptable' => $validated['minimum_acceptable'],
                'buyer_message' => $validated['buyer_message'],
                'objective' => $validated['objective'],
                'rounds' => (int) $validated['rounds'],
                'currency' => 'USD',
            ],
            'graph8_deal' => $dealRecord
                ? [
                    'external_id' => (string) $dealRecord->external_id,
                    'record_name' => $dealRecord->name,
                    'data' => $liveDeal,
                    'fetched_at' => now()->toIso8601String(),
                ]
                : null,
            'source_notes' => [
                'scenario' => 'User-supplied proposal and constraints',
                'buyer_message' => 'User-provided buyer statement; unverified',
                'graph8_deal' => $dealRecord
                    ? 'Live graph8 API response'
                    : 'No linked CRM deal',
            ],
        ];

        $simulation = Simulation::create([
            'user_id' => $request->user()->id,
            'module' => 'negotiator8',
            'title' => $validated['buyer'].' negotiation',
            'input_data' => [
                ...$validated,
                // Preserve the buyer field used by voice playback.
                'negotiation_context' => $context,
            ],
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            $result = $simulationService->runNegotiation($context);

            DB::transaction(function () use (
                $simulation,
                $result,
                $request,
                $validated,
                $dealRecord
            ): void {
                $simulation->update([
                    'result_data' => $result,
                    'status' => 'completed',
                    'score' => $result['overall_score'],
                    'completed_at' => now(),
                ]);

                Recommendation::create([
                    'user_id' => $request->user()->id,
                    'simulation_id' => $simulation->id,
                    'source_module' => 'negotiator8',
                    'title' => $result['recommendation']['title'],
                    'summary' => $result['recommendation']['summary'],
                    'action_type' => 'review_negotiation',
                    'action_payload' => [
                        'buyer' => $validated['buyer'],
                        'product_name' => $validated['product_name'],
                        'recommended_price' => $result['recommended_price'],
                        'currency' => 'USD',
                        'recommended_move' => $result['recommended_move'],
                        'approved_concessions' =>
                            $result['acceptable_concessions'],
                        'graph8_deal_id' => $dealRecord
                            ? (string) $dealRecord->external_id
                            : null,
                        'requires_human_review' => true,
                        'simulation_only' => true,
                    ],
                    'status' => 'pending',
                ]);
            });

            return redirect()
                ->route('negotiator.index', [
                    'simulation' => $simulation->id,
                ])
                ->with(
                    'success',
                    'Negotiator8 completed using the supplied product and commercial context.'
                );
        } catch (Throwable $exception) {
            report($exception);

            $simulation->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'completed_at' => now(),
            ]);

            return redirect()
                ->route('negotiator.index', [
                    'simulation' => $simulation->id,
                ])
                ->withInput()
                ->withErrors([
                    'simulation' => $exception->getMessage(),
                ]);
        }
    }
}