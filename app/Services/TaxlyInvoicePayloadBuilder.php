<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Arr;

class TaxlyInvoicePayloadBuilder
{
  public static function build(Invoice $invoice): array
  {
    $organization = $invoice->organization;
    $customer = $invoice->customer;
    $customerParty = $invoice->accounting_customer_party ?? [];
    $supplier = $invoice->accounting_supplier_party ?? [];

    return [
      'channel' => 'api',
      'invoice_reference' => $invoice->invoice_reference,
      'irn' => $invoice->irn,
      'issue_date' => $invoice->issue_date,
      'due_date' => $invoice->due_date,
      'issue_time' => $invoice->issue_time ?? now()->format('H:i:s'),
      'invoice_type_code' => $invoice->invoice_type_code,
      'payment_status' => $invoice->payment_status,
      'note' => $invoice->note,
      'tax_point_date' => $invoice->tax_point_date,
      'document_currency_code' => $invoice->document_currency_code,
      'tax_currency_code' => $invoice->tax_currency_code,
      'accounting_cost' => $invoice->accounting_cost,
      'buyer_reference' => $invoice->buyer_reference,
      'invoice_delivery_period' => [
        'start_date' => $invoice->delivery_start_date,
        'end_date' => $invoice->delivery_end_date,
      ],
      'order_reference' => $invoice->order_reference,
      'billing_reference' => [
        [
          'irn' => $invoice->irn,
          'issue_date' => $invoice->issue_date,
        ],
      ],
      'dispatch_document_reference' => [
        'irn' => $invoice->irn,
        'issue_date' => $invoice->issue_date,
      ],
      'receipt_document_reference' => [
        'irn' => $invoice->irn,
        'issue_date' => $invoice->issue_date,
      ],
      'originator_document_reference' => [
        'irn' => $invoice->irn,
        'issue_date' => $invoice->issue_date,
      ],
      'contract_document_reference' => [
        'irn' => $invoice->irn,
        'issue_date' => $invoice->issue_date,
      ],
      '_document_reference' => [
        [
          'irn' => $invoice->irn,
          'issue_date' => $invoice->issue_date,
        ],
      ],
      'accounting_supplier_party' => [
        'party_name' => Arr::get($supplier, 'party_name', $organization->legal_name),
        'tin' => Arr::get($supplier, 'tin', $organization->tin),
        'email' => Arr::get($supplier, 'email', $organization->email),
        'telephone' => Arr::get($supplier, 'telephone', $organization->phone),
        'business_description' => Arr::get($supplier, 'business_description', $organization->business_description),
        'postal_address' => [
          'street_name' => $organization->address_line_1,
          'city_name' => $organization->city_name,
          'postal_zone' => $organization->postal_code,
          'country' => 'NG',
        ],
      ],
      'accounting_customer_party' => $customer ? $customer->toPartyObject() : [
        'party_name' => Arr::get($customerParty, 'party_name'),
        'tin' => Arr::get($customerParty, 'tin'),
        'email' => Arr::get($customerParty, 'email'),
        'telephone' => Arr::get($customerParty, 'telephone'),
        'business_description' => Arr::get($customerParty, 'business_description'),
        'postal_address' => Arr::get($customerParty, 'postal_address', []),
      ],
      'actual_delivery_date' => $invoice->actual_delivery_date,
      'payment_means' => $invoice->payment_means ?? [
        [
          'payment_means_code' => '10',
          'payment_due_date' => $invoice->issue_date,
        ],
      ],
      'payment_terms_note' => $invoice->payment_terms_note,
      'allowance_charge' => $invoice->allowance_charge ?? [
        [
          'charge_indicator' => true,
          'amount' => $invoice->legal_monetary_total['payable_amount'] ?? 0,
        ],
      ],
      'tax_total' => $invoice->tax_totals->map(function ($tax) {
        return [
          'tax_amount' => $tax->tax_amount,
          'tax_subtotal' => $tax->subtotals ? $tax->subtotals->map(fn($sub) => [
            'taxable_amount' => $sub->taxable_amount,
            'tax_amount' => $sub->tax_amount,
            'tax_category' => [
              'id' => $sub->tax_category_id,
              'percent' => $sub->tax_percent,
            ],
          ]) : [],
        ];
      }),
      'legal_monetary_total' => [
        'tax_exclusive_amount' => $invoice->legal_monetary_total['tax_exclusive_amount'] ?? $invoice->lines->sum('line_total') ?? 0,
        'tax_inclusive_amount' => $invoice->legal_monetary_total['tax_inclusive_amount'] ?? ($invoice->lines->sum('line_total') + $invoice->tax_totals->sum('tax_amount')) ?? 0,
        'line_extension_amount' => $invoice->legal_monetary_total['line_extension_amount'] ?? $invoice->lines->sum('line_total') ?? 0,
        'payable_amount' => $invoice->legal_monetary_total['payable_amount'] ?? ($invoice->lines->sum('line_total') + $invoice->tax_totals->sum('tax_amount')) ?? 0,
      ],
      'invoice_line' => $invoice->lines->map(function ($line) {
        return [
          'hsn_code' => $line->hsn_code,
          'product_category' => $line->product_category,
          'discount_rate' => $line->discount_rate,
          'discount_amount' => $line->discount_amount,
          'fee_rate' => $line->fee_rate,
          'fee_amount' => $line->fee_amount,
          'invoiced_quantity' => $line->invoiced_quantity,
          'line_extension_amount' => $line->line_extension_amount,
          'item' => [
            'name' => Arr::get($line->item, 'name'),
            'description' => Arr::get($line->item, 'description'),
            'sellers_item_identification' => Arr::get($line->item, 'sellers_item_identification'),
          ],
          'price' => [
            'price_amount' => Arr::get($line->price, 'price_amount'),
            'base_quantity' => Arr::get($line->price, 'base_quantity', 1),
            'price_unit' => Arr::get($line->price, 'price_unit', 'NGN per 1'),
          ],
        ];
      }),
    ];
  }
}
