<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_reference }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #374151;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
        }

        /* Header Section */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e5e7eb;
        }

        .logo-section {
            flex: 1;
        }

        .company-name {
            font-size: 24pt;
            font-weight: bold;
            color: #111827;
            margin-bottom: 5px;
        }

        .company-tagline {
            font-size: 10pt;
            color: #6b7280;
        }

        .invoice-title-section {
            text-align: right;
        }

        .invoice-title {
            font-size: 32pt;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }

        .invoice-number {
            font-size: 12pt;
            color: #6b7280;
            font-family: 'Courier New', monospace;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 10px;
        }

        .status-paid {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .status-overdue {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .status-default {
            background-color: #f3f4f6;
            color: #374151;
            border: 1px solid #e5e7eb;
        }

        /* Info Grid */
        .info-grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }

        .info-section {
            flex: 1;
        }

        .info-section:first-child {
            margin-right: 40px;
        }

        .info-label {
            font-size: 8pt;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 8px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .info-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
        }

        .info-box h3 {
            font-size: 14pt;
            font-weight: bold;
            color: #111827;
            margin-bottom: 8px;
        }

        .info-item {
            font-size: 10pt;
            color: #4b5563;
            margin-bottom: 4px;
        }

        .info-item strong {
            color: #374151;
        }

        /* Invoice Details */
        .invoice-details {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
        }

        .details-table {
            border-collapse: collapse;
        }

        .details-table td {
            padding: 6px 16px;
            font-size: 10pt;
        }

        .details-table td:first-child {
            text-align: right;
            color: #6b7280;
        }

        .details-table td:last-child {
            font-weight: bold;
            color: #111827;
        }

        /* Items Table */
        .items-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 12pt;
            font-weight: bold;
            color: #111827;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #2563eb;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table thead {
            background-color: #2563eb;
            color: white;
        }

        .items-table th {
            padding: 12px;
            text-align: left;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .items-table th:last-child,
        .items-table td:last-child {
            text-align: right;
        }

        .items-table th:nth-child(4),
        .items-table th:nth-child(5),
        .items-table td:nth-child(4),
        .items-table td:nth-child(5) {
            text-align: right;
        }

        .items-table tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }

        .items-table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .items-table td {
            padding: 12px;
            font-size: 10pt;
            vertical-align: top;
        }

        .item-description {
            font-weight: 600;
            color: #111827;
        }

        .item-detail {
            font-size: 8pt;
            color: #6b7280;
            margin-top: 2px;
        }

        /* Tax Table */
        .tax-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .tax-table th {
            background-color: #f3f4f6;
            padding: 10px 12px;
            text-align: left;
            font-size: 9pt;
            font-weight: bold;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
        }

        .tax-table td {
            padding: 10px 12px;
            font-size: 10pt;
            border-bottom: 1px solid #e5e7eb;
        }

        .tax-table td:last-child {
            text-align: right;
            font-weight: 600;
        }

        /* Summary Section */
        .summary-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
        }

        .summary-box {
            width: 300px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 10pt;
        }

        .summary-row.discount {
            color: #dc2626;
        }

        .summary-divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 12px 0;
        }

        .summary-row.total {
            font-size: 14pt;
            font-weight: bold;
            color: #111827;
            padding-top: 8px;
            border-top: 2px solid #2563eb;
        }

        /* QR Code Section */
        .qr-section {
            display: flex;
            align-items: flex-start;
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }

        .qr-code {
            margin-right: 20px;
        }

        .qr-code img {
            width: 120px;
            height: 120px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 8px;
            background-color: white;
        }

        .qr-info {
            flex: 1;
        }

        .qr-title {
            font-size: 11pt;
            font-weight: bold;
            color: #111827;
            margin-bottom: 8px;
        }

        .qr-description {
            font-size: 9pt;
            color: #6b7280;
            line-height: 1.5;
        }

        .irn-code {
            font-family: 'Courier New', monospace;
            font-size: 8pt;
            color: #374151;
            margin-top: 8px;
            padding: 8px;
            background-color: #e5e7eb;
            border-radius: 4px;
            word-break: break-all;
        }

        /* Notes Section */
        .notes-section {
            margin-bottom: 30px;
        }

        .notes-box {
            background-color: #f9fafb;
            border-left: 4px solid #2563eb;
            padding: 16px;
            border-radius: 0 8px 8px 0;
        }

        .notes-title {
            font-size: 10pt;
            font-weight: bold;
            color: #111827;
            margin-bottom: 8px;
        }

        .notes-text {
            font-size: 9pt;
            color: #4b5563;
            line-height: 1.6;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
        }

        .footer-text {
            font-size: 8pt;
            color: #9ca3af;
        }

        .footer-text strong {
            color: #6b7280;
        }

        /* Utility Classes */
        .text-muted {
            color: #6b7280;
        }

        .text-right {
            text-align: right;
        }

        .font-mono {
            font-family: 'Courier New', monospace;
        }

        .mb-4 {
            margin-bottom: 16px;
        }

        .mt-4 {
            margin-top: 16px;
        }
    </style>
