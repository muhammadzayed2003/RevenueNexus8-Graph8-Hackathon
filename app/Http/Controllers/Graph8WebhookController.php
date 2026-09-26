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
        $payload = $request->all();

        $eventType = $this->firstValue($payload, [
            'event_type',
            'eventType',
            'type',
            'event.type',
            'name',
        ]) ?? 'unknown';

        $externalId = $this->firstValue($payload, [
            'event_id',
            'eventId',
            'id',
            'event.id',
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
            'occurred_at',
            'occurredAt',
            'created_at',
            'createdAt',
            'timestamp',
            'event.created_at',
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