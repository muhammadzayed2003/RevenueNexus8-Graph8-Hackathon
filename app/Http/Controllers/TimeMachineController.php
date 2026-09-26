<?php

namespace App\Http\Controllers;

use App\Models\Graph8Event;
use App\Models\Recommendation;
use App\Models\Simulation;
use App\Services\RevenueSimulationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class TimeMachineController extends Controller
{
    public function index(Request $request): View
    {
        $simulations = Simulation::query()
            ->where('user_id', $request->user()->id)
            ->where('module', 'time_machine8')
            ->latest()
            ->take(10)
            ->get();

        $selectedSimulation = null;

        if ($request->filled('simulation')) {
            $selectedSimulation = Simulation::query()
                ->where('user_id', $request->user()->id)
                ->where('module', 'time_machine8')
                ->findOrFail($request->integer('simulation'));
        }

        $availableEventTypes = Graph8Event::query()
            ->whereNotNull('event_type')
            ->distinct()
            ->orderBy('event_type')
            ->pluck('event_type');

        $eventCount = Graph8Event::count();

        $latestEvent = Graph8Event::query()
            ->latest('occurred_at')
            ->first();

        return view('modules.time-machine.index', [
            'simulations' => $simulations,
            'selectedSimulation' => $selectedSimulation,
            'availableEventTypes' => $availableEventTypes,
            'eventCount' => $eventCount,
            'latestEvent' => $latestEvent,
        ]);
    }

    public function store(
        Request $request,
        RevenueSimulationService $simulationService
    ): RedirectResponse {
        $validated = $request->validate([
            'period_days' => [
                'required',
                'integer',
                'in:30,90,180,365',
            ],
            'event_type' => [
                'required',
                'string',
                'max:255',
            ],
            'candidate_strategy' => [
                'required',
                'string',
                'max:10000',
            ],
        ]);

        $eventQuery = Graph8Event::query()
            ->where(
                'occurred_at',
                '>=',
                now()->subDays($validated['period_days'])
            );

        if ($validated['event_type'] !== 'all') {
            $eventQuery->where(
                'event_type',
                $validated['event_type']
            );
        }

        $events = $eventQuery
            ->orderBy('occurred_at')
            ->limit(100)
            ->get();

        if ($events->isEmpty()) {
            return redirect()
                ->route('time-machine.index')
                ->withErrors([
                    'events' => 'No graph8 events were found for the selected period and event type.',
                ])
                ->withInput();
        }

        $configuration = [
            'period_days' => $validated['period_days'],
            'event_type' => $validated['event_type'],
            'candidate_strategy' => $validated['candidate_strategy'],
            'event_count' => $events->count(),
        ];

        $eventData = $events
            ->map(function (Graph8Event $event): array {
                return [
                    'database_id' => $event->id,
                    'external_id' => $event->external_id,
                    'event_type' => $event->event_type,
                    'company_id' => $event->company_id,
                    'contact_id' => $event->contact_id,
                    'deal_id' => $event->deal_id,
                    'occurred_at' => $event->occurred_at?->toIso8601String(),
                    'payload' => $event->payload,
                ];
            })
            ->values()
            ->all();

        $simulation = Simulation::create([
            'user_id' => $request->user()->id,
            'module' => 'time_machine8',
            'title' => 'Historical replay — '.$events->count().' events',
            'input_data' => [
                ...$configuration,
                'event_ids' => $events->pluck('id')->all(),
            ],
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            $result = $simulationService->runTimeMachine(
                $configuration,
                $eventData
            );

            $simulation->update([
                'result_data' => $result,
                'status' => 'completed',
                'score' => $result['overall_score'] ?? null,
                'completed_at' => now(),
            ]);

            $events->each(function (Graph8Event $event): void {
                $event->update([
                    'processed' => true,
                    'processed_at' => now(),
                ]);
            });

            $recommendation = $result['recommendation'] ?? [];

            Recommendation::create([
                'user_id' => $request->user()->id,
                'simulation_id' => $simulation->id,
                'source_module' => 'time_machine8',
                'title' => $recommendation['title']
                    ?? 'Review TimeMachine8 recommendation',
                'summary' => $recommendation['summary']
                    ?? ($result['executive_summary'] ?? 'Historical replay completed.'),
                'action_type' => $recommendation['action_type']
                    ?? 'apply_candidate_strategy',
                'action_payload' => $recommendation['action_payload']
                    ?? [
                        'candidate_strategy' => $validated['candidate_strategy'],
                        'event_count' => $events->count(),
                    ],
                'status' => 'pending',
            ]);

            return redirect()
                ->route('time-machine.index', [
                    'simulation' => $simulation->id,
                ])
                ->with(
                    'success',
                    'TimeMachine8 historical replay completed successfully.'
                );
        } catch (Throwable $exception) {
            report($exception);

            $simulation->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'completed_at' => now(),
            ]);

            return redirect()
                ->route('time-machine.index', [
                    'simulation' => $simulation->id,
                ])
                ->withErrors([
                    'simulation' => $exception->getMessage(),
                ]);
        }
    }
}