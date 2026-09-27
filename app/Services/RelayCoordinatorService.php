<?php

namespace App\Services;

use App\Models\Recommendation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class RelayCoordinatorService
{
    public function send(
        Recommendation $recommendation,
        array $target,
        array $metadata
    ): array {
        $agentId = config(
            'graph8.relay.agent_id'
        );

        $contactId = $this->contactId(
            $recommendation,
            $target
        );

        if (blank($agentId)) {
            throw new RuntimeException(
                'GRAPH8_RELAY_AGENT_ID is missing.'
            );
        }

        if (blank($contactId)) {
            throw new RuntimeException(
                'A graph8 contact is required for Relay8 Coordinator.'
            );
        }

        $actionPayload = is_array(
            $recommendation->action_payload
        )
            ? $recommendation->action_payload
            : [];

        $report = [
            'report_type' =>
                'RevenueNexus8 approved recommendation',
            'human_approval' => [
                'approved' => true,
                'approved_by' =>
                    $metadata['approved_by']
                    ?? null,
                'approved_at' =>
                    $metadata['approved_at']
                    ?? null,
            ],
            'recommendation' => [
                'id' => $recommendation->id,
                'simulation_id' =>
                    $recommendation->simulation_id,
                'source_module' =>
                    $recommendation->source_module,
                'title' =>
                    $recommendation->title,
                'summary' =>
                    $recommendation->summary,
                'action_type' =>
                    $recommendation->action_type,
                'action_payload' => Arr::except(
                    $actionPayload,
                    ['replayed_event_ids']
                ),
            ],
            'graph8_target' => $target,
            'processing_rules' => [
                'decision_support_only',
                'separate_verified_facts_from_simulation',
                'human_approval_required',
                'no_external_action',
            ],
        ];

        $reportText = implode("\n\n", [
            'RevenueNexus8 approved decision report.',
            'Review risks, missing evidence and unsupported commitments.',
            'Recommend one safe next step.',
            'Do not perform external actions.',
            json_encode(
                $report,
                JSON_PRETTY_PRINT
                | JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_UNICODE
            ),
        ]);

        $response = Http::baseUrl(
            rtrim(
                (string) config('graph8.base_url'),
                '/'
            )
        )
            ->acceptJson()
            ->asJson()
            ->timeout(
                (int) config(
                    'graph8.timeout',
                    60
                )
            )
            ->retry(2, 1000, null, false)
            ->withToken(
                (string) config(
                    'graph8.api_token'
                )
            )
            ->post(
                '/voice/agents/'
                .rawurlencode((string) $agentId)
                .'/memory',
                [
                    'contact_id' =>
                        (string) $contactId,
                    'session_id' =>
                        'revenuenexus8-recommendation-'
                        .$recommendation->id,
                    'actor' =>
                        'RevenueNexus8 Relay8',
                    'interaction_type' =>
                        'chat',
                    'interactions' => [
                        [
                            'role' => 'user',
                            'content' => $reportText,
                        ],
                    ],
                ]
            );

        if ($response->failed()) {
            throw new RuntimeException(
                'Relay8 Coordinator delivery failed: '
                .(
                    $response->json('message')
                    ?? $response->json('detail')
                    ?? $response->body()
                )
            );
        }

        return [
            'agent_id' =>
                (string) $agentId,
            'agent_name' => config(
                'graph8.relay.agent_name',
                'Relay8 Coordinator'
            ),
            'contact_id' =>
                (string) $contactId,
            'session_id' =>
                'revenuenexus8-recommendation-'
                .$recommendation->id,
            'delivered' => true,
            'delivery_channel' =>
                'agent_memory',
            'status' =>
                'Approved report recorded in Relay8 Coordinator memory.',
            'graph8_response' =>
                $response->json(),
        ];
    }

    private function contactId(
        Recommendation $recommendation,
        array $target
    ): mixed {
        $contactId = $target['contact_id']
            ?? null;

        if (filled($contactId)) {
            return $contactId;
        }

        $payload = is_array(
            $recommendation->action_payload
        )
            ? $recommendation->action_payload
            : [];

        $contactId = Arr::get(
            $payload,
            'graph8_contact_id'
        );

        if (filled($contactId)) {
            return $contactId;
        }

        $contactIds = Arr::get(
            $payload,
            'graph8_contact_ids',
            []
        );

        if (is_array($contactIds)) {
            $contactId = collect($contactIds)
                ->filter(
                    fn (mixed $value): bool =>
                        filled($value)
                )
                ->first();

            if (filled($contactId)) {
                return $contactId;
            }
        }

        return config(
            'graph8.relay.fallback_contact_id'
        );
    }
}