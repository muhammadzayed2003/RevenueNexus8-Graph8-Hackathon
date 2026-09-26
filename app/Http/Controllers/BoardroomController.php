<?php

namespace App\Http\Controllers;

use App\Models\Recommendation;
use App\Models\Simulation;
use App\Services\RevenueSimulationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        return view('modules.boardroom.index', [
            'simulations' => $simulations,
            'selectedSimulation' => $selectedSimulation,
        ]);
    }

    public function store(
        Request $request,
        RevenueSimulationService $simulationService
    ): RedirectResponse {
        $validated = $request->validate([
            'company' => ['required', 'string', 'max:255'],
            'deal_value' => ['required', 'numeric', 'min:0'],
            'stage' => ['required', 'string', 'max:100'],
            'solution' => ['required', 'string', 'max:5000'],
            'objections' => ['nullable', 'string', 'max:5000'],
        ]);

        $simulation = Simulation::create([
            'user_id' => $request->user()->id,
            'module' => 'boardroom8',
            'title' => $validated['company'].' buyer committee',
            'input_data' => $validated,
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            $result = $simulationService->runBoardroom($validated);

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
                'action_payload' => $recommendation['action_payload']
                    ?? [
                        'company' => $validated['company'],
                        'stage' => $validated['stage'],
                    ],
                'status' => 'pending',
            ]);

            return redirect()
                ->route('boardroom.index', [
                    'simulation' => $simulation->id,
                ])
                ->with(
                    'success',
                    'Boardroom8 simulation completed successfully.'
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