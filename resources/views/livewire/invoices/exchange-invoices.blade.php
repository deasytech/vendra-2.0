<div class="max-w-7xl mx-auto p-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Exchange Invoices</h1>
                <p class="text-purple-100">Manage invoices received from FIRS Exchange</p>
                <div class="mt-2 flex items-center space-x-4 text-sm text-purple-200">
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Auto-sync via webhook
                    </span>
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Manual sync available
                    </span>
                </div>
            </div>
            <div class="text-white text-right">
                <div class="text-sm opacity-75">Total Received</div>
                <div class="text-2xl font-bold">{{ $invoices->total() }}</div>
            </div>
        </div>
    </div>

    <!-- Filters and Actions -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <!-- Search -->
            <div class="flex-1 min-w-[200px]">
                <label for="search" class="sr-only">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search" id="search"
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg leading-5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-purple-500 focus:border-purple-500 sm:text-sm"
                        placeholder="Search by reference, IRN, or supplier...">
                </div>
            </div>

            <!-- Date Filters -->
            <div class="flex flex-col sm:flex-row gap-3">
                <div>
                    <label for="dateFrom"
                        class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">From</label>
                    <input type="date" wire:model.live="dateFrom" id="dateFrom"
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                </div>
                <div>
                    <label for="dateTo"
                        class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">To</label>
                    <input type="date" wire:model.live="dateTo" id="dateTo"
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                </div>
            </div>

            <!-- Sync Button -->
            <div>
                <button wire:click="syncFromTaxly" wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                    <svg wire:loading.remove wire:target="syncFromTaxly" class="mr-2 -ml-1 h-5 w-5" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <svg wire:loading wire:target="syncFromTaxly" class="animate-spin mr-2 -ml-1 h-5 w-5 text-white"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    Sync from FIRS
                </button>
            </div>
        </div>
    </div>


    <!-- Sync Modal -->
    @if ($showSyncModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50" wire:click="closeSyncModal">
        </div>
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div
                    class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <div class="bg-white dark:bg-gray-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            @if ($syncError)
                                <div
                                    class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            @else
                                <div
                                    class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-purple-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                </div>
                            @endif
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white"
                                    id="modal-title">
                                    {{ $syncError ? 'Sync Failed' : 'Sync Results' }}
                                </h3>
                                <div class="mt-2">
                                    <p
                                        class="text-sm {{ $syncError ? 'text-red-600 dark:text-red-400 font-medium' : 'text-gray-500 dark:text-gray-400' }}">
                                        {{ $syncMessage }}
                                    </p>

                                    @if ($syncError)
                                        <div
                                            class="mt-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                                            <div class="text-sm text-red-800 dark:text-red-300">
                                                <p class="font-semibold mb-1">Error Details:</p>

                                                @if (!empty($syncError['details']))
                                                    <p class="mb-2 text-red-700 dark:text-red-300 font-medium">
                                                        {{ $syncError['details'] }}
                                                    </p>
                                                @endif

                                                <p class="mb-1"><span class="font-medium">Message:</span>
                                                    {{ $syncError['message'] }}</p>
                                                @if (!empty($syncError['type']))
                                                    <p class="mb-1"><span class="font-medium">Type:</span>
                                                        {{ $syncError['type'] }}</p>
                                                @endif
                                                @if (!empty($syncError['code']))
                                                    <p><span class="font-medium">Code:</span> {{ $syncError['code'] }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    @if (!empty($syncResults))
                                        <div class="mt-4 grid grid-cols-3 gap-4 text-center">
                                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                                <div class="text-2xl font-bold text-purple-600">
                                                    {{ $syncResults['synced'] }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">New</div>
                                            </div>
                                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                                <div class="text-2xl font-bold text-gray-600 dark:text-gray-300">
                                                    {{ $syncResults['existing'] }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">Existing</div>
                                            </div>
                                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                                <div class="text-2xl font-bold text-indigo-600">
                                                    {{ $syncResults['total'] }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">Total</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" wire:click="closeSyncModal"
                            class="inline-flex w-full justify-center rounded-md {{ $syncError ? 'bg-red-600 hover:bg-red-500' : 'bg-purple-600 hover:bg-purple-500' }} px-3 py-2 text-sm font-semibold text-white shadow-sm sm:ml-3 sm:w-auto">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Exchange Invoices Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-4v4m0 0V2m0 4H4m4 0h4" />
                </svg>
                Received Invoices from Exchange
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Supplier</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            IRN
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Issue Date</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Status</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Total</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($invoices as $invoice)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    {{ data_get($invoice->accounting_supplier_party, 'party_name', 'Unknown') }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ data_get($invoice->accounting_supplier_party, 'party_tin', data_get($invoice->accounting_supplier_party, 'tin', '')) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white font-mono">
                                    {{ $invoice->irn ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    {{ \Carbon\Carbon::parse($invoice->issue_date)->format('M d, Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">
                                    INCOMING
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $storedPayableAmount =
                                        (float) ($invoice->legal_monetary_total['payable_amount'] ?? 0);
                                    $decryptedPayableAmount =
                                        (float) (data_get(
                                            $invoice->metadata,
                                            'decrypted_invoice.legal_monetary_total.payable_amount',
                                        ) ??
                                            (data_get($invoice->metadata, 'decrypted_invoice.payableAmount') ??
                                                (data_get($invoice->metadata, 'decrypted_invoice.totalAmount') ?? 0)));

                                    $payableAmount =
                                        $storedPayableAmount > 0 ? $storedPayableAmount : $decryptedPayableAmount;
                                @endphp
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $invoice->document_currency_code === 'NGN' ? '₦' : '$' }}{{ number_format((float) $payableAmount, 2) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('exchange-invoice.show', $invoice->id) }}"
                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-4v4m0 0V2m0 4H4m4 0h4" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No exchange invoices
                                    found</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Invoices received from FIRS
                                    Exchange will appear
                                    here.</p>
                                <div class="mt-6">
                                    <button wire:click="syncFromTaxly" type="button"
                                        class="inline-flex items-center rounded-md bg-purple-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-purple-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-purple-600 cursor-pointer">
                                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        Sync from FIRS
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($invoices->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
