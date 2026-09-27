<?php

namespace App\Http\Controllers;

use App\Models\Graph8Event;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Throwable;

class Graph8WebhookController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        if (! $this->hasValidSignature($request)) {
            return response()->json([
                'received' => false,
                'message' => 'Invalid graph8 webhook signature.',
            ], 401);
        }

        $payload = $request->json()->all();

        $eventType = $this->firstValue($payload, [
            'event',
            'event_type',
            'eventType',
            'type',
            'event.type',
            'data.eda_event',
            'data.g8_correlation.event',
            'name',
        ]) ?? 'unknown';

        $externalId = $this->firstValue($payload, [
            'id',
            'event_id',
            'eventId',
            'event.id',
            'data.idempotency_key',
            'data.g8_correlation.idempotency_key',
        ]);

        $companyId = $this->firstValue($payload, [
            'company_id',
            'companyId',
            'company.id',
            'data.company_id',
            'data.companyId',
            'data.company.id',
        ]);

        $contactId = $this->firstValue($payload, [
            'contact_id',
            'contactId',
            'contact.id',
            'data.contact_id',
            'data.contactId',
            'data.contact.id',
        ]);

        $dealId = $this->firstValue($payload, [
            'deal_id',
            'dealId',
            'deal.id',
            'data.deal_id',
            'data.dealId',
            'data.deal.id',
        ]);

        $occurredAtValue = $this->firstValue($payload, [
            'timestamp',
            'occurred_at',
            'occurredAt',
            'created_at',
            'createdAt',
            'event.created_at',
            'data.changed_at',
        ]);

        $occurredAt = now();

        if ($occurredAtValue) {
            try {
                $occurredAt = Carbon::parse($occurredAtValue);
            } catch (Throwable) {
                $occurredAt = now();
            }
        }

        $attributes = [
            'event_type' => (string) $eventType,
            'source' => 'graph8',
            'company_id' => $companyId
                ? (string) $companyId
                : null,
            'contact_id' => $contactId
                ? (string) $contactId
                : null,
            'deal_id' => $dealId
                ? (string) $dealId
                : null,
            'payload' => $payload,
            'occurred_at' => $occurredAt,
            'processed' => false,
        ];

        if ($externalId) {
            $event = Graph8Event::updateOrCreate(
                [
                    'external_id' => (string) $externalId,
                ],
                $attributes
            );
        } else {
            $event = Graph8Event::create([
                ...$attributes,
                'external_id' => null,
            ]);
        }

        return response()->json([
            'received' => true,
            'event_id' => $event->id,
            'event_type' => $event->event_type,
        ], 202);
    }

    private function hasValidSignature(Request $request): bool
    {
        $secret = (string) config(
            'services.graph8.webhook_secret',
            ''
        );

        if ($secret === '') {
            return false;
        }

        $configuredHeader = (string) config(
            'services.graph8.webhook_signature_header',
            'X-G8-Signature'
        );

        $providedSignature = $request->header($configuredHeader)
            ?? $request->header('X-G8-Signature')
            ?? $request->header('X-Graph8-Signature');

        if (! is_string($providedSignature)
            || trim($providedSignature) === '') {
            return false;
        }

        $rawBody = $request->getContent();

        $expectedHex = hash_hmac(
            'sha256',
            $rawBody,
            $secret
        );

        $expectedBase64 = base64_encode(
            hash_hmac(
                'sha256',
                $rawBody,
                $secret,
                true
            )
        );

        foreach ($this->signatureCandidates(
            $providedSignature
        ) as $candidate) {
            if (hash_equals(
                $expectedHex,
                strtolower($candidate)
            )) {
                return true;
            }

            if (hash_equals(
                $expectedBase64,
                $candidate
            )) {
                return true;
            }
        }

        return false;
    }

    private function signatureCandidates(
        string $signature
    ): array {
        $candidates = [];

        foreach (explode(',', trim($signature)) as $part) {
            $part = trim($part);

            if ($part === '') {
                continue;
            }

            $candidates[] = $part;

            if (str_contains($part, '=')) {
                [$prefix, $value] = explode(
                    '=',
                    $part,
                    2
                );

                if (in_array(
                    strtolower(trim($prefix)),
                    ['sha256', 'v1'],
                    true
                )) {
                    $candidates[] = trim($value);
                }
            }
        }

        return array_values(array_unique($candidates));
    }

    private function firstValue(
        array $payload,
        array $keys
    ): mixed {
        foreach ($keys as $key) {
            $value = Arr::get($payload, $key);

            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }
}