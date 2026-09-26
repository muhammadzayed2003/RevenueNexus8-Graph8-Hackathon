<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class RevenueSimulationService
{
    public function runBoardroom(array $deal): array
    {
        $prompt = <<<'PROMPT'
You are Boardroom8, an enterprise buyer committee simulation engine.

Analyze the supplied B2B deal using four buyer personas:

1. CFO — ROI, budget and financial risk
2. CTO — security, integration and technical risk
3. Business Champion — urgency, business impact and adoption
4. Procurement — pricing, terms and vendor risk

The personas must debate the deal from their own perspectives. Return a realistic
purchase probability, objections, a recommended next action and a graph8-ready
action payload.

Return ONLY valid JSON using this exact structure:

{
  "executive_summary": "string",
  "purchase_probability": 0,
  "overall_score": 0,
  "personas": [
    {
      "name": "string",
      "role": "string",
      "position": "supportive|neutral|opposed",
      "concerns": ["string"],
      "argument": "string"
    }
  ],
  "debate": [
    {
      "speaker": "string",
      "message": "string"
    }
  ],
  "key_objections": ["string"],
  "recommendation": {
    "title": "string",
    "summary": "string",
    "action_type": "string",
    "action_payload": {}
  }
}

Both purchase_probability and overall_score must be numbers between 0 and 100.
PROMPT;

        $dealData = json_encode(
            $deal,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );

        return $this->generateStructuredJson(
            $prompt."\n\nDEAL DATA:\n".$dealData
        );
    }

    private function generateStructuredJson(string $prompt): array
    {
        $apiKey = config('services.gemini.key');
        $model = config(
            'services.gemini.model',
            'gemini-3.1-flash-lite'
        );

        if (blank($apiKey)) {
            throw new RuntimeException(
                'GEMINI_API_KEY is missing from the .env file.'
            );
        }

        $response = Http::withHeaders([
            'x-goog-api-key' => $apiKey,
            'Content-Type' => 'application/json',
        ])
            ->timeout(90)
            ->retry(2, 1000)
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
                        'temperature' => 0.3,
                        'responseMimeType' => 'application/json',
                    ],
                ]
            );

        if ($response->failed()) {
            throw new RuntimeException(
                $response->json('error.message')
                    ?? 'The Gemini API request failed.'
            );
        }

        $text = $response->json(
            'candidates.0.content.parts.0.text'
        );

        if (! is_string($text) || blank($text)) {
            throw new RuntimeException(
                'Gemini returned an empty simulation result.'
            );
        }

        $result = json_decode($text, true);

        if (! is_array($result)) {
            throw new RuntimeException(
                'Gemini returned invalid JSON.'
            );
        }

        return $result;
    }
}