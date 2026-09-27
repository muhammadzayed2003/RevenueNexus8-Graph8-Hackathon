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
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
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
                ->findOrFail(
                    $request->integer('simulation')
                );
        }

        $companies = Graph8Record::query()
            ->where('record_type', 'company')
            ->orderByRaw(
                'name IS NULL, name ASC'
            )
            ->get();

        $deals = Graph8Record::query()
            ->where('record_type', 'deal')
            ->orderByRaw(
                'name IS NULL, name ASC'
            )
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
                Rule::exists(
                    'graph8_records',
                    'id'
                )->where(
                    fn (Builder $query) => $query
                        ->where(
                            'record_type',
                            'company'
                        )
                ),
            ],
            'deal_record_id' => [
                'nullable',
                'integer',
                Rule::exists(
                    'graph8_records',
                    'id'
                )->where(
                    fn (Builder $query) => $query
                        ->where(
                            'record_type',
                            'deal'
                        )
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
            ->findOrFail(
                $validated['company_record_id']
            );

        $deal = null;

        if (! empty($validated['deal_record_id'])) {
            $deal = Graph8Record::query()
                ->where('record_type', 'deal')
                ->findOrFail(
                    $validated['deal_record_id']
                );
        }

        try {
            $companyContacts = $graph8Service
                ->fetchCompanyContacts(
                    $company->external_id
                );

            if ($companyContacts === []) {
                throw new RuntimeException(
                    'The selected graph8 company has no contacts. Select a company with at least one contact.'
                );
            }

            $graph8Deal = $deal
                ? [
                    'id' => $deal->external_id,
                    'name' => $deal->name,
                    'data' => $deal->payload,
                    'created_by_revenuetwin8' => false,
                ]
                : $this->createGraph8Deal(
                    $request,
                    $graph8Service,
                    $company,
                    $companyContacts,
                    $validated
                );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('boardroom.index')
                ->withInput()
                ->withErrors([
                    'simulation' =>
                        'Unable to prepare the graph8 deal: '
                        .$exception->getMessage(),
                ]);
        }

        $graph8DealId = (string) (
            $graph8Deal['id']
            ?? ''
        );

        if ($graph8DealId === '') {
            return redirect()
                ->route('boardroom.index')
                ->withInput()
                ->withErrors([
                    'simulation' =>
                        'graph8 did not return a deal ID.',
                ]);
        }

        $dealContext = [
            'company' => [
                'graph8_id' => $company->external_id,
                'name' => $company->name,
                'data' => $company->payload,
            ],
            'company_contacts' => $companyContacts,
            'company_contact_count' => count(
                $companyContacts
            ),
            'deal' => [
                'graph8_id' => $graph8DealId,
                'name' => $graph8Deal['name']
                    ?? (
                        ($company->name
                            ?? 'graph8 Company')
                        .' RevenueTwin8 Deal'
                    ),
                'data' => $graph8Deal['data']
                    ?? $graph8Deal,
                'created_by_revenuetwin8' =>
                    (bool) (
                        $graph8Deal[
                            'created_by_revenuetwin8'
                        ] ?? false
                    ),
            ],
            'deal_value' => $validated['deal_value'],
            'stage' => $validated['stage'],
            'solution' => $validated['solution'],
            'objections' =>
                $validated['objections']
                ?? null,
        ];

        $simulation = Simulation::create([
            'user_id' => $request->user()->id,
            'module' => 'boardroom8',
            'title' => (
                $company->name
                ?? 'graph8 Company'
            ).' buyer committee',
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
                'score' =>
                    $result['overall_score']
                    ?? null,
                'completed_at' => now(),
            ]);

            $recommendation =
                $result['recommendation']
                ?? [];

            Recommendation::create([
                'user_id' =>
                    $request->user()->id,
                'simulation_id' =>
                    $simulation->id,
                'source_module' =>
                    'boardroom8',
                'title' =>
                    $recommendation['title']
                    ?? 'Review Boardroom8 recommendation',
                'summary' =>
                    $recommendation['summary']
                    ?? (
                        $result['executive_summary']
                        ?? 'Simulation completed.'
                    ),
                'action_type' =>
                    $recommendation['action_type']
                    ?? 'review_deal',
                'action_payload' => [
                    ...(
                        $recommendation[
                            'action_payload'
                        ] ?? []
                    ),
                    'graph8_company_id' =>
                        $company->external_id,
                    'graph8_deal_id' =>
                        $graph8DealId,
                    'graph8_contact_ids' =>
                        collect($companyContacts)
                            ->pluck('id')
                            ->filter()
                            ->map(
                                fn (mixed $id): int =>
                                    (int) $id
                            )
                            ->values()
                            ->all(),
                ],
                'status' => 'pending',
            ]);

            return redirect()
                ->route('boardroom.index', [
                    'simulation' =>
                        $simulation->id,
                ])
                ->with(
                    'success',
                    $deal
                        ? 'Boardroom8 simulation completed using the selected live graph8 deal.'
                        : 'Boardroom8 created a live graph8 deal and completed the buyer committee simulation.'
                );
        } catch (Throwable $exception) {
            report($exception);

            $simulation->update([
                'status' => 'failed',
                'error_message' =>
                    $exception->getMessage(),
                'completed_at' => now(),
            ]);

            return redirect()
                ->route('boardroom.index', [
                    'simulation' =>
                        $simulation->id,
                ])
                ->withErrors([
                    'simulation' =>
                        $exception->getMessage(),
                ]);
        }
    }

    private function createGraph8Deal(
        Request $request,
        Graph8Service $graph8Service,
        Graph8Record $company,
        array $companyContacts,
        array $validated
    ): array {
        $pipelinesResponse = $graph8Service
            ->fetchPipelines();

        $pipelines = $this->extractList(
            $pipelinesResponse,
            [
                'data',
                'pipelines',
                'data.pipelines',
            ]
        );

        if ($pipelines === []) {
            throw new RuntimeException(
                'No graph8 sales pipeline is available.'
            );
        }

        $pipeline = collect($pipelines)
            ->first(
                fn (array $item): bool =>
                    Str::lower(
                        (string) (
                            $item['name']
                            ?? ''
                        )
                    ) ===
                    Str::lower(
                        'RevenueTwin8 Sales Pipeline'
                    )
            )
            ?? collect($pipelines)
                ->firstWhere(
                    'is_default',
                    true
                )
            ?? $pipelines[0];

        $stages = is_array(
            $pipeline['stages']
            ?? null
        )
            ? $pipeline['stages']
            : [];

        if ($stages === []) {
            throw new RuntimeException(
                'The selected graph8 pipeline has no stages.'
            );
        }

        $stage = $this->matchStage(
            $stages,
            $validated['stage']
        );

        $contactIds = collect(
            $companyContacts
        )
            ->pluck('id')
            ->filter(
                fn (mixed $id): bool =>
                    is_numeric($id)
            )
            ->map(
                fn (mixed $id): int =>
                    (int) $id
            )
            ->unique()
            ->values()
            ->all();

        if ($contactIds === []) {
            throw new RuntimeException(
                'graph8 contacts did not contain valid contact IDs.'
            );
        }

        $dealName = trim(
            ($company->name
                ?? 'graph8 Company')
            .' — '
            .Str::limit(
                $validated['solution'],
                80,
                ''
            )
        );

        $response = $graph8Service
            ->createDeal([
                'name' => $dealName,
                'description' =>
                    $this->dealDescription(
                        $validated
                    ),
                'amount' =>
                    (float) $validated[
                        'deal_value'
                    ],
                'currency' => 'USD',
                'pipeline_id' =>
                    (string) $pipeline['id'],
                'stage_id' =>
                    (string) $stage['id'],
                'close_date' => now()
                    ->addDays(30)
                    ->toDateString(),
                'owner_id' =>
                    (string) config(
                        'graph8.owner_id',
                        $request->user()->email
                    ),
                'contact_ids' =>
                    $contactIds,
                'allow_duplicate' => true,
            ]);

        $dealData = $this->extractObject(
            $response,
            [
                'data',
                'deal',
                'data.deal',
            ]
        );

        $dealId = $this->firstValue(
            $response,
            [
                'data.id',
                'data.deal_id',
                'deal.id',
                'deal.deal_id',
                'id',
                'deal_id',
            ]
        );

        if (! $dealId) {
            throw new RuntimeException(
                'graph8 created the deal but did not return its ID.'
            );
        }

        return [
            'id' => (string) $dealId,
            'name' =>
                $dealData['name']
                ?? $dealName,
            'data' =>
                $dealData !== []
                    ? $dealData
                    : $response,
            'created_by_revenuetwin8' =>
                true,
        ];
    }

    private function matchStage(
        array $stages,
        string $requestedStage
    ): array {
        $requested = $this->normalize(
            $requestedStage
        );

        $exact = collect($stages)
            ->first(
                fn (array $stage): bool =>
                    $this->normalize(
                        (string) (
                            $stage['name']
                            ?? ''
                        )
                    ) === $requested
            );

        if ($exact) {
            return $exact;
        }

        $partial = collect($stages)
            ->first(
                function (
                    array $stage
                ) use ($requested): bool {
                    $candidate =
                        $this->normalize(
                            (string) (
                                $stage['name']
                                ?? ''
                            )
                        );

                    return $candidate !== ''
                        && (
                            str_contains(
                                $candidate,
                                $requested
                            )
                            || str_contains(
                                $requested,
                                $candidate
                            )
                        );
                }
            );

        if ($partial) {
            return $partial;
        }

        return collect($stages)
            ->firstWhere(
                'stage_type',
                'open'
            )
            ?? $stages[0];
    }

    private function normalize(
        string $value
    ): string {
        return Str::of($value)
            ->lower()
            ->replaceMatches(
                '/[^a-z0-9]+/',
                ''
            )
            ->value();
    }

    private function dealDescription(
        array $validated
    ): string {
        return implode("\n\n", [
            'Created automatically by RevenueTwin8.',
            'Proposed solution: '
                .$validated['solution'],
            'Known objections: '
                .(
                    $validated['objections']
                    ?? 'None provided.'
                ),
            'Requested stage: '
                .$validated['stage'],
        ]);
    }

    private function extractList(
        array $response,
        array $paths
    ): array {
        foreach ($paths as $path) {
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

    private function extractObject(
        array $response,
        array $paths
    ): array {
        foreach ($paths as $path) {
            $value = Arr::get(
                $response,
                $path
            );

            if (is_array($value)
                && ! array_is_list($value)) {
                return $value;
            }
        }

        return [];
    }

    private function firstValue(
        array $response,
        array $paths
    ): mixed {
        foreach ($paths as $path) {
            $value = Arr::get(
                $response,
                $path
            );

            if ($value !== null
                && $value !== '') {
                return $value;
            }
        }

        return null;
    }
}