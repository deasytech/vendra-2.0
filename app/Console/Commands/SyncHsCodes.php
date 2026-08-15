<?php

namespace App\Console\Commands;

use App\Models\HsCode;
use App\Services\TaxlyResourceOptions;
use App\Services\TaxlyService;
use Illuminate\Console\Command;
use Throwable;

class SyncHsCodes extends Command
{
    protected $signature = 'taxly:sync-hs-codes';

    protected $description = 'Sync the Taxly hs-codes taxonomy into the local database';

    public function handle(): int
    {
        $service = new TaxlyService();

        try {
            $first = $service->getHsCodes(1);
        } catch (Throwable $e) {
            $this->error('Failed to fetch HS codes page 1: ' . $e->getMessage());

            return self::FAILURE;
        }

        $firstPaginator = $first['data'] ?? [];
        $lastPage = (int) ($firstPaginator['last_page'] ?? 1);
        $items = collect(TaxlyResourceOptions::normalize($firstPaginator['data'] ?? [], ['hscode', 'hs_code', 'code']));

        $this->info("Syncing HS codes across {$lastPage} upstream pages...");
        $bar = $this->output->createProgressBar($lastPage);
        $bar->advance();

        // The upstream hs-codes endpoint is noticeably more fragile than quantity-codes
        // under sustained concurrent load (times out past a few dozen pages in), so we
        // fetch in small batches with a short pause between them, and retry whatever
        // fails a couple of times before giving up on those pages.
        $pending = range(2, max($lastPage, 1));

        for ($attempt = 1; $attempt <= 4 && !empty($pending) && $lastPage >= 2; $attempt++) {
            $stillPending = [];

            foreach (array_chunk($pending, 5) as $chunk) {
                $responses = $service->getHsCodesPages($chunk);

                foreach ($chunk as $page) {
                    if (!isset($responses[$page])) {
                        $stillPending[] = $page;
                        continue;
                    }

                    $paginator = $responses[$page]['data'] ?? [];
                    $items = $items->merge(TaxlyResourceOptions::normalize($paginator['data'] ?? [], ['hscode', 'hs_code', 'code']));
                    $bar->advance();
                }

                usleep(200_000);
            }

            $pending = $stillPending;
        }

        $bar->finish();
        $this->newLine();

        if (!empty($pending)) {
            $this->warn(count($pending) . ' page(s) could not be fetched after retries: ' . implode(', ', $pending));
        }

        $items = $items->unique('code')->values();

        $now = now();

        $items->chunk(200)->each(function ($chunk) use ($now) {
            HsCode::query()->upsert(
                $chunk->map(fn ($item) => [
                    'code' => $item['code'],
                    'description' => $item['description'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all(),
                ['code'],
                ['description', 'updated_at']
            );
        });

        $this->info("Synced {$items->count()} HS codes.");

        return self::SUCCESS;
    }
}
