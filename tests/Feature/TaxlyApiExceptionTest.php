<?php

use App\Exceptions\TaxlyApiException;
use App\Services\TaxlyService;
use Illuminate\Support\Facades\Http;

it('surfaces FIRS details instead of the generic headline', function () {
    Http::fake(['*' => Http::response([
        'success' => false,
        'message' => 'FIRS Invoice validation failed',
        'data' => ['error' => ['details' => 'businessdescription must be at least 5']],
    ], 400)]);

    try {
        (new TaxlyService(new \App\Models\TaxlyCredential(['base_url' => 'https://taxly.test/api/v1'])))->validateInvoice([]);
    } catch (TaxlyApiException $e) {
        expect(TaxlyApiException::readable($e))->toBe('businessdescription must be at least 5');
        return;
    }
    $this->fail('exception expected');
});

it('flattens validation error bags into the details', function () {
    Http::fake(['*' => Http::response([
        'success' => false,
        'message' => 'The hsn_code field format is invalid. (and 1 more error)',
        'errors' => ['invoice_line.0.hsn_code' => ['hsn_code format is invalid'], 'x' => ['x is required']],
    ], 422)]);

    try {
        (new TaxlyService(new \App\Models\TaxlyCredential(['base_url' => 'https://taxly.test/api/v1'])))->validateInvoice([]);
    } catch (TaxlyApiException $e) {
        expect(TaxlyApiException::readable($e))->toBe('hsn_code format is invalid; x is required');
        return;
    }
    $this->fail('exception expected');
});

it('falls back to the message when there are no details', function () {
    expect(TaxlyApiException::readable(new TaxlyApiException('FIRS authentication failed')))->toBe('FIRS authentication failed');
});
