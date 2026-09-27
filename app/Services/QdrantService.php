<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class QdrantService
{
    public function ensureCollection(): void
    {
        $response = $this->client()
            ->get(
                '/collections/'
                .rawurlencode($this->collection())
            );

        if ($response->successful()) {
            return;
        }

        if ($response->status() !== 404) {
            $this->throwFailure($response);
        }

        $response = $this->client()
            ->put(
                '/collections/'
                .rawurlencode($this->collection()),
                [
                    'vectors' => [
                        'size' =>
                            GeminiEmbeddingService::DIMENSIONS,
                        'distance' => 'Cosine',
                    ],
                ]
            );

        if ($response->failed()) {
            $this->throwFailure($response);
        }
    }

    public function upsert(array $points): void
    {
        if ($points === []) {
            return;
        }

        $response = $this->client()
            ->put(
                '/collections/'
                .rawurlencode($this->collection())
                .'/points',
                [
                    'points' => $points,
                ]
            );

        if ($response->failed()) {
            $this->throwFailure($response);
        }
    }

    public function search(
        array $vector,
        int $userId,
        int $limit = 8
    ): array {
        $response = $this->client()
            ->post(
                '/collections/'
                .rawurlencode($this->collection())
                .'/points/query',
                [
                    'query' => $vector,
                    'limit' => $limit,
                    'with_payload' => true,
                    'with_vector' => false,
                ]
            );

        if ($response->failed()) {
            $this->throwFailure($response);
        }

        return Arr::get(
            $response->json(),
            'result.points',
            []
        );
    }

    private function client(): PendingRequest
    {
        $url = config('qdrant.url');
        $apiKey = config('qdrant.api_key');

        if (blank($url) || blank($apiKey)) {
            throw new RuntimeException(
                'Qdrant URL or API key is missing.'
            );
        }

        return Http::baseUrl(
            rtrim($url, '/')
        )
            ->acceptJson()
            ->asJson()
            ->timeout(60)
            ->retry(
                2,
                800,
                null,
                false
            )
            ->withHeaders([
                'api-key' => $apiKey,
            ]);
    }

    private function collection(): string
    {
        return config(
            'qdrant.collection',
            'revenue_nexus_knowledge'
        );
    }

    private function throwFailure(
        Response $response
    ): never {
        throw new RuntimeException(
            $response->json('status.error')
                ?? $response->json('message')
                ?? "Qdrant request failed with status {$response->status()}."
        );
    }
}