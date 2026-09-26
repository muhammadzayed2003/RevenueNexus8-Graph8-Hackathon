<?php

namespace App\Http\Controllers;

use App\Models\Graph8Record;
use App\Services\Graph8Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use RuntimeException;
use Throwable;

class Graph8SyncController extends Controller
{
    public function sync(
        Request $request,
        Graph8Service $graph8Service
    ): RedirectResponse {
        try {
            $totals = [
                'companies' => $this->syncRecordType(
                    'company',
                    $graph8Service->fetchCompanies()
                ),
                'contacts' => $this->syncRecordType(
                    'contact',
                    $graph8Service->fetchContacts()
                ),
                'deals' => $this->syncRecordType(
                    'deal',
                    $graph8Service->fetchDeals()
                ),
            ];

            return redirect()
                ->route('dashboard')
                ->with(
                    'success',
                    "graph8 sync completed: {$totals['companies']} companies, {$totals['contacts']} contacts and {$totals['deals']} deals."
                );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('dashboard')
                ->withErrors([
                    'graph8' => $exception->getMessage(),
                ]);
        }
    }

    private function syncRecordType(
        string $recordType,
        array $response
    ): int {
        $records = $this->extractRecords(
            $response,
            $recordType
        );

        $synced = 0;

        foreach ($records as $record) {
            if (! is_array($record)) {
                continue;
            }

            $externalId = $this->recordId(
                $record,
                $recordType
            );

            if (blank($externalId)) {
                continue;
            }

            Graph8Record::updateOrCreate(
                [
                    'record_type' => $recordType,
                    'external_id' => (string) $externalId,
                ],
                [
                    'name' => $this->recordName(
                        $record,
                        $recordType
                    ),
                    'payload' => $record,
                    'synced_at' => now(),
                ]
            );

            $synced++;
        }

        return $synced;
    }

    private function extractRecords(
        array $response,
        string $recordType
    ): array {
        $plural = match ($recordType) {
            'company' => 'companies',
            'contact' => 'contacts',
            'deal' => 'deals',
            default => $recordType.'s',
        };

        $candidatePaths = [
            'data',
            'items',
            'results',
            $plural,
            "data.{$plural}",
            'data.items',
            'data.results',
            'response.data',
            "response.{$plural}",
        ];

        foreach ($candidatePaths as $path) {
            $value = Arr::get($response, $path);

            if (is_array($value) && array_is_list($value)) {
                return $value;
            }
        }

        if (array_is_list($response)) {
            return $response;
        }

        throw new RuntimeException(
            "Unable to locate {$plural} in the graph8 API response."
        );
    }

    private function recordId(
        array $record,
        string $recordType
    ): mixed {
        $candidateKeys = [
            'id',
            'uuid',
            'external_id',
            'externalId',
            "{$recordType}_id",
            "{$recordType}Id",
        ];

        foreach ($candidateKeys as $key) {
            $value = Arr::get($record, $key);

            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function recordName(
        array $record,
        string $recordType
    ): ?string {
        $candidateKeys = match ($recordType) {
            'company' => [
                'name',
                'company_name',
                'companyName',
                'domain',
            ],
            'contact' => [
                'name',
                'full_name',
                'fullName',
                'email',
            ],
            'deal' => [
                'name',
                'title',
                'deal_name',
                'dealName',
            ],
            default => [
                'name',
                'title',
            ],
        };

        foreach ($candidateKeys as $key) {
            $value = Arr::get($record, $key);

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }
}