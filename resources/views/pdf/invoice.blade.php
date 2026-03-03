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
        $projectName = \App\Models\Setting::getValue('project_name', 'Vendra Invoice System');
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

        $lineSubtotal = $invoice->lines->sum(function ($line) {
            $qty = (float) ($line->invoiced_quantity ?? ($line->quantity ?? 0));
            $price = (float) ($line->price['price_amount'] ?? 0);
            return isset($line->line_extension_amount) ? (float) $line->line_extension_amount : $qty * $price;
        });

        $taxAmount = (float) $invoice->taxTotals->sum('tax_amount');
        $taxExclusive = (float) ($invoice->legal_monetary_total['tax_exclusive_amount'] ?? $lineSubtotal);
        $grandTotal = (float) ($invoice->legal_monetary_total['tax_inclusive_amount'] ?? $taxExclusive + $taxAmount);
    @endphp

    <table class="header-table">
        <tr>
            <td>
                <h1 class="title">INVOICE</h1>
                <div class="ref">Reference: <strong>#{{ $invoice->invoice_reference }}</strong></div>
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
                                <strong>{{ optional($invoice->issue_date)->format('d M, Y') }}</strong></td>
                        </tr>
                        <tr>
                            <td class="muted">Due Date:</td>
                            <td class="text-right"><strong>{{ optional($invoice->due_date)->format('d M, Y') }}</strong>
                            </td>
                        </tr>
                        <tr>
                            <td class="muted">Currency:</td>
                            <td class="text-right"><strong>{{ $currency }}</strong></td>
                        </tr>
                        <tr>
                            <td class="muted">Status:</td>
                            <td class="text-right">
                                <strong>{{ strtoupper($invoice->payment_status ?? 'PENDING') }}</strong></td>
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
                    $qty = (float) ($line->invoiced_quantity ?? ($line->quantity ?? 0));
                    $price = (float) ($line->price['price_amount'] ?? 0);
                    $lineTotal = isset($line->line_extension_amount)
                        ? (float) $line->line_extension_amount
                        : $qty * $price;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $line->description ?? ($line->item['name'] ?? '-') }}</td>
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

    <div class="spacer-16"></div>

    <div class="totals-wrap">
        <table class="totals-table">
            <tr>
                <td class="muted">Subtotal</td>
                <td class="text-right">{{ $currencySymbol }}{{ number_format($lineSubtotal, 2) }}</td>
            </tr>
            <tr>
                <td class="muted">Tax</td>
                <td class="text-right">{{ $currencySymbol }}{{ number_format($taxAmount, 2) }}</td>
            </tr>
            <tr class="grand-total">
                <td>Grand Total</td>
                <td class="text-right">{{ $currencySymbol }}{{ number_format($grandTotal, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Generated by {{ $projectName }} on {{ now()->format('d M, Y H:i') }}
    </div>
</body>

</html>
