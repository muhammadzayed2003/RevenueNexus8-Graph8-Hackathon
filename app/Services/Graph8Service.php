<?php

namespace App\Services;

use App\Models\Recommendation;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
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

    public function executeRecommendation(
        Recommendation $recommendation
    ): array {
        $payload = [
            'action_type' => $recommendation->action_type,
            'payload' => $recommendation->action_payload,
            'source' => 'RevenueTwin8',
            'metadata' => [
                'recommendation_id' => $recommendation->id,
                'simulation_id' => $recommendation->simulation_id,
                'source_module' => $recommendation->source_module,
                'approved_by' => $recommendation->approved_by,
                'approved_at' => $recommendation->approved_at?->toIso8601String(),
            ],
        ];

        return $this->post(
            $this->endpoint('actions'),
            $payload
        );
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

    private function client(): PendingRequest
    {
        $baseUrl = config('graph8.base_url');
        $apiToken = config('graph8.api_token');
        $authHeader = config(
            'graph8.auth_header',
            'Authorization'
        );
        $authScheme = trim(
            (string) config('graph8.auth_scheme', 'Bearer')
        );
        $timeout = (int) config('graph8.timeout', 60);

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
        $endpoint = config("graph8.endpoints.{$name}");

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
            $value = Arr::get($response, $path);

            if (is_array($value) && array_is_list($value)) {
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
            $value = Arr::get($response, $path);

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
            throw new RuntimeException(
                $response->json('message')
                    ?? $response->json('error.message')
                    ?? "graph8 API request failed with status {$response->status()}."
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
}