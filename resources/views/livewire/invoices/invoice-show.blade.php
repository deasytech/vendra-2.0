<section class="w-full">
    <div class="max-w-6xl mx-auto bg-white dark:bg-zinc-900 shadow-lg rounded-xl p-8 space-y-8">

        {{-- Header --}}
        <div class="flex justify-between items-center border-b pb-6">
            <div>
                <h2 class="text-3xl font-bold text-zinc-800 dark:text-zinc-100">INVOICE</h2>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">#{{ $invoice->invoice_reference }}
                </p>
            </div>
            <div class="text-right text-sm text-zinc-600 dark:text-zinc-300">
                <p><span class="font-semibold">Issue Date:</span>
                    {{ \Carbon\Carbon::parse($invoice->issue_date)->format('d M, Y') }}</p>
                <p><span class="font-semibold">Due Date:</span>
                    {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M, Y') }}</p>
                <p><span class="font-semibold">Status:</span>
                    <span
                        class="px-2 py-1 text-xs rounded bg-green-100 text-green-700 dark:bg-green-800 dark:text-green-200">
                        {{ ucfirst($invoice->payment_status) }}
                    </span>
                </p>
            </div>
        </div>

        {{-- Parties --}}
        <div class="grid grid-cols-2 gap-8">
            <div>
                <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200 mb-2">Supplier</h3>
                <p class="font-semibold">{{ $invoice->organization['trade_name'] ?? '-' }}</p>
                <p>TIN: {{ $invoice->organization['tin'] ?? '-' }}</p>
                <p>Email: {{ $invoice->organization['email'] ?? '-' }}</p>
                <p class="text-sm">
                    {{ $invoice->organization['street_name'] ?? '' }},
                    {{ $invoice->organization['city_name'] ?? '' }},
                    {{ $invoice->organization['postal_zone'] ?? '' }},
                    {{ $invoice->organization['country'] ?? '' }}
                </p>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200 mb-2">Customer</h3>
                <p class="font-semibold">{{ $invoice->customer['name'] ?? '-' }}</p>
                <p>TIN: {{ $invoice->customer['tin'] ?? '-' }}</p>
                <p>Email: {{ $invoice->customer['email'] ?? '-' }}</p>
                <p class="text-sm">
                    {{ $invoice->customer['street_name'] ?? '' }},
                    {{ $invoice->customer['city_name'] ?? '' }},
                    {{ $invoice->customer['postal_zone'] ?? '' }},
                    {{ $invoice->customer['country'] ?? '' }}
                </p>
            </div>
        </div>

        {{-- Items --}}
        <div>
            <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200 mb-3">Invoice Items</h3>
            <table class="w-full text-sm border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden">
                <thead class="bg-zinc-100 dark:bg-zinc-800">
                    <tr>
                        <th class="p-3 text-left">Description</th>
                        <th class="p-3 text-left">HSN</th>
                        <th class="p-3 text-left">Category</th>
                        <th class="p-3 text-right">Qty</th>
                        <th class="p-3 text-right">Unit Price</th>
                        <th class="p-3 text-right">Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->lines as $item)
                        <tr class="border-t border-zinc-200 dark:border-zinc-700">
                            <td class="p-3">{{ $item->description }}</td>
                            <td class="p-3">{{ $item->hsn_code ?? '-' }}</td>
                            <td class="p-3">{{ $item->product_category ?? '-' }}</td>
                            <td class="p-3 text-right">{{ $item->quantity }}</td>
                            <td class="p-3 text-right">
                                {{ $invoice->document_currency_code === 'NGN' ? '₦' : ($invoice->document_currency_code === 'USD' ? '$' : ($invoice->document_currency_code === 'EUR' ? '€' : ($invoice->document_currency_code === 'GBP' ? '£' : ($invoice->document_currency_code === 'CAD' ? 'CA$' : ($invoice->document_currency_code === 'GHS' ? 'GH₵' : $invoice->document_currency_code))))) }}{{ $item->price['price_amount'] }}
                            </td>
                            <td class="p-3 text-right">
                                {{ $invoice->document_currency_code === 'NGN' ? '₦' : ($invoice->document_currency_code === 'USD' ? '$' : ($invoice->document_currency_code === 'EUR' ? '€' : ($invoice->document_currency_code === 'GBP' ? '£' : ($invoice->document_currency_code === 'CAD' ? 'CA$' : ($invoice->document_currency_code === 'GHS' ? 'GH₵' : $invoice->document_currency_code))))) }}{{ $item->line_total }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Totals --}}
        <div class="flex justify-between">
            @if ($qrDataUri)
                <div>
                    <h4 class="font-semibold text-sm">FIRS QR Code</h4>
                    <img src="{{ $qrDataUri }}" alt="FIRS QR" class="border rounded p-2">
                </div>
            @endif

            <div class="w-64 space-y-2 text-sm">
                @php
                    $lineExtension = isset($invoice->legal_monetary_total['line_extension_amount'])
                        ? $invoice->legal_monetary_total['line_extension_amount']
                        : $invoice->lines->sum('line_total');
                    $taxExclusive = isset($invoice->legal_monetary_total['tax_exclusive_amount'])
                        ? $invoice->legal_monetary_total['tax_exclusive_amount']
                        : 0;
                    $taxInclusive = isset($invoice->legal_monetary_total['tax_inclusive_amount'])
                        ? $invoice->legal_monetary_total['tax_inclusive_amount']
                        : $invoice->lines->sum('line_total');
                @endphp
                <div class="flex justify-between">
                    <span class="font-semibold">Line Extension:</span>
                    <span>{{ $invoice->document_currency_code === 'NGN' ? '₦' : ($invoice->document_currency_code === 'USD' ? '$' : ($invoice->document_currency_code === 'EUR' ? '€' : ($invoice->document_currency_code === 'GBP' ? '£' : ($invoice->document_currency_code === 'CAD' ? 'CA$' : ($invoice->document_currency_code === 'GHS' ? 'GH₵' : $invoice->document_currency_code))))) }}{{ number_format($lineExtension, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-semibold">Tax Exclusive:</span>
                    <span>{{ $invoice->document_currency_code === 'NGN' ? '₦' : ($invoice->document_currency_code === 'USD' ? '$' : ($invoice->document_currency_code === 'EUR' ? '€' : ($invoice->document_currency_code === 'GBP' ? '£' : ($invoice->document_currency_code === 'CAD' ? 'CA$' : ($invoice->document_currency_code === 'GHS' ? 'GH₵' : $invoice->document_currency_code))))) }}{{ number_format($taxExclusive, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-semibold">Tax Inclusive:</span>
                    <span>{{ $invoice->document_currency_code === 'NGN' ? '₦' : ($invoice->document_currency_code === 'USD' ? '$' : ($invoice->document_currency_code === 'EUR' ? '€' : ($invoice->document_currency_code === 'GBP' ? '£' : ($invoice->document_currency_code === 'CAD' ? 'CA$' : ($invoice->document_currency_code === 'GHS' ? 'GH₵' : $invoice->document_currency_code))))) }}{{ number_format($taxInclusive, 2) }}</span>
                </div>
                <div class="flex justify-between border-t pt-2 text-base font-bold">
                    <span>Grand Total:</span>
                    <span>{{ $invoice->document_currency_code === 'NGN' ? '₦' : ($invoice->document_currency_code === 'USD' ? '$' : ($invoice->document_currency_code === 'EUR' ? '€' : ($invoice->document_currency_code === 'GBP' ? '£' : ($invoice->document_currency_code === 'CAD' ? 'CA$' : ($invoice->document_currency_code === 'GHS' ? 'GH₵' : $invoice->document_currency_code))))) }}{{ number_format($taxInclusive, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Footer Actions --}}
        <div class="flex justify-end space-x-3 border-t pt-4">
            <a href="{{ route('invoices.index') }}"
                class="px-4 py-2 bg-gray-500 text-white rounded shadow hover:bg-gray-600">
                ⬅ Back
            </a>
            {{-- <a href="{{ route('invoices.pdf', $invoice->id) }}"
                class="px-4 py-2 bg-red-600 text-white rounded shadow hover:bg-red-700">
                ⬇ PDF
            </a>
            <a href="{{ route('invoices.email', $invoice->id) }}"
                class="px-4 py-2 bg-green-600 text-white rounded shadow hover:bg-green-700">
                📧 Email
            </a> --}}
        </div>
    </div>
</section>
