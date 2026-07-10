<div class="bg-white rounded-xl shadow-lg p-6">
    <form wire:submit.prevent="save">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Product Details</h3>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Product Name <span
                        class="text-red-500">*</span></label>
                <input type="text" wire:model="name"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-gray-700 @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">SKU</label>
                <input type="text" wire:model="sku"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-gray-700 @error('sku') border-red-500 @enderror">
                @error('sku')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea wire:model="description" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-gray-700 @error('description') border-red-500 @enderror"></textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2 border-t border-gray-200 pt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Classification</h3>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">HSN Code</label>
                <div class="relative" x-data="{
                    open: false,
                    query: '',
                    options: @js($hs_codes),
                    init() {
                        const selected = this.options.find(item => item.code === @js($hsn_code));
                        this.query = selected ? selected.description : '';
                    },
                    matches() {
                        const term = this.query.toLowerCase().trim();
                        if (!term) {
                            return this.options;
                        }
                
                        return this.options
                            .filter(item => (`${item.code} ${item.description}`).toLowerCase().includes(term));
                    },
                    filtered() {
                        return this.matches().slice(0, 50);
                    },
                    select(item) {
                        this.query = item.description;
                        this.open = false;
                        $wire.set('hsn_code', item.code);
                    },
                    onInput() {
                        this.open = true;
                        $wire.set('hsn_code', '');
                    }
                }" @click.outside="open = false">
                    <input type="text" x-model="query" @focus="open = true" @input="onInput()"
                        placeholder="Search HSN code..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-gray-700 @error('hsn_code') border-red-500 @enderror">

                    <div x-show="open" x-transition
                        class="absolute z-20 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-56 overflow-y-auto">
                        <template x-if="filtered().length === 0">
                            <div class="px-3 py-2 text-sm text-gray-500">No matching HSN code.</div>
                        </template>

                        <template x-for="item in filtered()" :key="item.code">
                            <button type="button" @click="select(item)"
                                class="w-full text-left px-3 py-2 hover:bg-emerald-50">
                                <div class="text-sm text-gray-800" x-text="item.description"></div>
                                <div class="text-xs text-gray-500" x-text="item.code"></div>
                            </button>
                        </template>

                        <div x-show="matches().length > filtered().length"
                            class="px-3 py-2 text-xs text-gray-400 border-t border-gray-100 sticky bottom-0 bg-white"
                            x-text="`Showing ${filtered().length} of ${matches().length} — keep typing to narrow down`">
                        </div>
                    </div>
                </div>
                @error('hsn_code')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">ISIC Code</label>
                <div class="relative" x-data="{
                    open: false,
                    query: '',
                    options: @js($service_codes),
                    init() {
                        const selected = this.options.find(item => item.code === @js($isic_code));
                        this.query = selected ? selected.description : '';
                    },
                    matches() {
                        const term = this.query.toLowerCase().trim();
                        if (!term) {
                            return this.options;
                        }
                
                        return this.options
                            .filter(item => (`${item.code} ${item.description}`).toLowerCase().includes(term));
                    },
                    filtered() {
                        return this.matches().slice(0, 50);
                    },
                    select(item) {
                        this.query = item.description;
                        this.open = false;
                        $wire.set('isic_code', item.code);
                    },
                    onInput() {
                        this.open = true;
                        $wire.set('isic_code', '');
                    }
                }" @click.outside="open = false">
                    <input type="text" x-model="query" @focus="open = true" @input="onInput()"
                        placeholder="Search service code..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-gray-700 @error('isic_code') border-red-500 @enderror">

                    <div x-show="open" x-transition
                        class="absolute z-20 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-56 overflow-y-auto">
                        <template x-if="filtered().length === 0">
                            <div class="px-3 py-2 text-sm text-gray-500">No matching service code.</div>
                        </template>

                        <template x-for="item in filtered()" :key="item.code">
                            <button type="button" @click="select(item)"
                                class="w-full text-left px-3 py-2 hover:bg-emerald-50">
                                <div class="text-sm text-gray-800" x-text="item.description"></div>
                                <div class="text-xs text-gray-500" x-text="item.code"></div>
                            </button>
                        </template>

                        <div x-show="matches().length > filtered().length"
                            class="px-3 py-2 text-xs text-gray-400 border-t border-gray-100 sticky bottom-0 bg-white"
                            x-text="`Showing ${filtered().length} of ${matches().length} — keep typing to narrow down`">
                        </div>
                    </div>
                </div>
                @error('isic_code')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2 border-t border-gray-200 pt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Pricing</h3>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Unit Price <span
                        class="text-red-500">*</span></label>
                <input type="number" step="0.01" wire:model="unit_price"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-gray-700 @error('unit_price') border-red-500 @enderror">
                @error('unit_price')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Currency <span
                        class="text-red-500">*</span></label>
                <select wire:model="currency_code"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-gray-700 @error('currency_code') border-red-500 @enderror">
                    <option value="NGN">NGN</option>
                    <option value="USD">USD</option>
                    <option value="CAD">CAD</option>
                    <option value="EUR">EUR</option>
                    <option value="GBP">GBP</option>
                    <option value="GHS">GHS</option>
                </select>
                @error('currency_code')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Unit of Measure <span
                        class="text-red-500">*</span></label>
                <div class="relative" x-data="{
                    open: false,
                    query: '',
                    options: @js($unit_codes),
                    init() {
                        const selected = this.options.find(item => item.code === @js($unit_of_measure));
                        this.query = selected ? selected.description : '';
                    },
                    matches() {
                        const term = this.query.toLowerCase().trim();
                        if (!term) {
                            return this.options;
                        }
                
                        return this.options
                            .filter(item => (`${item.code} ${item.description}`).toLowerCase().includes(term));
                    },
                    filtered() {
                        return this.matches().slice(0, 50);
                    },
                    select(item) {
                        this.query = item.description;
                        this.open = false;
                        $wire.set('unit_of_measure', item.code);
                    },
                    onInput() {
                        this.open = true;
                        $wire.set('unit_of_measure', '');
                    }
                }" @click.outside="open = false">
                    <input type="text" x-model="query" @focus="open = true" @input="onInput()"
                        placeholder="Quantity measurement code..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-gray-700 @error('unit_of_measure') border-red-500 @enderror">

                    <div x-show="open" x-transition
                        class="absolute z-20 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-56 overflow-y-auto">
                        <template x-if="filtered().length === 0">
                            <div class="px-3 py-2 text-sm text-gray-500">No matching unit of measure.</div>
                        </template>

                        <template x-for="item in filtered()" :key="item.code">
                            <button type="button" @click="select(item)"
                                class="w-full text-left px-3 py-2 hover:bg-emerald-50">
                                <div class="text-sm text-gray-800" x-text="item.description"></div>
                                <div class="text-xs text-gray-500" x-text="item.code"></div>
                            </button>
                        </template>

                        <div x-show="matches().length > filtered().length"
                            class="px-3 py-2 text-xs text-gray-400 border-t border-gray-100 sticky bottom-0 bg-white"
                            x-text="`Showing ${filtered().length} of ${matches().length} — keep typing to narrow down`">
                        </div>
                    </div>
                </div>
                @error('unit_of_measure')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center pt-8">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" wire:model="is_active" class="mr-2 rounded">
                    <span class="text-gray-700">Active</span>
                </label>
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-4">
            <a href="{{ route('products.index') }}"
                class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <button type="submit"
                class="px-6 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-colors">
                {{ $submitLabel }}
            </button>
        </div>
    </form>
</div>
