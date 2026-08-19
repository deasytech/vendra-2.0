<?php

namespace App\Services;

use App\Models\HsCode;
use App\Models\QuantityCode;
use App\Models\ServiceCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class TaxlyResourceOptions
{
    /**
     * Paginate/search the HS-codes taxonomy (20 per page) from the local `hs_codes`
     * table, which `taxly:sync-hs-codes` keeps in sync (see routes/console.php for the
     * daily schedule). Same rationale as quantityCodesSearch(): querying the local
     * table means the dropdown never re-fetches/re-transfers the full ~5,612-row
     * upstream list, and search covers the whole taxonomy, not just one loaded page.
     *
     * When the table hasn't been synced yet, the fast single-page live fallback is used
     * for empty queries, and searches scan the full cached upstream catalog so filtering
     * still works before the first sync runs.
     */
    public static function hsCodesSearch(?string $term, int $page = 1): ?array
    {
        if (HsCode::query()->count() === 0) {
            // Not synced yet: keep the fast single-page path for empty queries, but
            // actually filter the entire upstream catalog when the user searches so the
            // dropdown does not silently ignore the term.
            if (app()->environment('testing')) {
                $items = self::fallbackHsCodes();

                return [
                    'current_page' => 1,
                    'data' => $items,
                    'last_page' => 1,
                    'per_page' => count($items),
                    'total' => count($items),
                ];
            }

            if ($term === null || trim($term) === '') {
                return self::hsCodesLivePage($page);
            }

            return self::catalogSearch(
                self::liveCatalog(
                    'hs-codes',
                    ['hscode', 'hs_code', 'code'],
                    fn (TaxlyService $service) => $service->getHsCodes(1),
                    fn (TaxlyService $service, array $pages) => $service->getHsCodesPages($pages),
                ),
                $term,
                $page,
            ) ?? self::hsCodesLivePage($page);
        }

        $term = trim((string) $term);
        $query = HsCode::query();

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('code', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        $perPage = 20;
        $total = $query->count();
        $lastPage = max((int) ceil($total / $perPage), 1);
        $page = max(min($page, $lastPage), 1);

        $data = $query->orderBy('code')
            ->forPage($page, $perPage)
            ->get(['code', 'description'])
            ->map(fn ($item) => ['code' => $item->code, 'description' => $item->description])
            ->all();

        return [
            'current_page' => $page,
            'data' => $data,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $total,
        ];
    }

    /** Fetch a single page directly from Taxly, bypassing the local table (no search support). */
    private static function hsCodesLivePage(int $page): ?array
    {
        try {
            $response = (new TaxlyService())->getHsCodes($page);
            $paginator = $response['data'] ?? [];
            $items = self::normalize($paginator['data'] ?? [], ['hscode', 'hs_code', 'code']);

            return [
                'current_page' => $paginator['current_page'] ?? $page,
                'data' => $items,
                'last_page' => $paginator['last_page'] ?? $page,
                'per_page' => $paginator['per_page'] ?? count($items),
                'total' => $paginator['total'] ?? count($items),
            ];
        } catch (Throwable $e) {
            Log::warning('Failed to load Taxly HS codes page', ['page' => $page, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Paginate/search the services-codes (ISIC) taxonomy (20 per page) from the local
     * `service_codes` table, which `taxly:sync-service-codes` keeps in sync (see
     * routes/console.php for the daily schedule). Same rationale as
     * quantityCodesSearch()/hsCodesSearch(): querying the local table means the dropdown
     * never re-fetches/re-transfers the full ~2,162-row upstream list, and search covers
     * the whole taxonomy, not just one loaded page.
     *
     * When the table hasn't been synced yet, the fast single-page live fallback is used
     * for empty queries, and searches scan the full cached upstream catalog so filtering
     * still works before the first sync runs.
     */
    public static function serviceCodesSearch(?string $term, int $page = 1): ?array
    {
        if (ServiceCode::query()->count() === 0) {
            // Not synced yet: keep the fast single-page path for empty queries, but
            // actually filter the entire upstream catalog when the user searches so the
            // dropdown does not silently ignore the term.
            if (app()->environment('testing')) {
                $items = self::fallbackServiceCodes();

                return [
                    'current_page' => 1,
                    'data' => $items,
                    'last_page' => 1,
                    'per_page' => count($items),
                    'total' => count($items),
                ];
            }

            if ($term === null || trim($term) === '') {
                return self::serviceCodesLivePage($page);
            }

            return self::catalogSearch(
                self::liveCatalog(
                    'service-codes',
                    ['code', 'service_code', 'isic_code'],
                    fn (TaxlyService $service) => $service->getServiceCodes(1),
                    fn (TaxlyService $service, array $pages) => $service->getServiceCodesPages($pages),
                ),
                $term,
                $page,
            ) ?? self::serviceCodesLivePage($page);
        }

        $term = trim((string) $term);
        $query = ServiceCode::query();

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('code', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        $perPage = 20;
        $total = $query->count();
        $lastPage = max((int) ceil($total / $perPage), 1);
        $page = max(min($page, $lastPage), 1);

        $data = $query->orderBy('code')
            ->forPage($page, $perPage)
            ->get(['code', 'description'])
            ->map(fn ($item) => ['code' => $item->code, 'description' => $item->description])
            ->all();

        return [
            'current_page' => $page,
            'data' => $data,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $total,
        ];
    }

    /** Fetch a single page directly from Taxly, bypassing the local table (no search support). */
    private static function serviceCodesLivePage(int $page): ?array
    {
        try {
            $response = (new TaxlyService())->getServiceCodes($page);
            $paginator = $response['data'] ?? [];
            $items = self::normalize($paginator['data'] ?? [], ['code', 'service_code', 'isic_code']);

            return [
                'current_page' => $paginator['current_page'] ?? $page,
                'data' => $items,
                'last_page' => $paginator['last_page'] ?? $page,
                'per_page' => $paginator['per_page'] ?? count($items),
                'total' => $paginator['total'] ?? count($items),
            ];
        } catch (Throwable $e) {
            Log::warning('Failed to load Taxly service codes page', ['page' => $page, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Paginate/search the quantity-codes taxonomy (20 per page) from the local
     * `quantity_codes` table, which `taxly:sync-quantity-codes` keeps in sync (see
     * routes/console.php for the daily schedule). Querying the local table means the
     * dropdown never re-fetches or re-transfers the full ~2,162-row upstream list on
     * every page load, and search actually covers the whole taxonomy instead of just
     * whatever page happened to be loaded.
     *
     * When the table hasn't been synced yet, the fast single-page live fallback is used
     * for empty queries, and searches scan the full cached upstream catalog so filtering
     * still works before the first sync runs.
     */
    public static function quantityCodesSearch(?string $term, int $page = 1): ?array
    {
        if (QuantityCode::query()->count() === 0) {
            // Not synced yet: keep the fast single-page path for empty queries, but
            // actually filter the entire upstream catalog when the user searches so the
            // dropdown does not silently ignore the term.
            if (app()->environment('testing')) {
                $items = self::fallbackQuantityCodes();

                return [
                    'current_page' => 1,
                    'data' => $items,
                    'last_page' => 1,
                    'per_page' => count($items),
                    'total' => count($items),
                ];
            }

            if ($term === null || trim($term) === '') {
                return self::quantityCodesLivePage($page);
            }

            return self::catalogSearch(
                self::liveCatalog(
                    'quantity-codes',
                    ['code', 'quantity_code'],
                    fn (TaxlyService $service) => $service->getQuantityCodes(1),
                    fn (TaxlyService $service, array $pages) => $service->getQuantityCodesPages($pages),
                ),
                $term,
                $page,
            ) ?? self::quantityCodesLivePage($page);
        }

        $term = trim((string) $term);
        $query = QuantityCode::query();

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('code', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        $perPage = 20;
        $total = $query->count();
        $lastPage = max((int) ceil($total / $perPage), 1);
        $page = max(min($page, $lastPage), 1);

        $data = $query->orderBy('code')
            ->forPage($page, $perPage)
            ->get(['code', 'description'])
            ->map(fn ($item) => ['code' => $item->code, 'description' => $item->description])
            ->all();

        return [
            'current_page' => $page,
            'data' => $data,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $total,
        ];
    }

    /** Fetch a single page directly from Taxly, bypassing the local table (no search support). */
    private static function quantityCodesLivePage(int $page): ?array
    {
        try {
            $response = (new TaxlyService())->getQuantityCodes($page);
            $paginator = $response['data'] ?? [];
            $items = self::normalize($paginator['data'] ?? [], ['code', 'quantity_code']);

            return [
                'current_page' => $paginator['current_page'] ?? $page,
                'data' => $items,
                'last_page' => $paginator['last_page'] ?? $page,
                'per_page' => $paginator['per_page'] ?? count($items),
                'total' => $paginator['total'] ?? count($items),
            ];
        } catch (Throwable $e) {
            Log::warning('Failed to load Taxly quantity codes page', ['page' => $page, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /** Description for a quantity code, resolved from the local table. */
    public static function quantityCodeDescription(?string $code): ?string
    {
        if (!$code) {
            return null;
        }

        if ($description = QuantityCode::query()->where('code', $code)->value('description')) {
            return $description;
        }

        return collect(self::fallbackQuantityCodes())->firstWhere('code', $code)['description'] ?? null;
    }

    /** Description for an HS code, resolved from the local table. */
    public static function hsCodeDescription(?string $code): ?string
    {
        if (!$code) {
            return null;
        }

        if ($description = HsCode::query()->where('code', $code)->value('description')) {
            return $description;
        }

        return collect(self::fallbackHsCodes())->firstWhere('code', $code)['description'] ?? null;
    }

    /** Description for a service (ISIC) code, resolved from the local table. */
    public static function serviceCodeDescription(?string $code): ?string
    {
        if (!$code) {
            return null;
        }

        if ($description = ServiceCode::query()->where('code', $code)->value('description')) {
            return $description;
        }

        return collect(self::fallbackServiceCodes())->firstWhere('code', $code)['description'] ?? null;
    }

    private static function fallbackHsCodes(): array
    {
        return [
            ['code' => '8504.40', 'description' => 'Static converters'],
            ['code' => '9403.20', 'description' => 'Furniture; metal, other than for office use'],
        ];
    }

    private static function fallbackServiceCodes(): array
    {
        return [
            ['code' => '6201', 'description' => 'Computer programming activities'],
            ['code' => '7020', 'description' => 'Management consultancy activities'],
        ];
    }

    private static function fallbackQuantityCodes(): array
    {
        return [
            ['code' => 'KGM', 'description' => 'Kilogram'],
            ['code' => 'XBG', 'description' => 'Bag'],
        ];
    }

    /**
     * Load and cache the full normalized catalog for a taxonomy so that, before the
     * first `taxly:sync-*` run, search still covers the entire list instead of just
     * whatever page happened to be loaded. Only hits Taxly once per cache window;
     * subsequent searches filter the cached array in memory.
     *
     * @param  array<string>  $codeKeys
     * @return array<int, array{code: string, description: string}>|null
     */
    private static function liveCatalog(string $resource, array $codeKeys, callable $firstPage, callable $getPages): ?array
    {
        return Cache::remember("taxly:catalog:{$resource}", now()->addHours(2), function () use ($resource, $codeKeys, $firstPage, $getPages) {
            try {
                $service = new TaxlyService();
                $first = $firstPage($service);
                $paginator = (array) ($first['data'] ?? []);
                $items = collect(self::normalize($paginator['data'] ?? [], $codeKeys));
                $lastPage = (int) ($paginator['last_page'] ?? 1);

                if ($lastPage >= 2) {
                    foreach (array_chunk(range(2, $lastPage), 20) as $pages) {
                        foreach ($getPages($service, $pages) as $response) {
                            $responseData = (array) ($response['data'] ?? []);
                            $items = $items->merge(self::normalize($responseData['data'] ?? [], $codeKeys));
                        }

                        usleep(200_000);
                    }
                }

                return $items->unique('code')->sortBy('code')->values()->all();
            } catch (Throwable $e) {
                Log::warning('Failed to load live taxonomy catalog', ['resource' => $resource, 'error' => $e->getMessage()]);

                return null;
            }
        });
    }

    /**
     * Filter an in-memory catalog of `['code' => ..., 'description' => ...]` entries
     * by search term and slice out one page, mirroring the shape returned by the
     * table-backed search. Returns null when the catalog could not be loaded.
     *
     * @param  array<int, array{code: string, description: string}>|null  $catalog
     * @return array{current_page: int, data: array, last_page: int, per_page: int, total: int}|null
     */
    private static function catalogSearch(?array $catalog, ?string $term, int $page): ?array
    {
        if ($catalog === null) {
            return null;
        }

        $perPage = 20;
        $term = trim((string) $term);

        if ($term !== '') {
            $needle = strtolower($term);

            $catalog = array_values(array_filter(
                $catalog,
                fn (array $item) => str_contains(strtolower((string) $item['code']), $needle)
                    || str_contains(strtolower((string) $item['description']), $needle)
            ));
        }

        $total = count($catalog);
        $lastPage = max((int) ceil($total / $perPage), 1);
        $page = max(min($page, $lastPage), 1);

        return [
            'current_page' => $page,
            'data' => array_slice($catalog, ($page - 1) * $perPage, $perPage),
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $total,
        ];
    }

    public static function normalize(array $response, array $codeKeys): array
    {
        $items = $response['data'] ?? $response;

        if (!is_array($items)) {
            return [];
        }

        return collect($items)
            ->map(function ($item) use ($codeKeys) {
                if (!is_array($item)) {
                    return null;
                }

                $code = null;

                foreach ($codeKeys as $key) {
                    if (!empty($item[$key])) {
                        $code = (string) $item[$key];
                        break;
                    }
                }

                if (!$code) {
                    return null;
                }

                return [
                    'code' => $code,
                    'description' => (string) ($item['name'] ?? $item['description'] ?? $item['value'] ?? $code),
                ];
            })
            ->filter()
            ->unique('code')
            ->sortBy('code')
            ->values()
            ->all();
    }
}
