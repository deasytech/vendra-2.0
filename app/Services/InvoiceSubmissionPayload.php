<?php

namespace App\Services;

use App\Models\Invoice;

/**
 * Single source of truth for the payload sent to Taxly when validating or
 * submitting an invoice, whether it comes from form state (create/edit) or
 * from a saved draft.
 */
class InvoiceSubmissionPayload
{
    public const NOTE_TYPE_CODES = ['380', '384'];

    /**
     * @param array $input {
     *   business_id, invoice_reference, irn, issue_date, due_date, invoice_type_code,
     *   document_currency_code, payment_status, supplier, customer (?array),
     *   legal_monetary_total, lines (array), allowance_charges (array),
     *   tax_subtotals (array of {category_id, percent, taxable_amount, tax_amount}),
     *   withholding (?{enabled, rate, amount, taxable_amount}), related_invoice_id (?int)
     * }
     */
    public static function build(array $input): array
    {
        $total = self::normalizeMonetaryTotal($input['legal_monetary_total'] ?? []);
        $dueDate = $input['due_date'] ?? null;

        $payload = [
            'channel' => 'api',
            'business_id' => $input['business_id'] ?? null,
            'invoice_reference' => $input['invoice_reference'],
            'irn' => $input['irn'],
            'issue_date' => $input['issue_date'],
            'due_date' => $dueDate,
            'issue_time' => now()->format('H:i:s'),
            'invoice_type_code' => $input['invoice_type_code'],
            'document_currency_code' => $input['document_currency_code'],
            'tax_currency_code' => $input['document_currency_code'],
            'payment_status' => $input['payment_status'] ?? 'PENDING',
            'invoice_kind' => 'B2C',
            'accounting_supplier_party' => $input['supplier'],
            'legal_monetary_total' => $total,
            'invoice_line' => self::lines($input['lines'] ?? []),
            'payment_means' => [
                [
                    'payment_means_code' => '10',
                    'payment_due_date' => $dueDate ?: now()->addDays(30)->format('Y-m-d'),
                ],
            ],
            'allowance_charge' => self::allowanceCharges(
                $input['allowance_charges'] ?? [],
                $total['tax_exclusive_amount']
            ),
            'tax_total' => self::taxTotals($input['tax_subtotals'] ?? [], $input['withholding'] ?? null),
        ];

        if (!empty($input['customer'])) {
            $payload['accounting_customer_party'] = $input['customer'];
            $payload['invoice_kind'] = 'B2B';
        }

        if ($billing = self::billingReference($input['invoice_type_code'], $input['related_invoice_id'] ?? null)) {
            $payload['billing_reference'] = $billing;
        }

        return $payload;
    }

    /**
     * Adapt a saved invoice (e.g. a draft) to the builder input.
     */
    public static function fromInvoice(Invoice $invoice): array
    {
        $metadata = $invoice->metadata ?? [];
        $total = self::normalizeMonetaryTotal($invoice->legal_monetary_total ?? []);

        $customer = null;
        if ($invoice->customer_id || !empty($invoice->accounting_customer_party['party_name'])) {
            $customer = self::customerParty($invoice);
        }

        $withholding = null;
        if (!empty($metadata['withholding_tax_enabled'])) {
            $withholding = [
                'rate' => (float) ($metadata['withholding_tax_rate'] ?? 0),
                'amount' => (float) ($metadata['withholding_tax_amount'] ?? 0),
                'taxable_amount' => $total['tax_exclusive_amount'],
            ];
        }

        return [
            'business_id' => $invoice->organization->business_id ?? null,
            'invoice_reference' => $invoice->invoice_reference,
            'irn' => $invoice->irn,
            'issue_date' => $invoice->issue_date->format('Y-m-d'),
            'due_date' => $invoice->due_date?->format('Y-m-d'),
            'invoice_type_code' => $invoice->invoice_type_code,
            'document_currency_code' => $invoice->document_currency_code,
            'payment_status' => $invoice->payment_status,
            'supplier' => self::withBusinessDescription(
                $invoice->accounting_supplier_party ?? [],
                $invoice->organization->description ?? null
            ),
            'customer' => $customer,
            'legal_monetary_total' => $total,
            'lines' => $invoice->lines->map(fn ($line) => [
                'hsn_code' => $line->hsn_code,
                'isic_code' => $line->isic_code,
                'product_category' => $line->product_category,
                'service_category' => $line->service_category,
                'invoiced_quantity' => $line->invoiced_quantity,
                'price' => $line->price ?? [],
                'item' => $line->item ?? [],
            ])->all(),
            'allowance_charges' => $metadata['allowance_charges'] ?? [],
            'tax_subtotals' => self::storedTaxSubtotals($invoice, $total),
            'withholding' => $withholding,
            'related_invoice_id' => $invoice->related_invoice_id,
        ];
    }

    public static function isNoteType(?string $code): bool
    {
        return in_array($code, self::NOTE_TYPE_CODES, true);
    }

    /**
     * FIRS requires billing_reference (original invoice's irn/issue_date)
     * for Credit (380) and Debit (384) notes.
     */
    public static function billingReference(?string $typeCode, $relatedInvoiceId): ?array
    {
        if (!self::isNoteType($typeCode) || !$relatedInvoiceId) {
            return null;
        }

        $related = Invoice::find($relatedInvoiceId);
        if (!$related) {
            return null;
        }

        return [
            [
                'irn' => $related->irn,
                'issue_date' => optional($related->issue_date)->format('Y-m-d') ?? $related->issue_date,
            ],
        ];
    }

