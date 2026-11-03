<?php

use App\Services\InvoiceSubmissionPayload;

function payloadInput(array $overrides = []): array
{
    return array_merge([
        'business_id' => 'biz',
        'invoice_reference' => 'INV1',
        'irn' => 'INV1-SVC-20260101',
        'issue_date' => '2026-01-01',
        'due_date' => '2026-01-31',
        'invoice_type_code' => '396',
        'document_currency_code' => 'NGN',
        'supplier' => ['party_name' => 'Supplier'],
        'customer' => null,
        'legal_monetary_total' => ['tax_exclusive_amount' => '1000', 'payable_amount' => '1075'],
        'lines' => [[
            'hsn_code' => '4412.41',
            'isic_code' => '2710',
            'product_category' => 'General Items',
            'invoiced_quantity' => 2,
            'price' => ['price_amount' => 500, 'price_unit' => ''],
            'item' => ['name' => 'Plywood', 'description' => ''],
        ]],
        'tax_subtotals' => [['category_id' => 'STANDARD_VAT', 'percent' => 7.5, 'taxable_amount' => 1000, 'tax_amount' => 75]],
    ], $overrides);
}

it('sends no allowance charge unless one was entered', function () {
    expect(InvoiceSubmissionPayload::build(payloadInput())['allowance_charge'])->toBe([]);
});

it('converts percent allowance charges using the tax exclusive amount', function () {
    $payload = InvoiceSubmissionPayload::build(payloadInput([
        'allowance_charges' => [['charge_indicator' => true, 'amount' => 10, 'amount_type' => 'percent']],
    ]));

    expect($payload['allowance_charge'][0]['amount'])->toBe(100.0);
});

it('sends only the HSN fields when an HSN code is set', function () {
    $line = InvoiceSubmissionPayload::build(payloadInput())['invoice_line'][0];

    expect($line)->toHaveKey('hsn_code')->not->toHaveKey('isic_code');
    expect($line['price']['price_unit'])->toBe('C62');
    expect($line['item']['description'])->toBe('Plywood');
});

it('adds a withholding tax entry and marks B2B when a customer is present', function () {
    $payload = InvoiceSubmissionPayload::build(payloadInput([
        'customer' => ['party_name' => 'Buyer'],
        'withholding' => ['rate' => 5, 'amount' => 50, 'taxable_amount' => 1000],
    ]));

    expect($payload['invoice_kind'])->toBe('B2B');
    expect($payload['tax_total'])->toHaveCount(2);
    expect($payload['tax_total'][1]['tax_subtotal'][0]['tax_category']['id'])->toBe('WITHHOLDING_TAX');
});

it('omits billing reference for non-note invoices', function () {
    expect(InvoiceSubmissionPayload::build(payloadInput(['related_invoice_id' => 5])))->not->toHaveKey('billing_reference');
});
