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

You will receive actual graph8 company data, actual graph8 company contacts,
optional graph8 deal data, and the proposed commercial scenario.

Rules for buyer personas:

1. If company_contacts contains relevant people, use their exact graph8 name,
   title, role and contact ID. Never change or invent a personal name.
2. Select the most relevant real contacts for finance, technical, business,
   procurement, executive, champion, decision-maker, influencer or blocker roles.
3. If a required committee role has no matching real contact, create a role-only
   simulation persona named exactly like "CFO Persona", "CTO Persona",
   "Business Champion Persona" or "Procurement Persona".
4. Never give a simulated role persona a fictional human name.
5. Set source to "graph8_contact" for actual people and "role_simulation" for
   role-only personas.
6. Base company facts only on supplied graph8 data. Do not invent company size,
   revenue, technology, industry or contact details.
7. The committee must debate value, ROI, security, integration, adoption,
   procurement risk and purchase readiness when relevant to the supplied data.

Return a realistic purchase probability, objections, a recommended next action
and a graph8-ready action payload.

Return ONLY valid JSON using this exact structure:

{
  "executive_summary": "string",
  "purchase_probability": 0,
  "overall_score": 0,
  "personas": [
    {
      "name": "string",
      "role": "string",
      "source": "graph8_contact|role_simulation",
      "graph8_contact_id": "string|null",
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

        return $this->generateStructuredJson(
            $prompt."\n\nDEAL DATA:\n".$this->encodeData($deal)
        );
    }

    public function runNegotiation(array $deal): array
    {
        $prompt = <<<'PROMPT'
You are Negotiator8, a B2B negotiation war-game engine.

Simulate a realistic multi-round negotiation between:

1. Buyer Agent — protects budget, reduces risk and challenges commercial terms.
2. Seller Agent — protects deal value, handles objections and advances the deal.

Follow the requested number of rounds. Each round must contain a buyer move,
seller response, buyer pressure level, seller confidence level, offered price
and any concession. Do not recommend a final price below the supplied minimum
acceptable price.

Return ONLY valid JSON using this exact structure:

{
  "executive_summary": "string",
  "win_probability": 0,
  "overall_score": 0,
  "recommended_price": 0,
  "recommended_move": "string",
  "rounds": [
    {
      "round": 1,
      "buyer_move": "string",
      "seller_response": "string",
      "buyer_pressure": 0,
      "seller_confidence": 0,
      "offered_price": 0,
      "concession": "string"
    }
  ],
  "key_risks": ["string"],
  "acceptable_concessions": ["string"],
  "recommendation": {
    "title": "string",
    "summary": "string",
    "action_type": "string",
    "action_payload": {}
  }
}

win_probability, overall_score, buyer_pressure and seller_confidence must be
numbers between 0 and 100.
PROMPT;

        return $this->generateStructuredJson(
            $prompt."\n\nNEGOTIATION DATA:\n".$this->encodeData($deal)
        );
    }

    public function runTimeMachine(
        array $configuration,
        array $events
    ): array {
        $prompt = <<<'PROMPT'
You are TimeMachine8, a historical revenue strategy replay engine.

You will receive chronological graph8 revenue events and a candidate strategy.
Replay the supplied events in their original order. For each event:

1. Identify what happened in the historical event.
2. Determine how the candidate strategy would respond at that moment.
3. Estimate the likely commercial impact of the alternative action.
4. Compare the actual path with the simulated path.

Use only the supplied event data. Do not invent additional historical events.
Return a projected revenue or conversion uplift as a percentage and a confidence
score based on the quantity and quality of the supplied evidence.

Return ONLY valid JSON using this exact structure:

{
  "executive_summary": "string",
  "actual_outcome": "string",
  "simulated_outcome": "string",
  "projected_uplift_percent": 0,
  "confidence_score": 0,
  "overall_score": 0,
  "timeline": [
    {
      "event_id": "string",
      "event_type": "string",
      "occurred_at": "string",
      "actual_action": "string",
      "simulated_action": "string",
      "impact": "string"
    }
  ],
  "key_findings": ["string"],
  "limitations": ["string"],
  "recommendation": {
    "title": "string",
    "summary": "string",
    "action_type": "string",
    "action_payload": {}
  }
}

projected_uplift_percent, confidence_score and overall_score must be numbers.
PROMPT;

        $input = [
            'configuration' => $configuration,
            'chronological_events' => $events,
        ];

        return $this->generateStructuredJson(
            $prompt."\n\nREPLAY INPUT:\n".$this->encodeData($input)
        );
    }

    private function encodeData(array $data): string
    {
        return json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
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