    private static function normalizeMonetaryTotal(array $total): array
    {
        $fields = ['tax_exclusive_amount', 'tax_inclusive_amount', 'line_extension_amount', 'payable_amount', 'charge_total_amount', 'allowance_total_amount', 'prepaid_amount'];
        foreach ($fields as $field) {
            $total[$field] = (float) ($total[$field] ?? 0);
        }

        return $total;
    }

    private static function lines(array $lines): array
    {
        $out = [];
        foreach (array_values($lines) as $index => $line) {
            $price = $line['price'] ?? [];
            $quantity = (float) ($line['invoiced_quantity'] ?? 0);
            $item = $line['item'] ?? [];

            $formatted = [
                'invoiced_quantity' => $quantity,
                'line_extension_amount' => (float) (($price['price_amount'] ?? 0) * $quantity),
                'item' => [
                    'name' => $item['name'] ?? '',
                    'description' => ($item['description'] ?? '') ?: ($item['name'] ?? ''),
                    'sellers_item_identification' => $item['sellers_item_identification'] ?? null,
                    'selected_tax' => $line['selected_tax'] ?? $item['selected_tax'] ?? 'STANDARD_VAT',
                ],
                'price' => [
                    'price_amount' => (float) ($price['price_amount'] ?? 0),
                    'base_quantity' => (float) ($price['base_quantity'] ?? 1),
                    'price_unit' => ($price['price_unit'] ?? null) ?: 'C62',
                ],
                'order' => $index,
            ];

            // Taxly accepts either an HSN (product) or an ISIC (service) code, never both
            if (!empty($line['hsn_code'])) {
                $formatted['hsn_code'] = $line['hsn_code'];
                $formatted['product_category'] = ($line['product_category'] ?? null)
                    ?: TaxlyResourceOptions::hsCodeCategory($line['hsn_code']);
            } else {
                $formatted['isic_code'] = $line['isic_code'] ?? null;
                $formatted['service_category'] = ($line['service_category'] ?? null)
                    ?: TaxlyResourceOptions::serviceCodeCategory($line['isic_code'] ?? null);
            }

            $out[] = $formatted;
        }

        return $out;
    }

    private static function allowanceCharges(array $charges, float $subTotal): array
    {
        return array_map(function ($charge) use ($subTotal) {
            $amount = (float) ($charge['amount'] ?? 0);
            if (($charge['amount_type'] ?? 'fixed') === 'percent') {
                $amount = round($subTotal * ($amount / 100), 2);
            }

            return [
                'charge_indicator' => (bool) ($charge['charge_indicator'] ?? true),
                'amount' => (float) $amount,
                'amount_type' => $charge['amount_type'] ?? 'fixed',
            ];
        }, array_values($charges));
    }

    /**
     * STANDARD_VAT must always be 7.5%, so withholding is a separate
     * WITHHOLDING_TAX entry rather than a reused category id.
     */
    private static function taxTotals(array $subtotals, ?array $withholding): array
    {
        $totals = array_map(fn ($sub) => [
            'tax_amount' => (float) ($sub['tax_amount'] ?? 0),
            'tax_subtotal' => [
                [
                    'taxable_amount' => (float) ($sub['taxable_amount'] ?? 0),
                    'tax_amount' => (float) ($sub['tax_amount'] ?? 0),
                    'tax_category' => [
                        'id' => $sub['category_id'] ?? 'STANDARD_VAT',
                        'percent' => (float) ($sub['percent'] ?? 7.5),
                    ],
                ],
            ],
        ], array_values($subtotals));

        if ($withholding && (float) ($withholding['amount'] ?? 0) > 0) {
            $totals[] = [
                'tax_amount' => (float) $withholding['amount'],
                'tax_subtotal' => [
                    [
                        'taxable_amount' => (float) ($withholding['taxable_amount'] ?? 0),
                        'tax_amount' => (float) $withholding['amount'],
                        'tax_category' => [
                            'id' => 'WITHHOLDING_TAX',
                            'percent' => (float) ($withholding['rate'] ?? 0),
                        ],
                    ],
                ],
            ];
        }

        return $totals;
    }

    private static function storedTaxSubtotals(Invoice $invoice, array $total): array
    {
        $subtotals = [];

        foreach ($invoice->taxTotals as $taxTotal) {
            foreach ($taxTotal->tax_subtotal ?? [] as $sub) {
                $subtotals[] = [
                    'category_id' => (is_string($sub['tax_category'] ?? null) ? $sub['tax_category'] : null)
                        ?? $sub['tax_category_id']
                        ?? 'STANDARD_VAT',
                    'percent' => (float) ($sub['tax_percentage'] ?? 7.5),
                    'taxable_amount' => (float) ($sub['taxable_amount'] ?? 0),
                    'tax_amount' => (float) ($sub['tax_amount'] ?? 0),
                ];
            }
        }

        if ($subtotals) {
            return $subtotals;
        }

        // No stored rows: derive from the monetary total
        $taxable = $total['tax_exclusive_amount'] ?: $total['payable_amount'];

        return [[
            'category_id' => 'STANDARD_VAT',
            'percent' => 7.5,
            'taxable_amount' => $taxable,
            'tax_amount' => $total['payable_amount'] - $taxable,
        ]];
    }

    /**
     * Customer party as saved, falling back to the current customer record for
     * values FIRS rejects (business_description must be at least 5 chars).
     */
    private static function customerParty(Invoice $invoice): array
    {
        return self::withBusinessDescription(
            $invoice->accounting_customer_party ?? [],
            $invoice->customer->business_description ?? null
        );
    }

    private static function withBusinessDescription(array $party, ?string $fallback): array
    {
        if (mb_strlen(trim($party['business_description'] ?? '')) < 5) {
            $party['business_description'] = mb_strlen(trim((string) $fallback)) >= 5
                ? $fallback
                : ($party['party_name'] ?? $fallback);
        }

        return $party;
    }
}
