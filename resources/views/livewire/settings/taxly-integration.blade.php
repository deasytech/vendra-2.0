<div class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('')" :subheading="__('')">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Taxly Integration</h1>
                    <p class="text-blue-100">Register as a Taxly integrator to receive exchange invoices</p>
                </div>
                @if ($credential?->is_integrator)
                    <div class="text-white text-right">
                        <div class="text-sm opacity-75">Integrator Status</div>
                        <div class="text-2xl font-bold capitalize">{{ $credential->integrator_status }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Status Messages -->
        @if (session()->has('success'))
            <div
                class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-green-800 dark:text-green-300 font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session()->has('warning'))
            <div
                class="mb-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-yellow-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="text-yellow-800 dark:text-yellow-300 font-medium">{{ session('warning') }}</span>
                </div>
            </div>
        @endif

        @if ($errorMessage)
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-red-800 font-medium">{{ $errorMessage }}</span>
                </div>
            </div>
        @endif

        <!-- Integration Status Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Integration Status</h2>

            @if (!$credential?->tenant_id)
                <div class="text-center py-8">
                    <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Not Registered as Integrator</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">To receive exchange invoices from
                        other
                        businesses,
                        you
                        need to register as a Taxly integrator. This allows your suppliers to send invoices directly
                        to
                        your
                        Vendra account.</p>
                    <button wire:click="toggleRegistrationForm"
                        class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Register as Integrator
                    </button>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tenant ID</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono">
                                    {{ $credential->tenant_id }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tenant Name</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $credential->tenant_name }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Integrator Status</dt>
                                <dd class="mt-1">
                                    @php
                                        $statusClass = match ($credential->integrator_status) {
                                            'approved'
                                                => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
                                            'rejected'
                                                => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
                                            'pending'
                                                => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300',
                                            default => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300',
                                        };
                                    @endphp
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                        {{ ucfirst($credential->integrator_status) }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">API Key</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    @if ($credential->api_key)
                                        <span class="font-mono text-green-600 dark:text-green-400">✓ Configured</span>
                                    @else
                                        <span class="text-red-500 dark:text-red-400">✗ Not configured</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Business ID</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono">
                                    {{ $organization->business_id ?? 'Not set' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    @if ($credential->integrator_status === 'approved' && !$credential->api_key)
                        <button wire:click="toggleApiKeyForm"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Generate API Key
                        </button>
                        <button wire:click="toggleManualApiKeyForm"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Enter API Key Manually
                        </button>
                    @elseif ($credential->integrator_status === 'pending')
                        <button disabled
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gray-400 cursor-not-allowed">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Awaiting Approval
                        </button>
                    @elseif ($credential->api_key)
                        <button wire:click="clearApiKey"
                            class="inline-flex items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-md shadow-sm text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Clear API Key
                        </button>
                    @endif
                    <button wire:click="checkIntegratorStatus"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Refresh Status
                    </button>
                </div>

                @if ($credential->integrator_status === 'pending')
                    <div
                        class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 mr-2" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Application Under
                                    Review</h4>
                                <p class="text-sm text-yellow-700 dark:text-yellow-400 mt-1">
                                    Your integrator application has been submitted and is pending approval from Taxly.
                                    This process typically takes 1-2 business days. You will be able to generate API
                                    keys once approved.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>

        <!-- Integrator Registration Form -->
        @if ($showRegistrationForm)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Register as Taxly Integrator</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">Complete the form below to register your organization
                    as a Taxly
                    integrator.
                    This will allow you to receive exchange invoices from your suppliers.</p>

                <form wire:submit.prevent="registerIntegrator" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="integratorName"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Organization
                                Name</label>
                            <input type="text" wire:model="integratorName" id="integratorName"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('integratorName')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="integratorBrand"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Brand
                                Name</label>
                            <input type="text" wire:model="integratorBrand" id="integratorBrand"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('integratorBrand')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="integratorDomain"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Domain</label>
                            <input type="text" wire:model="integratorDomain" id="integratorDomain"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('integratorDomain')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="integratorWebsite"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Website</label>
                            <input type="url" wire:model="integratorWebsite" id="integratorWebsite"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('integratorWebsite')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="integratorContactPerson"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contact
                                Person</label>
                            <input type="text" wire:model="integratorContactPerson" id="integratorContactPerson"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('integratorContactPerson')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="integratorContactEmail"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contact
                                Email</label>
                            <input type="email" wire:model="integratorContactEmail" id="integratorContactEmail"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('integratorContactEmail')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="integratorDescription"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                        <textarea wire:model="integratorDescription" id="integratorDescription" rows="3"
                            placeholder="e.g., {{ $organization?->legal_name ?? 'Our company' }} is a leading provider of [products/services] in Nigeria. We manage a network of suppliers and require a streamlined invoice management system to ensure compliance with FIRS e-invoicing regulations."
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"></textarea>
                        @error('integratorDescription')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Minimum 50 characters required. Describe your business
                            and why you need integrator access.</p>
                    </div>

                    <div>
                        <label for="integratorUseCase"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Use
                            Case</label>
                        <textarea wire:model="integratorUseCase" id="integratorUseCase" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"></textarea>
                        @error('integratorUseCase')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" wire:click="toggleRegistrationForm"
                            class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Register
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <!-- API Key Generation Form -->
        @if ($showApiKeyForm)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Generate API Key</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-2">Generate an API key to authenticate with Taxly API for
                    receiving exchange invoices.</p>
                <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-sm text-yellow-800">
                        <strong>⚠️ Note:</strong> Automatic API key generation may fail if Taxly's systems are not fully
                        synchronized.
                        If you see "not approved" errors even though your status shows approved, wait a few minutes and
                        try again,
                        or use the "Enter API Key Manually" button.
                    </p>
                </div>

                <form wire:submit.prevent="generateApiKey" class="space-y-6">
                    <div>
                        <label for="apiKeyName" class="block text-sm font-medium text-gray-700 dark:text-gray-300">API
                            Key
                            Name</label>
                        <input type="text" wire:model="apiKeyName" id="apiKeyName"
                            placeholder="e.g., Production API Key"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        @error('apiKeyName')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="apiKeyDescription"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                        <textarea wire:model="apiKeyDescription" id="apiKeyDescription" rows="2"
                            placeholder="Describe the purpose of this API key"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"></textarea>
                        @error('apiKeyDescription')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Permissions</label>
                        <div class="mt-2 space-y-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" wire:model="apiKeyPermissions" value="invoices.read" checked
                                    disabled
                                    class="rounded border-gray-300 dark:border-gray-600 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Read Invoices</span>
                            </label>
                            <label class="inline-flex items-center ml-4">
                                <input type="checkbox" wire:model="apiKeyPermissions" value="invoices.write" checked
                                    disabled
                                    class="rounded border-gray-300 dark:border-gray-600 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Write Invoices</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" wire:click="toggleApiKeyForm"
                            class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Generate API Key
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <!-- Manual API Key Entry Form -->
        @if ($showManualApiKeyForm)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Enter API Key Manually</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-2">If you already have an API key from Taxly, you can
                    enter it here.</p>
                <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-blue-800">
                        <strong>Note:</strong> This is useful if the automatic API key generation is not working due to
                        Taxly API authentication issues. Contact Taxly support if you need help obtaining an API key.
                    </p>
                </div>

                <form wire:submit.prevent="saveManualApiKey" class="space-y-6">
                    <div>
                        <label for="manualApiKey"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">API Key</label>
                        <input type="text" wire:model="manualApiKey" id="manualApiKey"
                            placeholder="Paste your Taxly API key here"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        @error('manualApiKey')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" wire:click="toggleManualApiKeyForm"
                            class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Save API Key
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <!-- API Key Result -->
        @if ($apiKeyResult && isset($apiKeyResult['api_key']))
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 mb-6">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-yellow-600 mt-0.5 mr-3" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-lg font-medium text-yellow-900 mb-2">API Key Generated - Copy Now!</h3>
                        <p class="text-yellow-800 mb-3">This API key will only be shown once. Please copy it and
                            store
                            it
                            securely.</p>
                        <div class="bg-white rounded-lg p-3 border border-yellow-300">
                            <code
                                class="text-sm font-mono text-gray-900 break-all">{{ $apiKeyResult['api_key']['key'] }}</code>
                        </div>
                        <button wire:click="$set('apiKeyResult', null)"
                            class="mt-3 text-sm text-yellow-700 hover:text-yellow-900 font-medium">
                            I've copied the key
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Quick Actions -->
        @if ($credential?->is_integrator)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Quick Actions</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="{{ route('exchange-invoices') }}"
                        class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-4v4m0 0V2m0 4H4m4 0h4" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">View Exchange Invoices</h3>
                            <p class="text-sm text-gray-500">See invoices received from your suppliers</p>
                        </div>
                    </a>

                    <div class="flex items-center p-4 border border-gray-200 rounded-lg">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">Webhook URL</h3>
                            <p class="text-sm text-gray-500 font-mono">{{ url('/api/taxly/webhook/invoice') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </x-settings.layout>
</div>
