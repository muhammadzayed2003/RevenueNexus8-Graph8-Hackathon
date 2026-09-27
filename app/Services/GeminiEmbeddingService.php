<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiEmbeddingService
{
    public const DIMENSIONS = 768;

    public function embedDocument(
        string $text,
        ?string $title = null
    ): array {
        return $this->embed(
            $text,
            'RETRIEVAL_DOCUMENT',
            $title
        );
    }

    public function embedQuery(string $text): array
    {
        return $this->embed(
            $text,
            'RETRIEVAL_QUERY'
        );
    }

    private function embed(
        string $text,
        string $taskType,
        ?string $title = null
    ): array {
        $apiKey = config('services.gemini.key');
        $model = config(
            'qdrant.embedding_model',
            'gemini-embedding-001'
        );

        if (blank($apiKey)) {
            throw new RuntimeException(
                'GEMINI_API_KEY is missing.'
            );
        }

        $payload = [
            'model' => 'models/'.$model,
            'content' => [
                'parts' => [
                    [
                        'text' => mb_substr(
                            $text,
                            0,
                            12000
                        ),
                    ],
                ],
            ],
            'taskType' => $taskType,
            'outputDimensionality' => self::DIMENSIONS,
        ];

        if (
            $taskType === 'RETRIEVAL_DOCUMENT'
            && filled($title)
        ) {
            $payload['title'] = $title;
        }

        $response = Http::withHeaders([
            'x-goog-api-key' => $apiKey,
            'Content-Type' => 'application/json',
        ])
            ->timeout(60)
            ->retry(2, 800)
            ->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:embedContent",
                $payload
            );

        if ($response->failed()) {
            throw new RuntimeException(
                $response->json('error.message')
                    ?? 'Gemini embedding request failed.'
            );
        }

        $values = $response->json(
            'embedding.values'
        );

        if (
            ! is_array($values)
            || count($values) !== self::DIMENSIONS
        ) {
            throw new RuntimeException(
                'Gemini returned an invalid embedding.'
            );
        }

        return array_map(
            'floatval',
            $values
        );
    }
}