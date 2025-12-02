<section class="w-full">
    {{-- Flash Messages --}}
    @if (session('success'))
        <div
            class="mb-6 rounded-xl bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/30 dark:to-emerald-900/30 border border-green-200/50 dark:border-green-700/50 p-4 shadow-sm backdrop-blur-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-500 dark:text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-semibold text-green-800 dark:text-green-200">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div
            class="mb-6 rounded-xl bg-gradient-to-r from-red-50 to-rose-50 dark:from-red-900/30 dark:to-rose-900/30 border border-red-200/50 dark:border-red-700/50 p-4 shadow-sm backdrop-blur-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500 dark:text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-semibold text-red-800 dark:text-red-200">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div
        class="max-w-6xl mx-auto bg-white dark:bg-zinc-800 shadow-xl rounded-2xl p-8 space-y-8 border border-zinc-100 dark:border-zinc-700/50 backdrop-blur-sm">

        {{-- Header --}}
        <div class="flex justify-between items-center border-b border-zinc-200 dark:border-zinc-700 pb-8">
            <div class="space-y-2">
                <div class="flex items-center space-x-3">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h1
                            class="text-4xl font-bold bg-gradient-to-r from-zinc-800 to-zinc-600 dark:from-zinc-100 dark:to-zinc-300 bg-clip-text text-transparent">
                            INVOICE
                        </h1>
                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Invoice Reference</p>
                    </div>
                </div>
                <div
                    class="bg-gradient-to-r from-zinc-100 to-zinc-50 dark:from-zinc-700/50 dark:to-zinc-800/50 rounded-lg px-4 py-2 inline-block">
                    <p class="text-lg font-mono font-bold text-zinc-700 dark:text-zinc-300 tracking-wider">
                        #{{ $invoice->invoice_reference }}
                    </p>
                </div>
            </div>

            <div class="space-y-4">
                {{-- Status Badge --}}
                <div class="flex justify-end">
                    @php
                        $statusColors = match (strtolower($invoice->payment_status)) {
                            'paid'
                                => 'bg-gradient-to-r from-green-100 to-emerald-100 dark:from-green-900/40 dark:to-emerald-900/40 text-green-700 dark:text-green-300 border-green-200 dark:border-green-700',
                            'pending'
                                => 'bg-gradient-to-r from-amber-100 to-yellow-100 dark:from-amber-900/40 dark:to-yellow-900/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-700',
                            'overdue'
                                => 'bg-gradient-to-r from-red-100 to-rose-100 dark:from-red-900/40 dark:to-rose-900/40 text-red-700 dark:text-red-300 border-red-200 dark:border-red-700',
                            default
                                => 'bg-gradient-to-r from-zinc-100 to-gray-100 dark:from-zinc-900/40 dark:to-gray-900/40 text-zinc-700 dark:text-zinc-300 border-zinc-200 dark:border-zinc-700',
                        };
                    @endphp
                    <span
                        class="px-4 py-2 rounded-full text-sm font-semibold border {{ $statusColors }} backdrop-blur-sm shadow-sm">
                        <span class="flex items-center">
                            <span
                                class="w-2 h-2 rounded-full mr-2 
                                {{ strtolower($invoice->payment_status) === 'paid'
                                    ? 'bg-green-500'
                                    : (strtolower($invoice->payment_status) === 'pending'
                                        ? 'bg-amber-500'
                                        : (strtolower($invoice->payment_status) === 'overdue'
                                            ? 'bg-red-500'
                                            : 'bg-zinc-500')) }}">
                            </span>
                            {{ strtoupper($invoice->payment_status) }}
                        </span>
                    </span>
                </div>

                {{-- Invoice Details --}}
                <div
                    class="bg-gradient-to-br from-zinc-50 to-white dark:from-zinc-800/50 dark:to-zinc-900/50 rounded-xl p-6 border border-zinc-200 dark:border-zinc-700/50 shadow-sm backdrop-blur-sm">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-zinc-600 dark:text-zinc-400">Issue Date</span>
                                <span class="font-semibold text-zinc-800 dark:text-zinc-200">
                                    {{ \Carbon\Carbon::parse($invoice->issue_date)->format('d M, Y') }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-zinc-600 dark:text-zinc-400">Due Date</span>
                                <span class="font-semibold text-zinc-800 dark:text-zinc-200">
                                    {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M, Y') }}
                                </span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-zinc-600 dark:text-zinc-400">Currency</span>
                                <span class="font-semibold text-zinc-800 dark:text-zinc-200">
                                    {{ $invoice->document_currency_code }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-zinc-600 dark:text-zinc-400">Type</span>
                                <span class="font-semibold text-zinc-800 dark:text-zinc-200">
                                    {{ $invoice->invoice_type_code ?? 'Standard' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Parties --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Supplier Card --}}
            <div
                class="bg-gradient-to-br from-zinc-50 to-white dark:from-zinc-800/60 dark:to-zinc-900/40 rounded-2xl p-8 border border-zinc-200 dark:border-zinc-700/50 shadow-lg backdrop-blur-sm">
                <div class="flex items-center space-x-4 mb-6">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-zinc-800 dark:text-zinc-100">Supplier</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Billing From</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div
                        class="bg-white dark:bg-zinc-800/50 rounded-xl p-4 border border-zinc-100 dark:border-zinc-700/30">
                        <h4 class="font-bold text-lg text-zinc-800 dark:text-zinc-100 mb-2">
                            {{ $invoice->organization->legal_name ?? '-' }}
                        </h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-zinc-400 dark:text-zinc-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                </svg>
                                <span class="text-zinc-600 dark:text-zinc-400">TIN:</span>
                                <span
                                    class="font-mono font-semibold text-zinc-700 dark:text-zinc-300">{{ $invoice->organization->tin ?? '-' }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-zinc-400 dark:text-zinc-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <span class="text-zinc-600 dark:text-zinc-400">Email:</span>
                                <span
                                    class="text-zinc-700 dark:text-zinc-300">{{ $invoice->organization->email ?? '-' }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-zinc-400 dark:text-zinc-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <span class="text-zinc-600 dark:text-zinc-400">Phone:</span>
                                <span
                                    class="text-zinc-700 dark:text-zinc-300">{{ $invoice->organization->phone ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    @php
                        $orgAddress = $invoice->organization->postal_address ?? [];
                    @endphp
                    @if (!empty($orgAddress['street_name']) || !empty($orgAddress['city_name']))
                        <div
                            class="bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 rounded-xl p-4 border border-emerald-200/50 dark:border-emerald-700/50">
                            <div class="flex items-start space-x-3">
                                <svg class="w-5 h-5 text-emerald-500 dark:text-emerald-400 mt-0.5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <div class="text-sm">
                                    <p class="font-medium text-emerald-800 dark:text-emerald-200 mb-1">Business Address
                                    </p>
                                    <p class="text-emerald-700 dark:text-emerald-300">
                                        {{ $orgAddress['street_name'] ?? '' }}<br>
                                        {{ $orgAddress['city_name'] ?? '' }}
                                        {{ $orgAddress['postal_zone'] ?? '' }}<br>
                                        {{ $orgAddress['country'] ?? '' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Customer Card --}}
            <div
                class="bg-gradient-to-br from-zinc-50 to-white dark:from-zinc-800/60 dark:to-zinc-900/40 rounded-2xl p-8 border border-zinc-200 dark:border-zinc-700/50 shadow-lg backdrop-blur-sm">
                <div class="flex items-center space-x-4 mb-6">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-zinc-800 dark:text-zinc-100">Customer</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Billing To</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div
                        class="bg-white dark:bg-zinc-800/50 rounded-xl p-4 border border-zinc-100 dark:border-zinc-700/30">
                        <h4 class="font-bold text-lg text-zinc-800 dark:text-zinc-100 mb-2">
                            {{ $invoice->customer->name ?? '-' }}
                        </h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-zinc-400 dark:text-zinc-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                </svg>
                                <span class="text-zinc-600 dark:text-zinc-400">TIN:</span>
                                <span
                                    class="font-mono font-semibold text-zinc-700 dark:text-zinc-300">{{ $invoice->customer->tin ?? '-' }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-zinc-400 dark:text-zinc-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <span class="text-zinc-600 dark:text-zinc-400">Email:</span>
                                <span
                                    class="text-zinc-700 dark:text-zinc-300">{{ $invoice->customer->email ?? '-' }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-zinc-400 dark:text-zinc-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <span class="text-zinc-600 dark:text-zinc-400">Phone:</span>
                                <span
                                    class="text-zinc-700 dark:text-zinc-300">{{ $invoice->customer->phone ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    @if (!empty($invoice->customer->street_name) || !empty($invoice->customer->city_name))
                        <div
                            class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-4 border border-blue-200/50 dark:border-blue-700/50">
                            <div class="flex items-start space-x-3">
                                <svg class="w-5 h-5 text-blue-500 dark:text-blue-400 mt-0.5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <div class="text-sm">
                                    <p class="font-medium text-blue-800 dark:text-blue-200 mb-1">Customer Address</p>
                                    <p class="text-blue-700 dark:text-blue-300">
                                        {{ $invoice->customer->street_name ?? '' }}<br>
                                        {{ $invoice->customer->city_name ?? '' }}
                                        {{ $invoice->customer->postal_zone ?? '' }}<br>
                                        {{ $invoice->customer->state ?? '' }} {{ $invoice->customer->country ?? '' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Items --}}
        <div>
            <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200 mb-3 flex items-center">
                <svg class="w-5 h-5 mr-2 text-zinc-600 dark:text-zinc-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Invoice Items
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden">
                    <thead class="bg-zinc-100 dark:bg-zinc-800">
                        <tr>
                            <th class="p-3 text-left font-semibold">Description</th>
                            <th class="p-3 text-left font-semibold">HSN</th>
                            <th class="p-3 text-left font-semibold">Category</th>
                            <th class="p-3 text-right font-semibold">Qty</th>
                            <th class="p-3 text-right font-semibold">Unit Price</th>
                            <th class="p-3 text-right font-semibold">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoice->lines as $item)
                            <tr
                                class="border-t border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition-colors">
                                <td class="p-3">
                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $item->description }}
                                    </div>
                                    @if ($item->item['description'] ?? false)
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $item->item['description'] }}</div>
                                    @endif
                                </td>
                                <td class="p-3 text-zinc-600 dark:text-zinc-400">{{ $item->hsn_code ?? '-' }}</td>
                                <td class="p-3 text-zinc-600 dark:text-zinc-400">{{ $item->product_category ?? '-' }}
                                </td>
                                <td class="p-3 text-right text-zinc-900 dark:text-zinc-100">
                                    {{ number_format($item->invoiced_quantity ?? $item->quantity, 2) }}</td>
                                <td class="p-3 text-right text-zinc-900 dark:text-zinc-100">
                                    @php
                                        $currency = $invoice->document_currency_code;
                                        $currencySymbol = match ($currency) {
                                            'NGN' => '₦',
                                            'USD' => '$',
                                            'EUR' => '€',
                                            'GBP' => '£',
                                            'CAD' => 'CA$',
                                            'GHS' => 'GH₵',
                                            default => $currency . ' ',
                                        };
                                    @endphp
                                    {{ $currencySymbol }}{{ number_format($item->price['price_amount'] ?? 0, 2) }}
                                </td>
                                <td class="p-3 text-right font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $currencySymbol }}{{ number_format($item->line_total ?? 0, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-zinc-500 dark:text-zinc-400">
                                    <svg class="w-12 h-12 mx-auto mb-4 text-zinc-300 dark:text-zinc-600"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    No invoice items found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tax Totals --}}
        @if ($invoice->taxTotals->count() > 0)
            <div>
                <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200 mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-zinc-600 dark:text-zinc-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Tax Breakdown
                </h3>
                <div class="overflow-x-auto">
                    <table
                        class="w-full text-sm border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden">
                        <thead class="bg-zinc-100 dark:bg-zinc-800">
                            <tr>
                                <th class="p-3 text-left font-semibold">Tax Type</th>
                                <th class="p-3 text-right font-semibold">Tax Amount</th>
                                <th class="p-3 text-left font-semibold">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoice->taxTotals as $tax)
                                <tr
                                    class="border-t border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition-colors">
                                    <td class="p-3 text-zinc-900 dark:text-zinc-100">
                                        @php
                                            $taxSubtotals = $tax->tax_subtotal ?? [];
                                            $firstSubtotal =
                                                is_array($taxSubtotals) && count($taxSubtotals) > 0
                                                    ? $taxSubtotals[0]
                                                    : [];
                                            $taxCategory = $firstSubtotal['tax_category'] ?? 'Tax';
                                        @endphp
                                        {{ $taxCategory }}
                                    </td>
                                    <td class="p-3 text-right font-semibold text-zinc-900 dark:text-zinc-100">
                                        @php
                                            $currency = $invoice->document_currency_code;
                                            $currencySymbol = match ($currency) {
                                                'NGN' => '₦',
                                                'USD' => '$',
                                                'EUR' => '€',
                                                'GBP' => '£',
                                                'CAD' => 'CA$',
                                                'GHS' => 'GH₵',
                                                default => $currency . ' ',
                                            };
                                        @endphp
                                        {{ $currencySymbol }}{{ number_format($tax->tax_amount ?? 0, 2) }}
                                    </td>
                                    <td class="p-3 text-zinc-600 dark:text-zinc-400">
                                        @if (is_array($taxSubtotals) && count($taxSubtotals) > 0)
                                            @foreach ($taxSubtotals as $subtotal)
                                                <div class="text-xs">
                                                    Rate: {{ $subtotal['tax_percentage'] ?? 0 }}% |
                                                    Base:
                                                    {{ $currencySymbol }}{{ number_format($subtotal['taxable_amount'] ?? 0, 2) }}
                                                </div>
                                            @endforeach
                                        @else
                                            No additional details
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Additional Information --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- QR Code and Transmission Status --}}
            <div class="space-y-4">
                @if ($qrDataUri)
                    <div
                        class="bg-gradient-to-br from-zinc-50 to-white dark:from-zinc-800/60 dark:to-zinc-900/40 rounded-xl p-6 border border-zinc-200 dark:border-zinc-700/50 shadow-lg backdrop-blur-sm">
                        <h4 class="font-semibold text-sm text-zinc-800 dark:text-zinc-200 mb-4 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-zinc-600 dark:text-zinc-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                            FIRS QR Code
                        </h4>
                        <div class="flex justify-center">
                            <img src="{{ $qrDataUri }}" alt="FIRS QR Code" title="FIRS QR Code"
                                class="border-2 border-zinc-300 dark:border-zinc-600 rounded-lg p-3 bg-white shadow-md hover:shadow-lg transition-shadow duration-200"
                                style="max-width: 200px; height: auto;">
                        </div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 text-center mt-3">
                            Scan this QR code to verify invoice authenticity
                        </p>
                    </div>
                @endif

                {{-- Transmission Status --}}
                @if ($invoice->transmissions->count() > 0)
                    <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4">
                        <h4 class="font-semibold text-sm text-zinc-800 dark:text-zinc-200 mb-2 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            Transmission Status
                        </h4>
                        <div class="space-y-2">
                            @foreach ($invoice->transmissions as $transmission)
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-zinc-600 dark:text-zinc-400">
                                        {{ \Carbon\Carbon::parse($transmission->created_at)->format('d M, Y H:i') }}
                                    </span>
                                    <span
                                        class="px-2 py-1 text-xs rounded 
                                        @if ($transmission->status === 'SUCCESS') bg-green-100 text-green-700 dark:bg-green-800 dark:text-green-200
                                        @elseif($transmission->status === 'FAILED') bg-red-100 text-red-700 dark:bg-red-800 dark:text-red-200
                                        @else bg-amber-100 text-amber-700 dark:bg-amber-800 dark:text-amber-200 @endif">
                                        {{ $transmission->status }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Totals --}}
            <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4">
                <h4 class="font-semibold text-sm text-zinc-800 dark:text-zinc-200 mb-3 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Invoice Summary
                </h4>
                @php
                    $currency = $invoice->document_currency_code;
                    $currencySymbol = match ($currency) {
                        'NGN' => '₦',
                        'USD' => '$',
                        'EUR' => '€',
                        'GBP' => '£',
                        'CAD' => 'CA$',
                        'GHS' => 'GH₵',
                        default => $currency . ' ',
                    };
                    $lineExtension = isset($invoice->legal_monetary_total['line_extension_amount'])
                        ? $invoice->legal_monetary_total['line_extension_amount']
                        : $invoice->lines->sum('line_total');
                    $taxExclusive = isset($invoice->legal_monetary_total['tax_exclusive_amount'])
                        ? $invoice->legal_monetary_total['tax_exclusive_amount']
                        : $lineExtension;
                    $taxAmount = $invoice->taxTotals->sum('tax_amount');
                    $taxInclusive = isset($invoice->legal_monetary_total['tax_inclusive_amount'])
                        ? $invoice->legal_monetary_total['tax_inclusive_amount']
                        : $taxExclusive + $taxAmount;
                @endphp
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-zinc-600 dark:text-zinc-400">Subtotal:</span>
                        <span
                            class="text-zinc-900 dark:text-zinc-100">{{ $currencySymbol }}{{ number_format($lineExtension, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-600 dark:text-zinc-400">Tax Exclusive:</span>
                        <span
                            class="text-zinc-900 dark:text-zinc-100">{{ $currencySymbol }}{{ number_format($taxExclusive, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-600 dark:text-zinc-400">Tax Amount:</span>
                        <span
                            class="text-zinc-900 dark:text-zinc-100">{{ $currencySymbol }}{{ number_format($taxAmount, 2) }}</span>
                    </div>
                    <div class="border-t border-zinc-200 dark:border-zinc-700 pt-2">
                        <div class="flex justify-between text-base font-bold">
                            <span class="text-zinc-800 dark:text-zinc-200">Grand Total:</span>
                            <span
                                class="text-zinc-900 dark:text-zinc-100">{{ $currencySymbol }}{{ number_format($taxInclusive, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Invoice Attachments --}}
        @if ($invoice->attachments->count() > 0)
            <div>
                <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200 mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-zinc-600 dark:text-zinc-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                    </svg>
                    Attachments
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($invoice->attachments as $attachment)
                        <div
                            class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-8 h-8 text-zinc-400 dark:text-zinc-500" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">
                                        {{ $attachment->file_name ?? 'Attachment #' . $loop->iteration }}
                                    </p>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ \Carbon\Carbon::parse($attachment->created_at)->format('d M, Y') }}
                                    </p>
                                </div>
                                <div class="flex-shrink-0">
                                    <a href="#"
                                        class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm">
                                        View
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Footer Actions --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-t pt-6">
            {{-- Invoice Notes --}}
            @if ($invoice->note || $invoice->payment_terms_note)
                <div class="flex-1 max-w-md">
                    @if ($invoice->note)
                        <div class="mb-3">
                            <h4 class="text-sm font-semibold text-zinc-800 dark:text-zinc-200 mb-1">Notes</h4>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                {{ is_array($invoice->note) ? implode(', ', $invoice->note) : $invoice->note }}</p>
                        </div>
                    @endif
                    @if ($invoice->payment_terms_note)
                        <div>
                            <h4 class="text-sm font-semibold text-zinc-800 dark:text-zinc-200 mb-1">Payment Terms</h4>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                {{ is_array($invoice->payment_terms_note) ? implode(', ', $invoice->payment_terms_note) : $invoice->payment_terms_note }}
                            </p>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Action Buttons --}}
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('invoices.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-zinc-600 text-white rounded-lg shadow-sm hover:bg-zinc-700 transition-colors duration-200 cursor-pointer">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Invoices
                </a>

                <button onclick="window.print()"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg shadow-sm hover:bg-blue-700 transition-colors duration-200 cursor-pointer">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print Invoice
                </button>

                @if ($invoice->transmit)
                    <button wire:click="transmitInvoice" wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg shadow-sm hover:bg-green-700 transition-colors duration-200 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="transmitInvoice">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            Transmit Invoice
                        </span>
                        <span wire:loading wire:target="transmitInvoice" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Transmitting...
                        </span>
                    </button>
                @endif
            </div>
        </div>
    </div>
</section>

@script
    <script>
        // Listen for Livewire events
        Livewire.on('success', (message) => {
            // Show success notification
            const successDiv = document.createElement('div');
            successDiv.className =
                'mb-6 rounded-xl bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/30 dark:to-emerald-900/30 border border-green-200/50 dark:border-green-700/50 p-4 shadow-sm backdrop-blur-sm';
            successDiv.innerHTML = `
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-500 dark:text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-semibold text-green-800 dark:text-green-200">${message}</p>
                </div>
            </div>
        `;

            // Insert at the top of the section
            const section = document.querySelector('section');
            section.insertBefore(successDiv, section.firstChild);

            // Auto-remove after 5 seconds
            setTimeout(() => {
                successDiv.remove();
            }, 5000);
        });

        Livewire.on('error', (message) => {
            // Show error notification
            const errorDiv = document.createElement('div');
            errorDiv.className =
                'mb-6 rounded-xl bg-gradient-to-r from-red-50 to-rose-50 dark:from-red-900/30 dark:to-rose-900/30 border border-red-200/50 dark:border-red-700/50 p-4 shadow-sm backdrop-blur-sm';
            errorDiv.innerHTML = `
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500 dark:text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-semibold text-red-800 dark:text-red-200">${message}</p>
                </div>
            </div>
        `;

            // Insert at the top of the section
            const section = document.querySelector('section');
            section.insertBefore(errorDiv, section.firstChild);

            // Auto-remove after 5 seconds
            setTimeout(() => {
                errorDiv.remove();
            }, 5000);
        });
    </script>
@endscript
