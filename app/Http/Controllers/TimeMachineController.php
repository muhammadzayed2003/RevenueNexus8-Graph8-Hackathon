<?php

namespace App\Http\Controllers;

use App\Models\Graph8Event;
use App\Models\Recommendation;
use App\Models\Simulation;
use App\Services\Graph8Service;
use App\Services\RevenueSimulationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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
                ->findOrFail(
                    $request->integer('simulation')
                );
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
        RevenueSimulationService $simulationService,
        Graph8Service $graph8Service
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

        try {
            $this->synchronizeLiveDealEvents(
                $graph8Service
            );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('time-machine.index')
                ->withInput()
                ->withErrors([
                    'events' => 'Unable to load live graph8 deal history: '
                        .$exception->getMessage(),
                ]);
        }

        $eventQuery = Graph8Event::query()
            ->where(
                'occurred_at',
                '>=',
                now()->subDays(
                    $validated['period_days']
                )
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
            'candidate_strategy' => $validated[
                'candidate_strategy'
            ],
            'event_count' => $events->count(),
            'data_source' => 'Live graph8 API deal history and snapshots',
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
                    'occurred_at' => $event
                        ->occurred_at
                        ?->toIso8601String(),
                    'payload' => $event->payload,
                ];
            })
            ->values()
            ->all();

        $latestReplayEvent = $events->last();

        $simulation = Simulation::create([
            'user_id' => $request->user()->id,
            'module' => 'time_machine8',
            'title' => 'Historical replay — '
                .$events->count()
                .' events',
            'input_data' => [
                ...$configuration,
                'event_ids' => $events
                    ->pluck('id')
                    ->all(),
                'graph8_deal_ids' => $events
                    ->pluck('deal_id')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all(),
            ],
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            $result = $simulationService
                ->runTimeMachine(
                    $configuration,
                    $eventData
                );

            $simulation->update([
                'result_data' => $result,
                'status' => 'completed',
                'score' => $result[
                    'overall_score'
                ] ?? null,
                'completed_at' => now(),
            ]);

            $events->each(
                function (Graph8Event $event): void {
                    $event->update([
                        'processed' => true,
                        'processed_at' => now(),
                    ]);
                }
            );

            $recommendation = $result[
                'recommendation'
            ] ?? [];

            $actionPayload = $recommendation[
                'action_payload'
            ] ?? [
                'candidate_strategy' => $validated[
                    'candidate_strategy'
                ],
                'event_count' => $events->count(),
            ];

            $actionPayload = [
                ...$actionPayload,
                'graph8_deal_id' => $latestReplayEvent
                    ?->deal_id,
                'graph8_company_id' => $latestReplayEvent
                    ?->company_id,
                'replayed_event_ids' => $events
                    ->pluck('external_id')
                    ->filter()
                    ->values()
                    ->all(),
                'data_source' => 'graph8_api',
            ];

            Recommendation::create([
                'user_id' => $request->user()->id,
                'simulation_id' => $simulation->id,
                'source_module' => 'time_machine8',
                'title' => $recommendation['title']
                    ?? 'Review TimeMachine8 recommendation',
                'summary' => $recommendation['summary']
                    ?? (
                        $result['executive_summary']
                        ?? 'Historical replay completed.'
                    ),
                'action_type' => $recommendation[
                    'action_type'
                ] ?? 'apply_candidate_strategy',
                'action_payload' => $actionPayload,
                'status' => 'pending',
            ]);

            return redirect()
                ->route('time-machine.index', [
                    'simulation' => $simulation->id,
                ])
                ->with(
                    'success',
                    'TimeMachine8 replay completed using live graph8 deal data.'
                );
        } catch (Throwable $exception) {
            report($exception);

            $simulation->update([
                'status' => 'failed',
                'error_message' => $exception
                    ->getMessage(),
                'completed_at' => now(),
            ]);

            return redirect()
                ->route('time-machine.index', [
                    'simulation' => $simulation->id,
                ])
                ->withErrors([
                    'simulation' => $exception
                        ->getMessage(),
                ]);
        }
    }

    private function synchronizeLiveDealEvents(
        Graph8Service $graph8Service
    ): void {
        $page = 1;

        while (true) {
            $response = $graph8Service
                ->fetchDeals([
                    'page' => $page,
                    'limit' => 200,
                ]);

            $deals = $this->extractList(
                $response,
                [
                    'data',
                    'data.items',
                    'deals',
                    'items',
                    'results',
                ]
            );

            if ($deals === []) {
                break;
            }

            foreach ($deals as $deal) {
                if (! is_array($deal)) {
                    continue;
                }

                $dealId = $this->firstValue(
                    $deal,
                    [
                        'id',
                        'deal_id',
                        'uuid',
                    ]
                );

                if (! $dealId) {
                    continue;
                }

                $this->storeDealSnapshot(
                    (string) $dealId,
                    $deal
                );

                $historyResponse = $graph8Service
                    ->fetchDealHistory(
                        (string) $dealId
                    );

                $historyItems = $this->extractList(
                    $historyResponse,
                    [
                        'data.items',
                        'data',
                        'items',
                        'history',
                        'data.history',
                    ]
                );

                foreach ($historyItems as $index => $item) {
                    if (! is_array($item)) {
                        continue;
                    }

                    $this->storeHistoryEvent(
                        (string) $dealId,
                        $deal,
                        $item,
                        $index
                    );
                }
            }

            if (! $this->hasNextPage($response)) {
                break;
            }

            $page++;
        }
    }

    private function storeDealSnapshot(
        string $dealId,
        array $deal
    ): void {
        $companyId = $this->firstValue(
            $deal,
            [
                'company_id',
                'company.id',
                'account_id',
            ]
        );

        $contactId = $this->firstValue(
            $deal,
            [
                'contact_id',
                'primary_contact_id',
                'contact.id',
                'contacts.0.id',
                'contact_ids.0',
            ]
        );

        $occurredAt = $this->parseDate(
            $this->firstValue(
                $deal,
                [
                    'updated_at',
                    'changed_at',
                    'created_at',
                    'close_date',
                ]
            )
        );

        Graph8Event::updateOrCreate(
            [
                'external_id' => 'deal-snapshot-'
                    .$dealId,
            ],
            [
                'event_type' => 'deal.snapshot',
                'source' => 'graph8_api',
                'company_id' => $companyId
                    ? (string) $companyId
                    : null,
                'contact_id' => $contactId
                    ? (string) $contactId
                    : null,
                'deal_id' => $dealId,
                'payload' => [
                    'event' => 'deal.snapshot',
                    'source' => 'graph8_api',
                    'data' => $deal,
                ],
                'occurred_at' => $occurredAt,
                'processed' => false,
                'processed_at' => null,
            ]
        );
    }

    private function storeHistoryEvent(
        string $dealId,
        array $deal,
        array $item,
        int $index
    ): void {
        $eventType = $this->firstValue(
            $item,
            [
                'event_type',
                'event',
                'type',
                'action',
                'change_type',
                'field',
            ]
        ) ?? 'deal.history';

        if (! str_starts_with(
            (string) $eventType,
            'deal.'
        )) {
            $eventType = 'deal.'
                .strtolower(
                    str_replace(
                        [' ', '_'],
                        '.',
                        (string) $eventType
                    )
                );
        }

        $historyId = $this->firstValue(
            $item,
            [
                'id',
                'event_id',
                'history_id',
                'uuid',
            ]
        );

        if (! $historyId) {
            $historyId = hash(
                'sha256',
                $dealId
                    .'|'
                    .$index
                    .'|'
                    .json_encode($item)
            );
        }

        $companyId = $this->firstValue(
            $item,
            [
                'company_id',
                'company.id',
            ]
        ) ?? $this->firstValue(
            $deal,
            [
                'company_id',
                'company.id',
                'account_id',
            ]
        );

        $contactId = $this->firstValue(
            $item,
            [
                'contact_id',
                'contact.id',
            ]
        ) ?? $this->firstValue(
            $deal,
            [
                'contact_id',
                'primary_contact_id',
                'contacts.0.id',
                'contact_ids.0',
            ]
        );

        $occurredAt = $this->parseDate(
            $this->firstValue(
                $item,
                [
                    'occurred_at',
                    'changed_at',
                    'created_at',
                    'updated_at',
                    'timestamp',
                ]
            )
        );

        Graph8Event::updateOrCreate(
            [
                'external_id' => 'deal-history-'
                    .$historyId,
            ],
            [
                'event_type' => (string) $eventType,
                'source' => 'graph8_api',
                'company_id' => $companyId
                    ? (string) $companyId
                    : null,
                'contact_id' => $contactId
                    ? (string) $contactId
                    : null,
                'deal_id' => $dealId,
                'payload' => [
                    'event' => $eventType,
                    'source' => 'graph8_api',
                    'deal' => $deal,
                    'data' => $item,
                ],
                'occurred_at' => $occurredAt,
                'processed' => false,
                'processed_at' => null,
            ]
        );
    }

    private function extractList(
        array $response,
        array $candidatePaths
    ): array {
        foreach ($candidatePaths as $path) {
            $value = Arr::get(
                $response,
                $path
            );

            if (is_array($value)
                && array_is_list($value)) {
                return $value;
            }
        }

        if (array_is_list($response)) {
            return $response;
        }

        return [];
    }

    private function hasNextPage(
        array $response
    ): bool {
        foreach ([
            'pagination.has_next',
            'data.pagination.has_next',
            'meta.has_next',
            'data.meta.has_next',
        ] as $path) {
            $value = Arr::get(
                $response,
                $path
            );

            if (is_bool($value)) {
                return $value;
            }
        }

        return false;
    }

    private function firstValue(
        array $payload,
        array $paths
    ): mixed {
        foreach ($paths as $path) {
            $value = Arr::get(
                $payload,
                $path
            );

            if ($value !== null
                && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function parseDate(
        mixed $value
    ): Carbon {
        if ($value === null
            || $value === '') {
            return now();
        }

        try {
            if (is_numeric($value)) {
                return Carbon::createFromTimestamp(
                    (int) $value
                );
            }

            return Carbon::parse(
                (string) $value
            );
        } catch (Throwable) {
            return now();
        }
    }
}