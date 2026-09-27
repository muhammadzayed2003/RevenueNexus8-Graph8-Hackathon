<?php

namespace App\Services;

use App\Models\Recommendation;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class Graph8Service
{
    public function fetchEvents(array $query = []): array
    {
        return $this->get(
            $this->endpoint('events'),
            $query
        );
    }

    public function fetchCompanies(array $query = []): array
    {
        return $this->get(
            $this->endpoint('companies'),
            $query
        );
    }

    public function fetchContacts(array $query = []): array
    {
        return $this->get(
            $this->endpoint('contacts'),
            $query
        );
    }

    public function fetchDeals(array $query = []): array
    {
        return $this->get(
            $this->endpoint('deals'),
            $query
        );
    }

    public function fetchPipelines(
        array $query = []
    ): array {
        return $this->get(
            '/deals/pipelines',
            $query
        );
    }

    public function fetchDealHistory(
        string $dealId,
        array $query = []
    ): array {
        return $this->get(
            '/deals/'
                .rawurlencode($dealId)
                .'/history',
            $query
        );
    }

    public function findCompany(string $companyId): array
    {
        return $this->get(
            $this->resourceUrl(
                $this->endpoint('companies'),
                $companyId
            )
        );
    }

    public function findContact(string $contactId): array
    {
        return $this->get(
            $this->resourceUrl(
                $this->endpoint('contacts'),
                $contactId
            )
        );
    }

    public function findDeal(string $dealId): array
    {
        return $this->get(
            $this->resourceUrl(
                $this->endpoint('deals'),
                $dealId
            )
        );
    }

    public function fetchCompanyContacts(
        string $companyId
    ): array {
        $page = 1;
        $contacts = [];

        while (true) {
            $response = $this->get(
                '/companies/'
                    .rawurlencode($companyId)
                    .'/contacts',
                [
                    'page' => $page,
                    'limit' => 200,
                ]
            );

            $pageContacts = $this->extractList(
                $response,
                [
                    'data',
                    'contacts',
                    'items',
                    'data.contacts',
                    'data.items',
                    'data.results',
                ]
            );

            if ($pageContacts === []) {
                break;
            }

            $contacts = [
                ...$contacts,
                ...$pageContacts,
            ];

            if (! $this->hasNextPage($response)) {
                break;
            }

            $page++;
        }

        return $contacts;
    }

    public function createDeal(array $payload): array
    {
        $this->requirePayloadValues(
            $payload,
            [
                'name',
                'owner_id',
                'contact_ids',
            ],
            'graph8 deal'
        );

        if (! is_array($payload['contact_ids'])
            || $payload['contact_ids'] === []) {
            throw new RuntimeException(
                'At least one graph8 contact is required to create a deal.'
            );
        }

        return $this->post(
            $this->endpoint('deals'),
            $this->withoutNullValues($payload)
        );
    }

    public function updateDeal(
        string $dealId,
        array $payload
    ): array {
        if ($payload === []) {
            throw new RuntimeException(
                'At least one deal field is required for an update.'
            );
        }

        return $this->patch(
            $this->resourceUrl(
                $this->endpoint('deals'),
                $dealId
            ),
            $this->withoutNullValues($payload)
        );
    }

    public function createNote(array $payload): array
    {
        $this->requirePayloadValues(
            $payload,
            ['content'],
            'graph8 note'
        );

        return $this->post(
            '/notes',
            $this->withoutNullValues($payload)
        );
    }

    public function createTask(array $payload): array
    {
        $this->requirePayloadValues(
            $payload,
            ['title'],
            'graph8 task'
        );

        return $this->post(
            '/tasks',
            $this->withoutNullValues($payload)
        );
    }

    public function executeRecommendation(
        Recommendation $recommendation
    ): array {
        $actionPayload = is_array(
            $recommendation->action_payload
        )
            ? $recommendation->action_payload
            : [];

        $dealId = $this->firstPayloadValue(
            $actionPayload,
            [
                'graph8_deal_id',
                'deal_id',
                'target.deal_id',
                'metadata.graph8_deal_id',
            ]
        );

        $companyId = $this->firstPayloadValue(
            $actionPayload,
            [
                'graph8_company_id',
                'company_id',
                'target.company_id',
                'metadata.graph8_company_id',
            ]
        );

        $contactId = $this->firstPayloadValue(
            $actionPayload,
            [
                'graph8_contact_id',
                'contact_id',
                'target.contact_id',
                'metadata.graph8_contact_id',
            ]
        );

        [$entityType, $entityId] = $this->targetEntity(
            $dealId,
            $companyId,
            $contactId
        );

        $actionTitle = trim(
            (string) (
                $recommendation->action_type
                ?: 'RevenueNexus8 recommended action'
            )
        );

        $actionDescription = trim(
            (string) (
                $recommendation->description
                ?: Arr::get(
                    $actionPayload,
                    'description',
                    'Execute the approved RevenueNexus8 recommendation.'
                )
            )
        );

        $metadata = [
            'recommendation_id' => $recommendation->id,
            'simulation_id' => $recommendation->simulation_id,
            'source_module' => $recommendation->source_module,
            'approved_by' => $recommendation->approved_by,
            'approved_at' => $recommendation
                ->approved_at
                ?->toIso8601String(),
        ];

        $results = [];

        $notePayload = [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'content' => $this->recommendationNote(
                $actionTitle,
                $actionDescription,
                $actionPayload,
                $metadata
            ),
            'source_url' => config('app.url'),
        ];

        $results['note'] = $this->createNote(
            $notePayload
        );

        $taskPayload = [
            'title' => Str::limit(
                'RevenueNexus8: '.$actionTitle,
                255,
                ''
            ),
            'description' => $actionDescription,
            'task_type' => $this->taskType(
                $recommendation->action_type
            ),
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'due_date' => now()
                ->addDay()
                ->toIso8601String(),
            'priority' => 1,
            'tags' => [
                'RevenueNexus8',
                (string) $recommendation->source_module,
            ],
            'source_url' => config('app.url'),
        ];

        $results['task'] = $this->createTask(
            $taskPayload
        );

        $targetStageId = $this->firstPayloadValue(
            $actionPayload,
            [
                'target_stage_id',
                'graph8_stage_id',
                'target.stage_id',
            ]
        );

        if ($dealId && $targetStageId) {
            $results['deal'] = $this->updateDeal(
                (string) $dealId,
                [
                    'stage_id' => (string) $targetStageId,
                ]
            );
        }

        return [
            'provider' => 'graph8',
            'source' => 'RevenueNexus8',
            'target' => [
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'deal_id' => $dealId,
                'company_id' => $companyId,
                'contact_id' => $contactId,
            ],
            'metadata' => $metadata,
            'results' => $results,
        ];
    }

    private function get(
        string $endpoint,
        array $query = []
    ): array {
        $response = $this->client()
            ->get($endpoint, $query);

        return $this->responseData($response);
    }

    private function post(
        string $endpoint,
        array $payload
    ): array {
        $response = $this->client()
            ->post($endpoint, $payload);

        return $this->responseData($response);
    }

    private function patch(
        string $endpoint,
        array $payload
    ): array {
        $response = $this->client()
            ->patch($endpoint, $payload);

        return $this->responseData($response);
    }

    private function client(): PendingRequest
    {
        $baseUrl = config('graph8.base_url');
        $apiToken = config('graph8.api_token');
        $authHeader = config(
            'graph8.auth_header',
            'Authorization'
        );
        $authScheme = trim(
            (string) config(
                'graph8.auth_scheme',
                'Bearer'
            )
        );
        $timeout = (int) config(
            'graph8.timeout',
            60
        );

        if (blank($baseUrl)) {
            throw new RuntimeException(
                'GRAPH8_BASE_URL is missing from the .env file.'
            );
        }

        if (blank($apiToken)) {
            throw new RuntimeException(
                'GRAPH8_API_TOKEN is missing from the .env file.'
            );
        }

        $authValue = $authScheme === ''
            ? $apiToken
            : $authScheme.' '.$apiToken;

        return Http::baseUrl(
            rtrim($baseUrl, '/')
        )
            ->acceptJson()
            ->asJson()
            ->timeout($timeout)
            ->retry(2, 1000)
            ->withHeaders([
                $authHeader => $authValue,
            ]);
    }

    private function endpoint(string $name): string
    {
        $endpoint = config(
            "graph8.endpoints.{$name}"
        );

        if (blank($endpoint)) {
            throw new RuntimeException(
                "GRAPH8_"
                .strtoupper($name)
                ."_ENDPOINT is missing from the .env file."
            );
        }

        return '/'.ltrim($endpoint, '/');
    }

    private function resourceUrl(
        string $endpoint,
        string $resourceId
    ): string {
        if (str_contains($endpoint, '{id}')) {
            return str_replace(
                '{id}',
                rawurlencode($resourceId),
                $endpoint
            );
        }

        return rtrim($endpoint, '/')
            .'/'
            .rawurlencode($resourceId);
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

    private function hasNextPage(array $response): bool
    {
        $candidatePaths = [
            'pagination.has_next',
            'data.pagination.has_next',
            'meta.has_next',
            'data.meta.has_next',
        ];

        foreach ($candidatePaths as $path) {
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

    private function responseData(
        Response $response
    ): array {
        if ($response->failed()) {
            $message = $response->json(
                'error.message'
            )
                ?? $response->json('message')
                ?? $response->json('detail.0.msg')
                ?? $response->body();

            throw new RuntimeException(
                'graph8 API request failed with status '
                .$response->status()
                .': '
                .Str::limit(
                    (string) $message,
                    1000
                )
            );
        }

        $data = $response->json();

        if (is_array($data)) {
            return $data;
        }

        return [
            'status' => $response->status(),
            'body' => $response->body(),
        ];
    }

    private function requirePayloadValues(
        array $payload,
        array $requiredKeys,
        string $resourceName
    ): void {
        foreach ($requiredKeys as $key) {
            if (! array_key_exists($key, $payload)
                || blank($payload[$key])) {
                throw new RuntimeException(
                    ucfirst($resourceName)
                    ." requires {$key}."
                );
            }
        }
    }

    private function withoutNullValues(
        array $payload
    ): array {
        return array_filter(
            $payload,
            static fn (mixed $value): bool =>
                $value !== null
        );
    }

    private function firstPayloadValue(
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

    private function targetEntity(
        mixed $dealId,
        mixed $companyId,
        mixed $contactId
    ): array {
        if ($dealId) {
            return [
                'deal',
                (string) $dealId,
            ];
        }

        if ($companyId) {
            return [
                'company',
                (string) $companyId,
            ];
        }

        if ($contactId) {
            return [
                'contact',
                (string) $contactId,
            ];
        }

        return [
            null,
            null,
        ];
    }

    private function recommendationNote(
        string $title,
        string $description,
        array $payload,
        array $metadata
    ): string {
        return implode("\n\n", [
            'RevenueNexus8 Approved Recommendation',
            'Action: '.$title,
            'Description: '.$description,
            'Source Module: '
                .($metadata['source_module'] ?? 'unknown'),
            'Recommendation ID: '
                .$metadata['recommendation_id'],
            'Dynamic Payload: '
                .json_encode(
                    $payload,
                    JSON_PRETTY_PRINT
                    | JSON_UNESCAPED_SLASHES
                    | JSON_UNESCAPED_UNICODE
                ),
        ]);
    }

    private function taskType(
        ?string $actionType
    ): string {
        $value = strtolower(
            (string) $actionType
        );

        return match (true) {
            str_contains($value, 'meeting'),
            str_contains($value, 'workshop'),
            str_contains($value, 'demo') => 'meeting',

            str_contains($value, 'call'),
            str_contains($value, 'follow-up'),
            str_contains($value, 'follow up') => 'call',

            default => 'task',
        };
    }
}
