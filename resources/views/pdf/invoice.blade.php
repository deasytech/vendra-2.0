<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_reference }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2937;
            margin: 0;
            padding: 28px;
            line-height: 1.45;
            background: #fff;
        }

        .text-right {
            text-align: right;
        }

        .muted {
            color: #6b7280;
        }

        .title {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 1px;
            margin: 0;
            color: #111827;
        }

        .ref {
            font-size: 12px;
            margin-top: 6px;
            color: #4b5563;
        }

        .card {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px;
        }

        .header-table,
        .meta-table,
        .party-table,
        .items-table,
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td,
        .party-table td {
            vertical-align: top;
            padding: 0;
        }

        .spacer-16 {
            height: 16px;
        }

        .spacer-20 {
            height: 20px;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #e5e7eb;
            padding: 8px;
        }

        .items-table th {
            background: #f3f4f6;
            font-weight: 700;
            color: #111827;
            text-transform: uppercase;
            font-size: 11px;
        }

        .totals-wrap {
            margin-left: auto;
            width: 320px;
        }

        .totals-table td {
            padding: 6px 0;
        }

        .totals-table .grand-total td {
            font-size: 14px;
            font-weight: 700;
            border-top: 1px solid #d1d5db;
            padding-top: 10px;
        }

        .footer {
            margin-top: 28px;
            border-top: 1px solid #e5e7eb;
            padding-top: 12px;
            font-size: 11px;
            color: #6b7280;
        }
    </style>
</head>

