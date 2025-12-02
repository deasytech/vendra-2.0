<div class="max-w-7xl mx-auto p-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-teal-600 to-blue-600 rounded-xl shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Invoice Exchange</h1>
                <p class="text-teal-100">View all invoices transmitting through Taxly</p>
            </div>
            <div class="text-white text-right">
                <div class="text-sm opacity-75">Total Transmitted</div>
                <div class="text-2xl font-bold">{{ $invoices->total() }}</div>
            </div>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-lg p-4 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Transmitted</p>
                    <p class="text-2xl font-bold text-gray-800">
                        {{ $invoices->where('transmit', 'TRANSMITTED')->count() }}</p>
                </div>
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-4 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Transmitting</p>
                    <p class="text-2xl font-bold text-gray-800">
                        {{ $invoices->where('transmit', 'TRANSMITTING')->count() }}</p>
                </div>
                <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-4 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Failed</p>
                    <p class="text-2xl font-bold text-gray-800">
                        {{ $invoices->where('transmit', 'FAILED')->count() }}</p>
                </div>
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Transmitted Invoices Table -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Transmitted Invoice List
            </h2>
        </div>

        <div class="overflow-x-visible">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            IRN</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Issue Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Transmission Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($invoices as $invoice)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $invoice->invoice_reference }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 font-mono">{{ $invoice->irn ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ \Carbon\Carbon::parse($invoice->issue_date)->format('M d, Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if ($invoice->transmit === 'TRANSMITTING') bg-yellow-100 text-yellow-800
                                    @elseif($invoice->transmit === 'TRANSMITTED') bg-green-100 text-green-800
                                    @elseif($invoice->transmit === 'FAILED') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $invoice->transmit }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    ₦{{ number_format($invoice->legal_monetary_total['payable_amount'] ?? 0, 2) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <flux:dropdown position="bottom" align="end">
                                    <flux:button variant="primary" size="sm" icon="ellipsis-vertical">
                                        <span class="sr-only">Open menu</span>
                                    </flux:button>

                                    <flux:menu class="w-48">
                                        <flux:menu.item icon="check" class="cursor-pointer"
                                            wire:click="confirmInvoice({{ $invoice->id }})">
                                            {{ __('Confirm Invoice') }}
                                        </flux:menu.item>

                                        <flux:menu.item icon="arrow-down-on-square" class="cursor-pointer"
                                            wire:click="downloadInvoice({{ $invoice->id }})">
                                            {{ __('Download Invoice') }}
                                        </flux:menu.item>

                                        <flux:menu.item icon="eye"
                                            href="{{ route('invoices.show', $invoice->id) }}">
                                            {{ __('View Invoice') }}
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No transmitted invoices found</h3>
                                <p class="mt-1 text-sm text-gray-500">Invoices will appear here after they are
                                    submitted to FIRS.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($invoices->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
