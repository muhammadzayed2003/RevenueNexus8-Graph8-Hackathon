<?php

namespace App\Http\Controllers;

use App\Services\RevenueKnowledgeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class EvidenceChatController extends Controller
{
    public function store(
        Request $request,
        RevenueKnowledgeService $knowledge
    ): JsonResponse {
        $validated = $request->validate([
            'message' => [
                'required',
                'string',
                'max:2000',
            ],
            'history' => [
                'nullable',
                'array',
                'max:8',
            ],
            'history.*.role' => [
                'required',
                'string',
                'in:user,assistant',
            ],
            'history.*.content' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        $matches = $knowledge->retrieve(
            $validated['message'],
            $request->user()->id
        );

        $sources = collect($matches)
            ->map(function (array $match): array {
                return [
                    'title' => data_get(
                        $match,
                        'payload.title',
                        'RevenueNexus8 documentation'
                    ),
                    'text' => data_get(
                        $match,
                        'payload.text',
                        ''
                    ),
                    'score' => round(
                        (float) data_get(
                            $match,
                            'score',
                            0
                        ),
                        3
                    ),
                ];
            })
            ->filter(
                fn (array $source): bool =>
                    filled($source['text'])
            )
            ->values();

        if ($sources->isEmpty()) {
            return response()->json([
                'answer' => 'I could not find relevant RevenueNexus8 documentation for that question.',
                'sources' => [],
            ]);
        }

        $context = $sources
            ->map(function (
                array $source,
                int $index
            ): string {
                $number = $index + 1;

                return "SOURCE {$number}: {$source['title']}\n"
                    .$source['text'];
            })
            ->implode("\n\n");

        $history = collect(
            $validated['history'] ?? []
        )
            ->map(
                fn (array $message): string =>
                    strtoupper($message['role'])
                    .': '
                    .$message['content']
            )
            ->implode("\n");

        $prompt = <<<PROMPT
You are Evidence8, the embedded RevenueNexus8 product knowledge assistant.

Answer questions only from the supplied RevenueNexus8 documentation.

Rules:
- Explain how RevenueNexus8 works, how modules were built and what they do.
- Do not invent features, integrations, results or implementation details.
- Do not claim access to live deals, companies, contacts or buyer activity.
- Evidence8 contains product documentation only.
- Clearly distinguish AI simulations from verified graph8 actions.
- If the documentation does not answer the question, say that the information is not available.
- Keep answers concise, clear and useful.
- Do not mention internal vector scores.
- Use plain text only. If listing items, use numbered sentences without Markdown formatting.
- keep your answers short and to the point
RECENT CONVERSATION:
{$history}

DOCUMENTATION:
{$context}

USER QUESTION:
{$validated['message']}
PROMPT;

        $apiKey = config('services.gemini.key');
        $model = config(
            'services.gemini.model',
            'gemini-3.1-flash-lite'
        );

        $response = Http::withHeaders([
            'x-goog-api-key' => $apiKey,
            'Content-Type' => 'application/json',
        ])
            ->timeout(60)
            ->retry(2, 800)
            ->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent",
                [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                [
                                    'text' => $prompt,
                                ],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.1,
                    ],
                ]
            );

        if ($response->failed()) {
            throw new RuntimeException(
                $response->json('error.message')
                    ?? 'Evidence8 could not generate an answer.'
            );
        }

        $answer = collect(
            $response->json(
                'candidates.0.content.parts',
                []
            )
        )
            ->pluck('text')
            ->filter(
                fn ($value): bool =>
                    is_string($value)
            )
            ->implode('');

        if (blank($answer)) {
            throw new RuntimeException(
                'Evidence8 returned an empty answer.'
            );
        }

        return response()->json([
            'answer' => trim($answer),
            'sources' => $sources
                ->pluck('title')
                ->unique()
                ->values()
                ->all(),
        ]);
    }
}