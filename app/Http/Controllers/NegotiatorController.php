<?php

namespace App\Http\Controllers;

use App\Models\Recommendation;
use App\Models\Simulation;
use App\Services\RevenueSimulationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
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

        return view('modules.negotiator.index', [
            'simulations' => $simulations,
            'selectedSimulation' => $selectedSimulation,
        ]);
    }

    public function store(
        Request $request,
        RevenueSimulationService $simulationService
    ): RedirectResponse {
        $validated = $request->validate([
            'buyer' => ['required', 'string', 'max:255'],
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
            'buyer_message' => [
                'required',
                'string',
                'max:5000',
            ],
            'objective' => [
                'required',
                'string',
                'max:255',
            ],
            'rounds' => [
                'required',
                'integer',
                'in:3,5,7',
            ],
        ]);

        $simulation = Simulation::create([
            'user_id' => $request->user()->id,
            'module' => 'negotiator8',
            'title' => $validated['buyer'].' negotiation',
            'input_data' => $validated,
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            $result = $simulationService->runNegotiation($validated);

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
                'source_module' => 'negotiator8',
                'title' => $recommendation['title']
                    ?? 'Review Negotiator8 recommendation',
                'summary' => $recommendation['summary']
                    ?? ($result['executive_summary'] ?? 'Negotiation completed.'),
                'action_type' => $recommendation['action_type']
                    ?? 'send_negotiation_response',
                'action_payload' => $recommendation['action_payload']
                    ?? [
                        'buyer' => $validated['buyer'],
                        'recommended_price' => $result['recommended_price']
                            ?? $validated['proposed_price'],
                        'recommended_move' => $result['recommended_move']
                            ?? null,
                    ],
                'status' => 'pending',
            ]);

            return redirect()
                ->route('negotiator.index', [
                    'simulation' => $simulation->id,
                ])
                ->with(
                    'success',
                    'Negotiator8 war-game completed successfully.'
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
                ->withErrors([
                    'simulation' => $exception->getMessage(),
                ]);
        }
    }
}