</head>

<body>
    <div class="invoice-container">
        @php
            $settingScope = $settingScope ?? null;
            $projectName = \App\Models\Setting::getValue('project_name', 'Vendra', $settingScope);
            $projectLogo = \App\Models\Setting::getValue('project_logo', null, $settingScope);
        @endphp
        {{-- Header --}}
        <div class="header">
            <div class="logo-section">
                @if ($projectLogo)
                    <img src="{{ Storage::url($projectLogo) }}" alt="{{ $projectName }}"
                        style="max-height: 60px; margin-bottom: 10px;">
                @else
                    <div class="company-name">{{ $invoice->organization->legal_name ?? $projectName }}</div>
                @endif
                <div class="company-tagline">Official Tax Invoice</div>
            </div>
            <div class="invoice-title-section">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-number">#{{ $invoice->invoice_reference }}</div>
                @php
                    $statusColors = match (strtolower($invoice->payment_status)) {
                        'paid' => 'status-paid',
                        'pending' => 'status-pending',
                        'overdue' => 'status-overdue',
                        default => 'status-default',
                    };
                @endphp
                <div class="status-badge {{ $statusColors }}">
                    {{ strtoupper($invoice->payment_status) }}
                </div>
            </div>
        </div>

        {{-- Parties Info --}}
        <div class="info-grid">
            {{-- Supplier Info --}}
            <div class="info-section">
                <div class="info-label">Billed From</div>
                <div class="info-box">
                    <h3>{{ $invoice->organization->legal_name ?? 'Supplier' }}</h3>
                    @if ($invoice->organization->tin)
                        <div class="info-item"><strong>TIN:</strong> {{ $invoice->organization->tin }}</div>
                    @endif
                    @if ($invoice->organization->email)
                        <div class="info-item"><strong>Email:</strong> {{ $invoice->organization->email }}</div>
                    @endif
                    @if ($invoice->organization->phone)
                        <div class="info-item"><strong>Phone:</strong> {{ $invoice->organization->phone }}</div>
                    @endif
                    @php
                        $orgAddress = $invoice->organization->postal_address ?? [];
                    @endphp
                    @if (!empty($orgAddress['street_name']) || !empty($orgAddress['city_name']))
                        <div class="info-item" style="margin-top: 8px;">
                            @if ($orgAddress['street_name'] ?? '')
                                {{ $orgAddress['street_name'] }}<br>
                            @endif
                            @if ($orgAddress['city_name'] ?? '')
                                {{ $orgAddress['city_name'] }}
                            @endif
                            @if ($orgAddress['postal_zone'] ?? '')
                                {{ $orgAddress['postal_zone'] }}<br>
                            @endif
                            @if ($orgAddress['country'] ?? '')
                                {{ $orgAddress['country'] }}
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Customer Info --}}
            <div class="info-section">
                <div class="info-label">Billed To</div>
                <div class="info-box">
                    <h3>{{ $invoice->customer->name ?? 'Customer' }}</h3>
                    @if ($invoice->customer->tin)
                        <div class="info-item"><strong>TIN:</strong> {{ $invoice->customer->tin }}</div>
                    @endif
                    @if ($invoice->customer->email)
                        <div class="info-item"><strong>Email:</strong> {{ $invoice->customer->email }}</div>
                    @endif
                    @if ($invoice->customer->phone)
                        <div class="info-item"><strong>Phone:</strong> {{ $invoice->customer->phone }}</div>
                    @endif
                    @if ($invoice->customer->street_name || $invoice->customer->city_name)
                        <div class="info-item" style="margin-top: 8px;">
                            @if ($invoice->customer->street_name)
                                {{ $invoice->customer->street_name }}<br>
                            @endif
                            @if ($invoice->customer->city_name)
                                {{ $invoice->customer->city_name }}
                            @endif
                            @if ($invoice->customer->postal_zone)
                                {{ $invoice->customer->postal_zone }}<br>
                            @endif
                            @if ($invoice->customer->state)
                                {{ $invoice->customer->state }}
                            @endif
                            @if ($invoice->customer->country)
                                {{ $invoice->customer->country }}
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Invoice Details --}}
        <div class="invoice-details">
            <table class="details-table">
                <tr>
                    <td>Issue Date:</td>
                    <td>{{ \Carbon\Carbon::parse($invoice->issue_date)->format('F d, Y') }}</td>
                </tr>
                <tr>
                    <td>Due Date:</td>
                    <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('F d, Y') }}</td>
                </tr>
                <tr>
                    <td>Currency:</td>
                    <td>{{ $invoice->document_currency_code }}</td>
                </tr>
                <tr>
                    <td>Invoice Type:</td>
                    <td>{{ $invoice->invoice_type_code ?? 'Standard' }}</td>
                </tr>
            </table>
        </div>

        {{-- Items Section --}}
        <div class="items-section">
            <div class="section-title">Invoice Items</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>HSN</th>
                        <th>Category</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->lines as $item)
                        @php
                            $itemQty = (float) ($item->invoiced_quantity ?? 0);
                            $itemPrice = (float) ($item->price['price_amount'] ?? 0);

                            // Use pre-calculated line_extension_amount if available (this is qty * price)
                            if (!empty($item->line_extension_amount) && $item->line_extension_amount > 0) {
                                $itemLineTotal = (float) $item->line_extension_amount;
                            } else {
                                $itemLineTotal = $itemQty * $itemPrice;
                            }

                            $currencySymbol = match ($invoice->document_currency_code) {
                                'NGN' => '₦',
                                'USD' => '$',
                                'EUR' => '€',
                                'GBP' => '£',
                                'CAD' => 'CA$',
                                'GHS' => 'GH₵',
                                default => $invoice->document_currency_code . ' ',
                            };
                        @endphp
                        <tr>
                            <td>
                                <div class="item-description">{{ $item->description }}</div>
                                @if ($item->item['description'] ?? false)
                                    <div class="item-detail">{{ $item->item['description'] }}</div>
                                @endif
                            </td>
                            <td>{{ $item->hsn_code ?? '-' }}</td>
                            <td>{{ $item->product_category ?? '-' }}</td>
                            <td>{{ number_format($itemQty, 2) }}</td>
                            <td>
                                {{ $currencySymbol }}{{ number_format($itemPrice, 2) }}
                            </td>
                            <td>{{ $currencySymbol }}{{ number_format($itemLineTotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Tax Section --}}
        @if ($invoice->taxTotals->count() > 0)
            <div class="items-section">
                <div class="section-title">Tax Breakdown</div>
                <table class="tax-table">
                    <thead>
                        <tr>
                            <th>Tax Type</th>
                            <th style="text-align: right;">Rate</th>
                            <th style="text-align: right;">Taxable Base</th>
                            <th style="text-align: right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoice->taxTotals as $tax)
                            @php
                                $taxSubtotals = $tax->tax_subtotal ?? [];
                                $firstSubtotal =
                                    is_array($taxSubtotals) && count($taxSubtotals) > 0 ? $taxSubtotals[0] : [];
                                $taxCategory = $firstSubtotal['tax_category'] ?? 'Tax';
                                $taxRate = $firstSubtotal['tax_percentage'] ?? 0;
                                $taxableAmount = $firstSubtotal['taxable_amount'] ?? 0;
                            @endphp
                            <tr>
                                <td>{{ $taxCategory }}</td>
                                <td style="text-align: right;">{{ $taxRate }}%</td>
                                <td style="text-align: right;">
                                    {{ $currencySymbol }}{{ number_format($taxableAmount, 2) }}</td>
                                <td style="text-align: right;">
                                    {{ $currencySymbol }}{{ number_format($tax->tax_amount ?? 0, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Summary Section --}}
        @php
            $metadata = is_array($invoice->metadata) ? $invoice->metadata : [];
            $allowanceCharges = is_array($metadata['allowance_charges'] ?? null) ? $metadata['allowance_charges'] : [];
            $withholdingEnabled = (bool) ($metadata['withholding_tax_enabled'] ?? false);
            $withholdingRate = (float) ($metadata['withholding_tax_rate'] ?? 0);
            $withholdingAmount = (float) ($metadata['withholding_tax_amount'] ?? 0);

            // Calculate line extension using same logic as line items
            $lineExtension = $invoice->lines->sum(function ($line) {
                if (!empty($line->line_extension_amount) && $line->line_extension_amount > 0) {
                    return (float) $line->line_extension_amount;
                }
                $qty = (float) ($line->invoiced_quantity ?? 0);
                $price = (float) ($line->price['price_amount'] ?? 0);
                return $qty * $price;
            });

            $taxAmount = $invoice->taxTotals->sum('tax_amount');
            $taxInclusive = $lineExtension + $taxAmount;
        @endphp

        <div class="summary-section">
            <div class="summary-box">
                <div class="summary-row">
                    <span class="text-muted">Subtotal:</span>
                    <span>{{ $currencySymbol }}{{ number_format($lineExtension, 2) }}</span>
                </div>
                {{-- <div class="summary-row">
                    <span class="text-muted">Tax Exclusive:</span>
                    <span>{{ $currencySymbol }}{{ number_format($lineExtension, 2) }}</span>
                </div> --}}
                <div class="summary-row">
                    <span class="text-muted">Tax Amount:</span>
                    <span>{{ $currencySymbol }}{{ number_format($taxAmount, 2) }}</span>
                </div>

                @if (count($allowanceCharges) > 0)
                    @foreach ($allowanceCharges as $charge)
                        @php
                            $chargeAmount = (float) ($charge['amount'] ?? 0);
                            if (($charge['amount_type'] ?? 'fixed') === 'percent') {
                                $chargeAmount = round($lineExtension * ($chargeAmount / 100), 2);
                            }
                        @endphp
                        <div class="summary-row {{ !($charge['charge_indicator'] ?? false) ? 'discount' : '' }}">
                            <span class="text-muted">
                                {{ $charge['reason'] ?? ($charge['charge_indicator'] ? 'Charge' : 'Discount') }}
                                @if (($charge['amount_type'] ?? 'fixed') === 'percent')
                                    ({{ $charge['amount'] ?? 0 }}%)
                                @endif
                            </span>
                            <span>
                                @if (!($charge['charge_indicator'] ?? false))
                                    -
                                @endif
                                {{ $currencySymbol }}{{ number_format($chargeAmount, 2) }}
                            </span>
                        </div>
                    @endforeach
                @endif

                @if ($withholdingEnabled)
                    <div class="summary-row discount">
                        <span class="text-muted">Withholding Tax ({{ $withholdingRate }}%):</span>
                        <span>-{{ $currencySymbol }}{{ number_format($withholdingAmount, 2) }}</span>
                    </div>
                @endif

                <div class="summary-divider"></div>

                <div class="summary-row total">
                    <span>Grand Total:</span>
                    <span>{{ $currencySymbol }}{{ number_format($taxInclusive, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- QR Code Section --}}
        @if (isset($qrDataUri) && $qrDataUri)
            <div class="qr-section">
                <div class="qr-code">
                    <img src="{{ $qrDataUri }}" alt="FIRS QR Code">
                </div>
                <div class="qr-info">
                    <div class="qr-title">NRS QR Code</div>
                    <div class="qr-description">Scan this QR code to verify invoice authenticity</div>
                    @if ($invoice->irn)
                        <div class="irn-code">IRN: {{ $invoice->irn }}</div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Notes Section --}}
        @if ($invoice->note || $invoice->payment_terms_note)
            <div class="notes-section">
                <div class="notes-box">
                    @if ($invoice->note)
                        <div class="notes-title">Notes</div>
                        <div class="notes-text">
                            {{ is_array($invoice->note) ? implode(', ', $invoice->note) : $invoice->note }}</div>
                    @endif
                    @if ($invoice->payment_terms_note)
                        <div class="notes-title" style="margin-top: 12px;">Payment Terms</div>
                        <div class="notes-text">
                            {{ is_array($invoice->payment_terms_note) ? implode(', ', $invoice->payment_terms_note) : $invoice->payment_terms_note }}
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Footer --}}
        <div class="footer">
            <div class="footer-text">Generated by {{ $projectName }} on
                {{ now()->format('F d, Y') }}</div>
        </div>
    </div>
</body>

</html>
