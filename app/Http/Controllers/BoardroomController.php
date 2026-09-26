<?php

namespace App\Http\Controllers;

use App\Models\Graph8Record;
use App\Models\Recommendation;
use App\Models\Simulation;
use App\Services\Graph8Service;
use App\Services\RevenueSimulationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class BoardroomController extends Controller
{
    public function index(Request $request): View
    {
        $simulations = Simulation::query()
            ->where('user_id', $request->user()->id)
            ->where('module', 'boardroom8')
            ->latest()
            ->take(10)
            ->get();

        $selectedSimulation = null;

        if ($request->filled('simulation')) {
            $selectedSimulation = Simulation::query()
                ->where('user_id', $request->user()->id)
                ->where('module', 'boardroom8')
                ->findOrFail($request->integer('simulation'));
        }

        $companies = Graph8Record::query()
            ->where('record_type', 'company')
            ->orderByRaw('name IS NULL, name ASC')
            ->get();

        $deals = Graph8Record::query()
            ->where('record_type', 'deal')
            ->orderByRaw('name IS NULL, name ASC')
            ->get();

        return view('modules.boardroom.index', [
            'simulations' => $simulations,
            'selectedSimulation' => $selectedSimulation,
            'companies' => $companies,
            'deals' => $deals,
        ]);
    }

    public function store(
        Request $request,
        RevenueSimulationService $simulationService,
        Graph8Service $graph8Service
    ): RedirectResponse {
        $validated = $request->validate([
            'company_record_id' => [
                'required',
                'integer',
                Rule::exists('graph8_records', 'id')
                    ->where(
                        fn (Builder $query) => $query
                            ->where('record_type', 'company')
                    ),
            ],
            'deal_record_id' => [
                'nullable',
                'integer',
                Rule::exists('graph8_records', 'id')
                    ->where(
                        fn (Builder $query) => $query
                            ->where('record_type', 'deal')
                    ),
            ],
            'deal_value' => [
                'required',
                'numeric',
                'min:0',
            ],
            'stage' => [
                'required',
                'string',
                'max:100',
            ],
            'solution' => [
                'required',
                'string',
                'max:5000',
            ],
            'objections' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        $company = Graph8Record::query()
            ->where('record_type', 'company')
            ->findOrFail($validated['company_record_id']);

        $deal = null;

        if (! empty($validated['deal_record_id'])) {
            $deal = Graph8Record::query()
                ->where('record_type', 'deal')
                ->findOrFail($validated['deal_record_id']);
        }

        try {
            $companyContacts = $graph8Service
                ->fetchCompanyContacts(
                    $company->external_id
                );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('boardroom.index')
                ->withInput()
                ->withErrors([
                    'simulation' => 'Unable to load company contacts from graph8: '
                        .$exception->getMessage(),
                ]);
        }

        $dealContext = [
            'company' => [
                'graph8_id' => $company->external_id,
                'name' => $company->name,
                'data' => $company->payload,
            ],
            'company_contacts' => $companyContacts,
            'company_contact_count' => count($companyContacts),
            'deal' => $deal
                ? [
                    'graph8_id' => $deal->external_id,
                    'name' => $deal->name,
                    'data' => $deal->payload,
                ]
                : null,
            'deal_value' => $validated['deal_value'],
            'stage' => $validated['stage'],
            'solution' => $validated['solution'],
            'objections' => $validated['objections'] ?? null,
        ];

        $simulation = Simulation::create([
            'user_id' => $request->user()->id,
            'module' => 'boardroom8',
            'title' => ($company->name ?? 'graph8 Company')
                .' buyer committee',
            'input_data' => $dealContext,
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            $result = $simulationService
                ->runBoardroom($dealContext);

            $simulation->update([
                'result_data' => $result,
                'status' => 'completed',
                'score' => $result['overall_score'] ?? null,
                'completed_at' => now(),
            ]);

            $recommendation = $result['recommendation'] ?? [];

            Recommendation::create([
                'user_id' => $request->user()->id,
                'simulation_id' => $simulation->id,
                'source_module' => 'boardroom8',
                'title' => $recommendation['title']
                    ?? 'Review Boardroom8 recommendation',
                'summary' => $recommendation['summary']
                    ?? ($result['executive_summary'] ?? 'Simulation completed.'),
                'action_type' => $recommendation['action_type']
                    ?? 'review_deal',
                'action_payload' => [
                    ...($recommendation['action_payload'] ?? []),
                    'graph8_company_id' => $company->external_id,
                    'graph8_deal_id' => $deal?->external_id,
                    'graph8_contact_ids' => collect($companyContacts)
                        ->pluck('id')
                        ->filter()
                        ->values()
                        ->all(),
                ],
                'status' => 'pending',
            ]);

            return redirect()
                ->route('boardroom.index', [
                    'simulation' => $simulation->id,
                ])
                ->with(
                    'success',
                    'Boardroom8 simulation completed using graph8 company and contact data.'
                );
        } catch (Throwable $exception) {
            report($exception);

            $simulation->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'completed_at' => now(),
            ]);

            return redirect()
                ->route('boardroom.index', [
                    'simulation' => $simulation->id,
                ])
                ->withErrors([
                    'simulation' => $exception->getMessage(),
                ]);
        }
    }
}