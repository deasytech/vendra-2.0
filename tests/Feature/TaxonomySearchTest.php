<?php

use App\Models\HsCode;
use App\Models\QuantityCode;
use App\Models\ServiceCode;
use App\Services\TaxlyResourceOptions;

it('filters hs codes from the local table by code', function () {
    HsCode::create(['code' => '8471.30', 'description' => 'Automatic data processing machines; portable']);
    HsCode::create(['code' => '8471.41', 'description' => 'Automatic data processing machines; processing units']);
    HsCode::create(['code' => '0101.21', 'description' => 'Horses; live']);

    $result = TaxlyResourceOptions::hsCodesSearch('8471');

    expect($result['total'])->toBe(2)
        ->and($result['data'])->toHaveCount(2)
        ->and(array_column($result['data'], 'code'))->toEqual(['8471.30', '8471.41']);
});

it('filters service codes from the local database by description', function () {
    ServiceCode::create(['code' => '6201', 'description' => 'Computer programming activities']);
    ServiceCode::create(['code' => '7020', 'description' => 'Management consultancy activities']);

    $result = TaxlyResourceOptions::serviceCodesSearch('computer');

    expect($result['total'])->toBe(1)
        ->and($result['data'][0]['code'])->toBe('6201');
});

it('returns every quantity code when no search term is given', function () {
    QuantityCode::create(['code' => 'KGM', 'description' => 'Kilogram']);
    QuantityCode::create(['code' => 'LTR', 'description' => 'Litre']);

    $result = TaxlyResourceOptions::quantityCodesSearch(null);

    expect($result['total'])->toBe(2)
        ->and(array_column($result['data'], 'code'))->toEqual(['KGM', 'LTR']);
});

it('paginates quantity code search results', function () {
    for ($i = 1; $i <= 21; $i++) {
        QuantityCode::create(['code' => "L{$i}", 'description' => "Litre variant {$i}"]);
    }

    $result = TaxlyResourceOptions::quantityCodesSearch('Litre', 2);

    expect($result['total'])->toBe(21)
        ->and($result['last_page'])->toBe(2)
        ->and($result['current_page'])->toBe(2)
        ->and($result['data'])->toHaveCount(1);
});