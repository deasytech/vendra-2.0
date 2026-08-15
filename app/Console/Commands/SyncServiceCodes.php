<?php

namespace App\Console\Commands;

use App\Models\ServiceCode;
use App\Services\TaxlyResourceOptions;
use App\Services\TaxlyService;
use Illuminate\Console\Command;
use Throwable;

class SyncServiceCodes extends Command
{
    protected $signature = 'taxly:sync-service-codes';

    protected $description = 'Sync the Taxly services-codes (ISIC) taxonomy into the local database';

    public function handle(): int
    {
        $service = new TaxlyService();

        try {
            $first = $service->getServiceCodes(1);
        } catch (Throwable $e) {
            $this->error('Failed to fetch service codes page 1: ' . $e->getMessage());

            return self::FAILURE;
        }

        $firstPaginator = $first['data'] ?? [];
        $lastPage = (int) ($firstPaginator['last_page'] ?? 1);
        $items = collect(TaxlyResourceOptions::normalize($firstPaginator['data'] ?? [], ['code', 'service_code', 'isic_code']));

        $this->info("Syncing service codes across {$lastPage} upstream pages...");
        $bar = $this->output->createProgressBar($lastPage);
        $bar->advance();

        foreach (array_chunk(range(2, max($lastPage, 1)), 20) as $chunk) {
            if ($lastPage < 2) {
                break;
            }

            foreach ($service->getServiceCodesPages($chunk) as $response) {
                $paginator = $response['data'] ?? [];
                $items = $items->merge(TaxlyResourceOptions::normalize($paginator['data'] ?? [], ['code', 'service_code', 'isic_code']));
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();

        $items = $items->unique('code')->values();

        $now = now();

        $items->chunk(200)->each(function ($chunk) use ($now) {
            ServiceCode::query()->upsert(
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

        $this->info("Synced {$items->count()} service codes.");

        return self::SUCCESS;
    }
}
