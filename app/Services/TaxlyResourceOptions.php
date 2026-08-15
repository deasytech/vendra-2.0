<?php

namespace App\Services;

use App\Models\HsCode;
use App\Models\QuantityCode;
use App\Models\ServiceCode;
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
     * Falls back to a single live upstream page (no search) if the table hasn't been
     * synced yet, so the dropdown still works before the first sync runs.
     */
    public static function hsCodesSearch(?string $term, int $page = 1): ?array
    {
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

        if (HsCode::query()->count() === 0) {
            return self::hsCodesLivePage($page);
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
     * Falls back to a single live upstream page (no search) if the table hasn't been
     * synced yet, so the dropdown still works before the first sync runs.
     */
    public static function serviceCodesSearch(?string $term, int $page = 1): ?array
    {
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

        if (ServiceCode::query()->count() === 0) {
            return self::serviceCodesLivePage($page);
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
     * Falls back to a single live upstream page (no search) if the table hasn't been
     * synced yet, so the dropdown still works before the first sync runs.
     */
    public static function quantityCodesSearch(?string $term, int $page = 1): ?array
    {
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

        if (QuantityCode::query()->count() === 0) {
            return self::quantityCodesLivePage($page);
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
