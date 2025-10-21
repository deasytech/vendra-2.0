<div class="max-w-6xl mx-auto p-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Create Invoice</h1>
                <p class="text-blue-100">Generate professional invoices with Taxly integration</p>
            </div>
            <div class="text-white text-right">
                <div class="text-sm opacity-75">Invoice Date</div>
                <div class="text-lg font-semibold">{{ now()->format('M d, Y') }}</div>
            </div>
        </div>
    </div>

    <!-- Status Messages -->
    @if ($message)
        <div
            class="mb-6 p-4 rounded-xl {{ $errors->has('submission') || $errors->has('validation') ? 'bg-red-50 border border-red-200 text-red-700' : 'bg-green-50 border border-green-200 text-green-700' }}">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    @if ($errors->has('submission') || $errors->has('validation'))
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    @else
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    @endif
                </svg>
                {{ $message }}
            </div>
        </div>
    @endif

    <!-- Invoice Details Section -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Invoice Details
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Invoice Reference *</label>
                <input wire:model.defer="invoice_reference"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    placeholder="INV-2024-001" />
                @error('invoice_reference')
                    <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Issue Date *</label>
                <input type="date" wire:model.defer="issue_date"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
                @error('issue_date')
                    <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                <input type="date" wire:model.defer="due_date"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Invoice Type</label>
                <select wire:model.defer="invoice_type_code"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <option value="">Select invoice type...</option>
                    @foreach ($invoice_types as $type)
                        <option value="{{ $type['code'] ?? ($type['id'] ?? ($type['value'] ?? '396')) }}">
                            {{ $type['value'] ?? ($type['label'] ?? ($type['description'] ?? 'Unknown Type')) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Supplier Section -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            Supplier (Your Company)
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Supplier Type</label>
                <select wire:model="supplier_type"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <option value="business">Business</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Supplier</label>
                <select wire:model.live.debounce.500ms="selected_supplier_id"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <option value="">Choose a supplier...</option>
                    @if ($supplier_type === 'business')
                        @foreach ($this->businesses as $business)
                            <option value="{{ $business->id }}">{{ $business->name }} - {{ $business->tin }}</option>
                        @endforeach
                    @else
                        @foreach ($this->organizations as $organization)
                            <option value="{{ $organization->id }}">{{ $organization->legal_name }} -
                                {{ $organization->registration_number }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Company Name *</label>
                <input wire:model.defer="supplier.party_name"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    placeholder="Enter company name" />
                @error('supplier.party_name')
                    <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">TIN</label>
                <input wire:model.defer="supplier.tin"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    placeholder="Tax Identification Number" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" wire:model.defer="supplier.email"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    placeholder="supplier@company.com" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                <input wire:model.defer="supplier.telephone"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    placeholder="+234..." />
            </div>
        </div>
    </div>

    <!-- Customer Section -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Customer
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Customer Type</label>
                <select wire:model="customer_type"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <option value="business">Business</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Customer</label>
                <select wire:model.live.debounce.500ms="selected_customer_id"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <option value="">Choose a customer...</option>
                    @if ($customer_type === 'business')
                        @foreach ($this->businesses as $business)
                            <option value="{{ $business->id }}">{{ $business->name }} - {{ $business->tin }}
                            </option>
                        @endforeach
                    @else
                        @foreach ($this->organizations as $organization)
                            <option value="{{ $organization->id }}">{{ $organization->legal_name }} -
                                {{ $organization->registration_number }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Customer Name *</label>
                <input wire:model.defer="customer.party_name"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    placeholder="Enter customer name" />
                @error('customer.party_name')
                    <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">TIN</label>
                <input wire:model.defer="customer.tin"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    placeholder="Tax Identification Number" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" wire:model.defer="customer.email"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    placeholder="customer@company.com" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                <input wire:model.defer="customer.telephone"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    placeholder="+234..." />
            </div>
        </div>
    </div>

    <!-- Invoice Lines Section -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Invoice Items
            </h2>
            <button wire:click.prevent="addLine"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center cursor-pointer">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Item
            </button>
        </div>

        <div class="space-y-4">
            @foreach ($invoice_lines as $idx => $line)
                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50" wire:key="line-{{ $idx }}">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Item Name *</label>
                            <input wire:model.defer="invoice_lines.{{ $idx }}.item.name"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Product or service name" />
                            @error('invoice_lines.' . $idx . '.item.name')
                                <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Item Description *</label>
                            <input wire:model.defer="invoice_lines.{{ $idx }}.item.description"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Product or service description" />
                            @error('invoice_lines.' . $idx . '.item.description')
                                <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quantity *</label>
                            <input type="number"
                                wire:model.live.debounce.500ms="invoice_lines.{{ $idx }}.invoiced_quantity"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                min="1" />
                            @error('invoice_lines.' . $idx . '.invoiced_quantity')
                                <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Unit Price (₦) *</label>
                            <input type="number" step="0.01"
                                wire:model.live.debounce.500ms="invoice_lines.{{ $idx }}.price.price_amount"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="0.00" />
                            @error('invoice_lines.' . $idx . '.price.price_amount')
                                <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Total (₦)</label>
                            <div
                                class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg font-semibold text-gray-700">
                                ₦{{ number_format(((float) ($line['price']['price_amount'] ?: 0)) * ((float) ($line['invoiced_quantity'] ?: 0)), 2) }}
                            </div>
                        </div>

                        <div class="md:col-span-1">
                            <button wire:click.prevent="removeLine({{ $idx }})"
                                class="w-full px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Totals Section -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            Invoice Summary
        </h2>

        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex justify-between items-center mb-2">
                <span class="text-gray-600">Subtotal:</span>
                <span class="font-semibold">₦{{ number_format($sub_total, 2) }}</span>
            </div>
            <div class="flex justify-between items-center mb-2">
                <span class="text-gray-600">VAT ({{ $vat_rate }}%):</span>
                <span class="font-semibold">₦{{ number_format($vat_amount, 2) }}</span>
            </div>
            <div class="border-t pt-2 flex justify-between items-center">
                <span class="text-lg font-semibold text-gray-800">Total:</span>
                <span class="text-2xl font-bold text-blue-600">₦{{ number_format($total_amount, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Taxly Integration Section -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            Taxly Integration
        </h2>

        <div class="bg-teal-50 border border-teal-200 rounded-lg p-4 mb-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-teal-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd" />
                </svg>
                <span class="text-teal-800">Validate your invoice before submission to ensure compliance with FIRS
                    requirements.</span>
            </div>
        </div>

        <div class="flex flex-wrap gap-4">
            <button wire:click.prevent="validateIRN"
                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center cursor-pointer"
                :disabled="$wire.validating">
                <span wire:loading.remove wire:target="validateIRN">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Validate IRN
                </span>
                <span wire:loading wire:target="validateIRN">
                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    Validating...
                </span>
            </button>

            <button wire:click.prevent="validateInvoice"
                class="px-6 py-3 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition-colors flex items-center cursor-pointer"
                :disabled="$wire.validating">
                <span wire:loading.remove wire:target="validateInvoice">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Validate Invoice
                </span>
                <span wire:loading wire:target="validateInvoice">
                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    Validating...
                </span>
            </button>

            <label
                class="flex items-center px-6 py-3 bg-gray-100 rounded-lg cursor-pointer hover:bg-gray-200 transition-colors">
                <input type="checkbox" wire:model="submit_to_firs" class="mr-2 rounded">
                <span class="text-gray-700">Submit to FIRS immediately after validation</span>
            </label>
        </div>

        @error('validation')
            <div class="mt-4 text-red-600 text-sm">{{ $message }}</div>
        @enderror
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-wrap gap-4 justify-end">
        <button wire:click.prevent="submitInvoice"
            class="px-8 py-4 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all flex items-center font-semibold text-lg shadow-lg cursor-pointer"
            :disabled="$wire.submitting">
            <span wire:loading.remove wire:target="submitInvoice">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
                Submit Invoice
            </span>
            <span wire:loading wire:target="submitInvoice">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                Submitting...
            </span>
        </button>
    </div>

    @error('submission')
        <div class="mt-4 text-red-600 text-center">{{ $message }}</div>
    @enderror
</div>

@push('scripts')
    <script>
        Livewire.on('validation-success', function(data) {
            // Show success notification
            if (window.Notification) {
                new Notification('Invoice Validation', {
                    body: data.message,
                    icon: '/favicon.ico'
                });
            }
        });

        Livewire.on('invoice-submitted', function(data) {
            // Show success notification
            if (window.Notification) {
                new Notification('Invoice Submitted', {
                    body: 'Invoice has been successfully submitted to Taxly!',
                    icon: '/favicon.ico'
                });
            }

            // Optionally redirect to invoice view
            // window.location.href = '/invoices/' + data.invoice_id;
        });
    </script>
@endpush
