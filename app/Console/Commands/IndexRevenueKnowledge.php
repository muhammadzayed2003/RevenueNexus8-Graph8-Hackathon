<?php

namespace App\Console\Commands;

use App\Services\RevenueKnowledgeService;
use Illuminate\Console\Command;
use Throwable;

class IndexRevenueKnowledge extends Command
{
    protected $signature = 'evidence8:index';

    protected $description =
        'Index graph8 and RevenueNexus8 evidence in Qdrant';

    public function handle(
        RevenueKnowledgeService $knowledge
    ): int {
        $this->info(
            'Indexing RevenueNexus8 evidence...'
        );

        try {
            $count = $knowledge->indexAll(
                function (
                    string $title,
                    int $current,
                    int $total
                ): void {
                    $this->line(
                        "[{$current}/{$total}] {$title}"
                    );
                }
            );

            $this->newLine();
            $this->info(
                "{$count} evidence documents indexed successfully."
            );

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error(
                $exception->getMessage()
            );

            return self::FAILURE;
        }
    }
}