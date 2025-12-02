<div class="max-w-7xl mx-auto p-6">
    <!-- Toast Notifications -->
    @if (session()->has('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition class="fixed top-4 right-4 z-50">
            <div class="bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif
    <div x-data="{
        showSuccess: false,
        showError: false,
        successMessage: '',
        errorMessage: '',
        init() {
            Livewire.on('success', (message) => {
                this.successMessage = message;
                this.showSuccess = true;
                setTimeout(() => this.showSuccess = false, 5000);
            });
            Livewire.on('error', (message) => {
                this.errorMessage = message;
                this.showError = true;
                setTimeout(() => this.showError = false, 5000);
            });
        }
    }">
        <!-- Success Toast -->
        <div x-show="showSuccess" x-transition class="fixed top-4 right-4 z-50">
            <div class="bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                <span x-text="successMessage"></span>
            </div>
        </div>

        <!-- Error Toast -->
        <div x-show="showError" x-transition class="fixed top-4 right-4 z-50">
            <div class="bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd" />
                </svg>
                <span x-text="errorMessage"></span>
            </div>
        </div>
    </div>
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Invoices</h1>
                <p class="text-blue-100">Manage and track all your invoices</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('invoice.create') }}"
                    class="px-6 py-3 bg-white text-blue-600 rounded-lg hover:bg-blue-50 transition-colors flex items-center font-semibold shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Invoice
                </a>
                <div class="text-white text-right">
                    <div class="text-sm opacity-75">Total Invoices</div>
                    <div class="text-2xl font-bold">{{ $invoices->total() }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-lg p-4 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Paid</p>
                    <p class="text-2xl font-bold text-gray-800">
                        {{ $invoices->where('payment_status', 'paid')->count() }}</p>
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
                    <p class="text-sm text-gray-600">Pending</p>
                    <p class="text-2xl font-bold text-gray-800">
                        {{ $invoices->where('payment_status', 'pending')->count() }}</p>
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
                    <p class="text-sm text-gray-600">Overdue</p>
                    <p class="text-2xl font-bold text-gray-800">
                        {{ $invoices->where('payment_status', 'overdue')->count() }}</p>
                </div>
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-4 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Value</p>
                    <p class="text-2xl font-bold text-gray-800">
                        @php
                            $currencySymbol = '₦';
                        @endphp
                        {{ $currencySymbol }}{{ number_format($invoices->sum(function ($invoice) {return $invoice->legal_monetary_total['payable_amount'] ?? 0;}),2) }}
                    </p>
                </div>
                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Filters</h3>
            <button wire:click="resetFilters"
                class="text-sm text-blue-600 hover:text-blue-800 font-medium cursor-pointer">
                Reset All Filters
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" wire:model.live.debounce.300ms="search"
                    class="w-full px-3 py-2 text-slate-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Invoice reference or IRN...">
            </div>

            <!-- Customer -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                <select wire:model.live="customer_id"
                    class="w-full px-3 py-2 text-slate-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Customers</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Payment Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
                <select wire:model.live="payment_status"
                    class="w-full px-3 py-2 text-slate-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Statuses</option>
                    @foreach ($paymentStatuses as $status)
                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Transmission Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Transmission Status</label>
                <select wire:model.live="transmit_status"
                    class="w-full px-3 py-2 text-slate-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Statuses</option>
                    @foreach ($transmitStatuses as $status)
                        <option value="{{ $status }}">{{ $status }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Currency -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                <select wire:model.live="currency"
                    class="w-full px-3 py-2 text-slate-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Currencies</option>
                    @foreach ($currencies as $currency)
                        <option value="{{ $currency }}">{{ $currency }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                <input type="date" wire:model.live="date_from"
                    class="w-full px-3 py-2 text-slate-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                <input type="date" wire:model.live="date_to"
                    class="w-full px-3 py-2 text-slate-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Amount Range -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Amount Range</label>
                <div class="flex space-x-2">
                    <input type="number" wire:model.live.debounce.300ms="amount_min"
                        class="w-1/2 px-3 py-2 text-slate-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Min">
                    <input type="number" wire:model.live.debounce.300ms="amount_max"
                        class="w-1/2 px-3 py-2 text-slate-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Max">
                </div>
            </div>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Invoice List
                @if ($invoices->total() > 0)
                    <span class="ml-2 text-sm font-normal text-gray-500">({{ $invoices->total() }} results)</span>
                @endif
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Issue
                            Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Payment Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Total
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Exchange
                        </th>
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
                                <div class="text-sm text-gray-900">
                                    {{ \Carbon\Carbon::parse($invoice->issue_date)->format('M d, Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    @if ($invoice->business)
                                        {{ $invoice->business->name }}
                                    @elseif($invoice->organization)
                                        {{ $invoice->organization->legal_name }}
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if ($invoice->payment_status === 'paid') bg-green-100 text-green-800
                                    @elseif($invoice->payment_status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($invoice->payment_status === 'overdue') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($invoice->payment_status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    @php
                                        $symbol = '₦';
                                        switch ($invoice->document_currency_code) {
                                            case 'USD':
                                                $symbol = '$';
                                                break;
                                            case 'EUR':
                                                $symbol = '€';
                                                break;
                                            case 'GBP':
                                                $symbol = '£';
                                                break;
                                            case 'CAD':
                                                $symbol = 'CA$';
                                                break;
                                            case 'GHS':
                                                $symbol = 'GH₵';
                                                break;
                                        }
                                    @endphp
                                    {{ $symbol }}{{ number_format($invoice->legal_monetary_total['payable_amount'] ?? 0, 2) }}
                                </div>
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <flux:dropdown position="bottom" align="end">
                                    <flux:button variant="primary" size="sm" icon="ellipsis-vertical">
                                        <span class="sr-only">Open menu</span>
                                    </flux:button>

                                    <flux:menu class="w-48">
                                        <flux:menu.item icon="eye" wire:click="viewInvoice({{ $invoice->id }})">
                                            {{ __('View Details') }}
                                        </flux:menu.item>

                                        <flux:menu.item icon="pencil" wire:click="editInvoice({{ $invoice->id }})">
                                            {{ __('Edit Invoice') }}
                                        </flux:menu.item>

                                        <flux:menu.item icon="paper-airplane"
                                            wire:click="transmitInvoice({{ $invoice->id }})"
                                            wire:loading.attr="disabled">
                                            {{ __('Transmit Invoice') }}
                                        </flux:menu.item>

                                        <flux:menu.separator />

                                        <flux:modal.trigger name="confirm-invoice-cancellation-{{ $invoice->id }}">
                                            <flux:menu.item variant="danger" icon="trash">
                                                {{ __('Cancel') }}
                                            </flux:menu.item>
                                        </flux:modal.trigger>
                                    </flux:menu>
                                </flux:dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No invoices found</h3>
                                <p class="mt-1 text-sm text-gray-500">Get started by creating a new invoice.</p>
                                <div class="mt-6">
                                    <a href="{{ route('invoice.create') }}"
                                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                        Create Invoice
                                    </a>
                                </div>
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

    <!-- Cancellation Confirmation Modals -->
    @foreach ($invoices as $invoice)
        <flux:modal name="confirm-invoice-cancellation-{{ $invoice->id }}" class="max-w-lg">
            <div class="space-y-6">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-200 mb-2">Cancel Invoice</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Are you sure you want to cancel invoice <strong>{{ $invoice->invoice_reference }}</strong>?
                        This action will mark the invoice as cancelled and it will no longer be visible in the active
                        invoice list.
                    </p>
                    @if ($invoice->irn)
                        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                            <div class="flex">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        <strong>Note:</strong> This invoice has been transmitted to Taxly (IRN:
                                        {{ $invoice->irn }}).
                                        Cancelling it here will not affect the transmission status.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex justify-end space-x-3">
                    <flux:modal.close>
                        <flux:button variant="filled" class="!cursor-pointer">
                            {{ __('No, keep invoice') }}
                        </flux:button>
                    </flux:modal.close>

                    <flux:button variant="danger" wire:click="deleteInvoice({{ $invoice->id }})"
                        class="!cursor-pointer">
                        {{ __('Yes, cancel invoice') }}
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endforeach
</div>
