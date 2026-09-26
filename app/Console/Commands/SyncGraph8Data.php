<?php

namespace App\Console\Commands;

use App\Models\Graph8Record;
use App\Services\Graph8Service;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use RuntimeException;
use Throwable;

class SyncGraph8Data extends Command
{
    protected $signature = 'graph8:sync';

    protected $description = 'Sync all companies, contacts and deals from graph8';

    public function handle(Graph8Service $graph8Service): int
    {
        $this->info('Starting complete graph8 data sync...');

        try {
            $companies = $this->syncAllPages(
                'company',
                fn (int $page): array => $graph8Service
                    ->fetchCompanies([
                        'page' => $page,
                        'limit' => 200,
                    ])
            );

            $this->info("Companies synced: {$companies}");

            $contacts = $this->syncAllPages(
                'contact',
                fn (int $page): array => $graph8Service
                    ->fetchContacts([
                        'page' => $page,
                        'limit' => 200,
                    ])
            );

            $this->info("Contacts synced: {$contacts}");

            $deals = $this->syncAllPages(
                'deal',
                fn (int $page): array => $graph8Service
                    ->fetchDeals([
                        'page' => $page,
                        'limit' => 200,
                    ])
            );

            $this->info("Deals synced: {$deals}");

            $this->newLine();
            $this->info('Complete graph8 data sync finished successfully.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);

            $this->error(
                'graph8 sync failed: '.$exception->getMessage()
            );

            return self::FAILURE;
        }
    }

    private function syncAllPages(
        string $recordType,
        callable $fetchPage
    ): int {
        $page = 1;
        $totalSynced = 0;

        while (true) {
            $response = $fetchPage($page);

            $records = $this->extractRecords(
                $response,
                $recordType
            );

            if ($records === []) {
                break;
            }

            $pageSynced = $this->syncRecords(
                $recordType,
                $records
            );

            $totalSynced += $pageSynced;

            $this->line(
                ucfirst($recordType)
                ." page {$page}: {$pageSynced} records"
            );

            $hasNext = $this->hasNextPage($response);

            if (! $hasNext) {
                break;
            }

            $page++;
        }

        return $totalSynced;
    }

    private function syncRecords(
        string $recordType,
        array $records
    ): int {
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
                'work_email',
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