<body>
    @php
        $settingScope = $settingScope ?? null;
        $projectName = \App\Models\Setting::getValue('project_name', 'Vendra Invoice System', $settingScope);
        $projectLogo = \App\Models\Setting::getValue('project_logo', null, $settingScope);
        $currency = $invoice->document_currency_code ?: 'NGN';
        $currencySymbol = match ($currency) {
            'NGN' => '₦',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'CAD' => 'CA$',
            'GHS' => 'GH₵',
            default => $currency . ' ',
        };

        // Calculate line totals with same logic as invoice show page
        $lineSubtotal = $invoice->lines->sum(function ($line) {
            // Use pre-calculated line_extension_amount if available (this is qty * price)
            if (!empty($line->line_extension_amount) && $line->line_extension_amount > 0) {
                return (float) $line->line_extension_amount;
            }

            // Otherwise calculate from price array
            $qty = (float) ($line->invoiced_quantity ?? 0);
            $price = (float) ($line->price['price_amount'] ?? 0);
            return $qty * $price;
        });

        // Metadata for withholding tax and allowances
        $metadata = is_array($invoice->metadata) ? $invoice->metadata : [];
        $allowanceCharges = is_array($metadata['allowance_charges'] ?? null) ? $metadata['allowance_charges'] : [];
        $withholdingEnabled = (bool) ($metadata['withholding_tax_enabled'] ?? false);
        $withholdingRate = (float) ($metadata['withholding_tax_rate'] ?? 0);
        $withholdingAmount = (float) ($metadata['withholding_tax_amount'] ?? 0);

        // Calculate allowances/charges total
        $allowanceTotal = 0;
        foreach ($allowanceCharges as $charge) {
            $chargeAmount = (float) ($charge['amount'] ?? 0);
            if (($charge['amount_type'] ?? 'fixed') === 'percent') {
                $chargeAmount = round($lineSubtotal * ($chargeAmount / 100), 2);
            }
            // charge_indicator = true means charge (add), false means discount (subtract)
            if (!empty($charge['charge_indicator'])) {
                $allowanceTotal += $chargeAmount;
            } else {
                $allowanceTotal -= $chargeAmount;
            }
        }

        // Taxable amount = subtotal + allowances/charges
        $taxableAmount = $lineSubtotal + $allowanceTotal;
        if ($taxableAmount < 0) {
            $taxableAmount = 0;
        }

        // Tax amount
        $taxAmount = (float) $invoice->taxTotals->sum('tax_amount');

        // Grand Total = taxable amount + tax - withholding tax
        $grandTotal = $taxableAmount + $taxAmount - $withholdingAmount;
    @endphp

    <table class="header-table">
        <tr>
            <td>
                @if ($projectLogo)
                    <div style="margin-bottom: 12px;">
                        <img src="{{ Storage::url($projectLogo) }}" alt="{{ $projectName }}"
                            style="max-height: 60px; max-width: 180px; object-fit: contain;">
                    </div>
                @endif
                <h1 class="title">INVOICE</h1>
                <div class="ref">Reference: <strong>#{{ $invoice->invoice_reference }}</strong></div>
                @if ($irn)
                    <div class="ref" style="margin-top: 4px; font-family: 'Courier New', monospace; font-size: 10px;">
                        <span style="color: #6b7280;">IRN:</span> <strong>{{ $irn }}</strong>
                    </div>
                @endif
            </td>
            <td class="text-right">
                <div><strong>{{ $invoice->organization->legal_name ?? $projectName }}</strong></div>
                <div class="muted">TIN: {{ $invoice->organization->tin ?? '-' }}</div>
                <div class="muted">{{ $invoice->organization->email ?? '-' }}</div>
            </td>
        </tr>
    </table>

    <div class="spacer-16"></div>

    <table class="meta-table">
        <tr>
            <td style="width: 58%; padding-right: 8px;">
                <div class="card">
                    <div style="font-weight: 700; margin-bottom: 6px;">Bill To</div>
                    <div><strong>{{ $invoice->customer->name ?? '-' }}</strong></div>
                    <div class="muted">TIN: {{ $invoice->customer->tin ?? '-' }}</div>
                    <div class="muted">{{ $invoice->customer->email ?? '-' }}</div>
                </div>
            </td>
            <td style="width: 42%; padding-left: 8px;">
                <div class="card">
                    <table style="width:100%; border-collapse: collapse;">
                        <tr>
                            <td class="muted">Issue Date:</td>
                            <td class="text-right">
                                <strong>{{ optional($invoice->issue_date)->format('d M, Y') }}</strong>
                            </td>
                        </tr>
                        <tr>
                            <td class="muted">Due Date:</td>
                            <td class="text-right">
                                <strong>{{ optional($invoice->due_date)->format('d M, Y') }}</strong>
                            </td>
                        </tr>
                        <tr>
                            <td class="muted">Currency:</td>
                            <td class="text-right"><strong>{{ $currency }}</strong></td>
                        </tr>
                        <tr>
                            <td class="muted">Status:</td>
                            <td class="text-right">
                                <strong>{{ strtoupper($invoice->payment_status ?? 'PENDING') }}</strong>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <div class="spacer-20"></div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th style="width:43%;">Description</th>
                <th style="width:12%;">HSN</th>
                <th style="width:10%;" class="text-right">Qty</th>
                <th style="width:15%;" class="text-right">Unit Price</th>
                <th style="width:15%;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($invoice->lines as $index => $line)
                @php
                    $qty = (float) ($line->invoiced_quantity ?? 0);
                    $price = (float) ($line->price['price_amount'] ?? 0);

                    // Use pre-calculated line_extension_amount if available (this is qty * price)
                    if (!empty($line->line_extension_amount) && $line->line_extension_amount > 0) {
                        $lineTotal = (float) $line->line_extension_amount;
                    } else {
                        $lineTotal = $qty * $price;
                    }
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $line->description ?? ($line->item['name'] ?? ($line->item['description'] ?? '-')) }}</td>
                    <td>{{ $line->hsn_code ?? '-' }}</td>
                    <td class="text-right">{{ number_format($qty, 2) }}</td>
                    <td class="text-right">{{ $currencySymbol }}{{ number_format($price, 2) }}</td>
                    <td class="text-right">{{ $currencySymbol }}{{ number_format($lineTotal, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-right muted" style="text-align:center;">No items found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Tax Breakdown Section --}}
    @if ($invoice->taxTotals->count() > 0)
        <div class="spacer-16"></div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 35%;">Tax Type</th>
                    <th style="width: 25%;" class="text-right">Rate</th>
                    <th style="width: 20%;" class="text-right">Taxable Base</th>
                    <th style="width: 20%;" class="text-right">Tax Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->taxTotals as $tax)
                    @php
                        $taxSubtotals = $tax->tax_subtotal ?? [];
                        $firstSubtotal = is_array($taxSubtotals) && count($taxSubtotals) > 0 ? $taxSubtotals[0] : [];
                        $taxCategory = $firstSubtotal['tax_category'] ?? 'Tax';
                        $taxRate = $firstSubtotal['tax_percentage'] ?? 0;
                        $taxableAmount = $firstSubtotal['taxable_amount'] ?? 0;
                    @endphp
                    <tr>
                        <td>{{ $taxCategory }}</td>
                        <td class="text-right">{{ $taxRate }}%</td>
                        <td class="text-right">{{ $currencySymbol }}{{ number_format($taxableAmount, 2) }}</td>
                        <td class="text-right">{{ $currencySymbol }}{{ number_format($tax->tax_amount ?? 0, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="spacer-16"></div>

    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            {{-- QR Code on the left --}}
            @if ($qrDataUri)
                <td style="width: 50%; vertical-align: top; padding-right: 20px;">
                    <div style="font-weight: 700; color: #374151; margin-bottom: 4px;">NRS QR Code</div>
                    <img src="{{ $qrDataUri }}" alt="NRS QR Code"
                        style="max-width: 120px; height: auto; border: 1px solid #e5e7eb; padding: 4px; background: white;">
                    <div style="font-size: 9px; color: #6b7280; margin-top: 4px;">Scan to verify authenticity</div>
                </td>
            @else
                <td style="width: 50%;"></td>
            @endif

            {{-- Totals on the right --}}
            <td style="width: 50%; vertical-align: top;">
                <div class="totals-wrap" style="margin-left: auto;">
                    <table class="totals-table">
                        <tr>
                            <td class="muted">Subtotal</td>
                            <td class="text-right">{{ $currencySymbol }}{{ number_format($lineSubtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="muted">Tax Amount</td>
                            <td class="text-right">{{ $currencySymbol }}{{ number_format($taxAmount, 2) }}</td>
                        </tr>

                        @if (count($allowanceCharges) > 0)
                            @foreach ($allowanceCharges as $charge)
                                @php
                                    $chargeAmount = (float) ($charge['amount'] ?? 0);
                                    if (($charge['amount_type'] ?? 'fixed') === 'percent') {
                                        $chargeAmount = round($lineSubtotal * ($chargeAmount / 100), 2);
                                    }
                                @endphp
                                <tr>
                                    <td class="muted">
                                        {{ $charge['reason'] ?? ($charge['charge_indicator'] ? 'Charge' : 'Discount') }}
                                        @if (($charge['amount_type'] ?? 'fixed') === 'percent')
                                            ({{ $charge['amount'] ?? 0 }}%)
                                        @endif
                                    </td>
                                    <td class="text-right"
                                        style="color: {{ !($charge['charge_indicator'] ?? false) ? '#dc2626' : 'inherit' }}">
                                        @if (!($charge['charge_indicator'] ?? false))
                                            -
                                        @endif
                                        {{ $currencySymbol }}{{ number_format($chargeAmount, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        @endif

                        @if ($withholdingEnabled)
                            <tr>
                                <td class="muted">Withholding Tax ({{ $withholdingRate }}%)</td>
                                <td class="text-right" style="color: #dc2626;">
                                    -{{ $currencySymbol }}{{ number_format($withholdingAmount, 2) }}</td>
                            </tr>
                        @endif

                        <tr class="grand-total">
                            <td>Grand Total</td>
                            <td class="text-right">{{ $currencySymbol }}{{ number_format($grandTotal, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Generated by {{ $projectName }} on {{ now()->format('d M, Y H:i') }}
    </div>
</body>

</html>
