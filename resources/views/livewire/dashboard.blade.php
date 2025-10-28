<div>
    <!-- Organization Completion Modal -->
    @if ($showOrganizationModal)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity duration-300">
            <div
                class="w-full max-w-lg rounded-lg bg-white dark:bg-neutral-800 p-6 shadow-xl transform transition-all duration-300 scale-100">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Complete Organization
                    Details</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                    Please complete your organization details to continue.
                </p>
                <form wire:submit.prevent="saveOrganizationDetails" class="space-y-4">
                    <div>
                        <label for="legal_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Legal Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="legal_name" id="legal_name"
                            class="mt-1 block w-full p-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                        @error('legal_name')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" wire:model="email" id="email"
                            class="mt-1 block w-full p-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                        @error('email')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Phone Number <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" wire:model="phone" id="phone"
                            class="mt-1 block w-full p-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                        @error('phone')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                        <div class="sm:col-span-2">
                            <label for="postal_address.street_name"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                Street Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="postal_address.street_name"
                                id="postal_address.street_name"
                                class="block w-full p-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xs"
                                placeholder="123 Main Street">
                            @error('postal_address.street_name')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="postal_address.city_name"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                City <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="postal_address.city_name" id="postal_address.city_name"
                                class="block w-full p-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xs"
                                placeholder="Lagos">
                            @error('postal_address.city_name')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="postal_address.postal_zone"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                Postal Code
                            </label>
                            <input type="text" wire:model="postal_address.postal_zone"
                                id="postal_address.postal_zone"
                                class="block w-full p-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xs"
                                placeholder="100001">
                            @error('postal_address.postal_zone')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="postal_address.country"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                Country Code <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="postal_address.country" id="postal_address.country"
                                class="block w-full p-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xs">
                                <option value="">Select Country</option>
                                <option value="NG">Nigeria (NG)</option>
                                <option value="GH">Ghana (GH)</option>
                                <option value="US">United States (US)</option>
                                <option value="GB">United Kingdom (GB)</option>
                                <option value="CA">Canada (CA)</option>
                                <option value="AU">Australia (AU)</option>
                                <option value="ZA">South Africa (ZA)</option>
                                <option value="KE">Kenya (KE)</option>
                                <option value="TZ">Tanzania (TZ)</option>
                                <option value="UG">Uganda (UG)</option>
                            </select>
                            @error('postal_address.country')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Description
                        </label>
                        <textarea wire:model="description" id="description" rows="2"
                            class="mt-1 block w-full p-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"></textarea>
                        @error('description')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end space-x-2">
                        <button type="submit" wire:loading.attr="disabled"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 cursor-pointer">
                            <span wire:loading.remove>Update</span>
                            <span wire:loading.flex class="items-center">
                                <svg class="animate-spin h-4 w-4 mr-2" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                                Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Success Message -->
    @if (session('success'))
        <div class="mb-4 rounded-md bg-green-50 dark:bg-green-900/20 p-4 border border-green-200 dark:border-green-800">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Dashboard Content -->
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <!-- Welcome Section -->
        <div
            class="rounded-xl bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-blue-900/20 dark:to-indigo-900/20 p-6 border border-blue-200 dark:border-blue-800">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        Welcome back, {{ auth()->user()->name }}!
                    </h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-300">
                        Here's what's happening with your business today.
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Today's Date</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ now()->format('l, F j, Y') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Key Metrics Cards -->
        <div class="grid auto-rows-min gap-6 md:grid-cols-2 lg:grid-cols-4">
            <!-- Total Invoices -->
            <div
                class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Invoices</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                            {{ App\Models\Invoice::where('tenant_id', auth()->user()->tenant_id)->count() }}
                        </p>
                        <p class="mt-1 text-xs text-green-600 dark:text-green-400">
                            <span class="inline-flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                +12% this month
                            </span>
                        </p>
                    </div>
                    <div class="rounded-full bg-blue-100 dark:bg-blue-900/30 p-3">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Pending Invoices -->
            <div
                class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending Invoices</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                            {{ App\Models\Invoice::where('tenant_id', auth()->user()->tenant_id)->where('payment_status', 'PENDING')->count() }}
                        </p>
                        <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                            <span class="inline-flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                        clip-rule="evenodd" />
                                </svg>
                                Requires attention
                            </span>
                        </p>
                    </div>
                    <div class="rounded-full bg-amber-100 dark:bg-amber-900/30 p-3">
                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Customers -->
            <div
                class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Customers</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                            {{ App\Models\Customer::where('tenant_id', auth()->user()->tenant_id)->count() + App\Models\Organization::where('tenant_id', auth()->user()->tenant_id)->count() }}
                        </p>
                        <p class="mt-1 text-xs text-green-600 dark:text-green-400">
                            <span class="inline-flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                Growing steadily
                            </span>
                        </p>
                    </div>
                    <div class="rounded-full bg-green-100 dark:bg-green-900/30 p-3">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Revenue This Month -->
            <div
                class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Revenue This Month</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                            ₦{{ number_format(App\Models\Invoice::where('tenant_id', auth()->user()->tenant_id)->whereMonth('created_at', now()->month)->sum('legal_monetary_total->payable_amount') ?? 0) }}
                        </p>
                        <p class="mt-1 text-xs text-purple-600 dark:text-purple-400">
                            <span class="inline-flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                +8% from last month
                            </span>
                        </p>
                    </div>
                    <div class="rounded-full bg-purple-100 dark:bg-purple-900/30 p-3">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Recent Activity -->
            <div
                class="lg:col-span-2 relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800">
                <div class="p-6 border-b border-neutral-200 dark:border-neutral-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Activity</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Latest invoice activities and updates</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @php
                            $recentInvoices = App\Models\Invoice::where('tenant_id', auth()->user()->tenant_id)
                                ->with([
                                    'transmissions' => function ($query) {
                                        $query->latest()->limit(1);
                                    },
                                ])
                                ->latest()
                                ->limit(5)
                                ->get();
                        @endphp

                        @forelse($recentInvoices as $invoice)
                            <div
                                class="flex items-center space-x-4 p-4 rounded-lg bg-gray-50 dark:bg-neutral-700/50 hover:bg-gray-100 dark:hover:bg-neutral-700 transition-colors">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        Invoice #{{ $invoice->invoice_reference }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $invoice->created_at->diffForHumans() }} •
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                            @if ($invoice->payment_status === 'PAID') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                            @elseif($invoice->payment_status === 'PENDING') bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400 @endif">
                                            {{ $invoice->payment_status }}
                                        </span>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        ₦{{ number_format($invoice->legal_monetary_total['payable_amount'] ?? 0) }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No recent activity
                                </h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new
                                    invoice.</p>
                                <div class="mt-6">
                                    <a href="{{ route('invoice.create') }}" wire:navigate
                                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Create Invoice
                                    </a>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div
                class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800">
                <div class="p-6 border-b border-neutral-200 dark:border-neutral-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Quick Actions</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Common tasks at your fingertips</p>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('invoice.create') }}" wire:navigate
                        class="flex items-center p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors group">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p
                                class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400">
                                Create New Invoice</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Start a new billing process</p>
                        </div>
                    </a>

                    <a href="{{ route('customers.index') }}" wire:navigate
                        class="flex items-center p-3 rounded-lg bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors group">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p
                                class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-green-600 dark:group-hover:text-green-400">
                                View Customers</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Manage your client base</p>
                        </div>
                    </a>

                    <a href="{{ route('invoice-exchange') }}" wire:navigate
                        class="flex items-center p-3 rounded-lg bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors group">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p
                                class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400">
                                View Transmitted</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Check submitted invoices</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
