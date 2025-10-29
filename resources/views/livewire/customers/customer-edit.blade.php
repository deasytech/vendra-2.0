<div class="max-w-4xl mx-auto p-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Edit Customer</h1>
                <p class="text-purple-100">Update customer information</p>
            </div>
            <a href="{{ route('customers.index') }}" class="text-white hover:text-purple-200 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </a>
        </div>
    </div>

    <!-- Success Message -->
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('message') }}
        </div>
    @endif

    <!-- Customer Form -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <form wire:submit.prevent="update">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div class="md:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Basic Information</h3>
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Customer Name *</label>
                    <input type="text" id="name" wire:model="name"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-700"
                        @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tin" class="block text-sm font-medium text-gray-700 mb-2">TIN (Tax Identification
                        Number)</label>
                    <input type="text" id="tin" wire:model="tin"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-700"
                        @error('tin') border-red-500 @enderror">
                    @error('tin')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <input type="email" id="email" wire:model="email"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-700"
                        @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                    <input type="text" id="phone" wire:model="phone"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-700"
                        @error('phone') border-red-500 @enderror">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="business_description" class="block text-sm font-medium text-gray-700 mb-2">Business
                        Description</label>
                    <textarea id="business_description" wire:model="business_description" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-700"
                        @error('business_description') border-red-500 @enderror"></textarea>
                    @error('business_description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Address Information -->
                <div class="md:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Address Information</h3>
                </div>

                <div class="md:col-span-2">
                    <label for="street_name" class="block text-sm font-medium text-gray-700 mb-2">Street Address</label>
                    <input type="text" id="street_name" wire:model="street_name"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-700"
                        @error('street_name') border-red-500 @enderror">
                    @error('street_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="city_name" class="block text-sm font-medium text-gray-700 mb-2">City</label>
                    <input type="text" id="city_name" wire:model="city_name"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-700"
                        @error('city_name') border-red-500 @enderror">
                    @error('city_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="state" class="block text-sm font-medium text-gray-700 mb-2">State</label>
                    <input type="text" id="state" wire:model="state"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-700"
                        @error('state') border-red-500 @enderror">
                    @error('state')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="postal_zone" class="block text-sm font-medium text-gray-700 mb-2">Postal Code</label>
                    <input type="text" id="postal_zone" wire:model="postal_zone"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-700"
                        @error('postal_zone') border-red-500 @enderror">
                    @error('postal_zone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="country" class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                    <select id="country" wire:model="country"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-700"
                        @error('country') border-red-500 @enderror">
                        <option value="NG">Nigeria</option>
                        <option value="GH">Ghana</option>
                        <option value="US">United States</option>
                        <option value="GB">United Kingdom</option>
                        <option value="CA">Canada</option>
                    </select>
                    @error('country')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="mt-8 flex justify-end space-x-4">
                <a href="{{ route('customers.index') }}"
                    class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                    class="px-6 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors">
                    Update Customer
                </button>
            </div>
        </form>
    </div>
</div>
