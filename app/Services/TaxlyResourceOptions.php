<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class TaxlyResourceOptions
{
    public static function hsCodes(): array
    {
        if (app()->environment('testing')) {
            return self::fallbackHsCodes();
        }

        return Cache::remember('taxly.resource_options.hs_codes', now()->addDay(), function () {
            try {
                return self::normalize((new TaxlyService())->getHsCodes(), ['hscode', 'hs_code', 'code']);
            } catch (Throwable $e) {
                Log::warning('Failed to load Taxly HS codes', ['error' => $e->getMessage()]);

                return self::fallbackHsCodes();
            }
        });
    }

    public static function serviceCodes(): array
    {
        if (app()->environment('testing')) {
            return self::fallbackServiceCodes();
        }

        return Cache::remember('taxly.resource_options.service_codes', now()->addDay(), function () {
            try {
                return self::normalize((new TaxlyService())->getServiceCodes(), ['code', 'service_code', 'isic_code']);
            } catch (Throwable $e) {
                Log::warning('Failed to load Taxly service codes', ['error' => $e->getMessage()]);

                return self::fallbackServiceCodes();
            }
        });
    }

    public static function hsCodeDescription(?string $code): ?string
    {
        return self::descriptionFor(self::hsCodes(), $code);
    }

    public static function serviceCodeDescription(?string $code): ?string
    {
        return self::descriptionFor(self::serviceCodes(), $code);
    }

    private static function descriptionFor(array $options, ?string $code): ?string
    {
        if (!$code) {
            return null;
        }

        $match = collect($options)->firstWhere('code', $code);

        return $match['description'] ?? null;
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

    private static function normalize(array $response, array $codeKeys): array
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
                    'description' => (string) ($item['description'] ?? $item['name'] ?? $item['value'] ?? $code),
                ];
            })
            ->filter()
            ->unique('code')
            ->sortBy('code')
            ->values()
            ->all();
    }
}
