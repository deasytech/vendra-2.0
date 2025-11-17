<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Logo')" :subheading="__('Upload your company logo to personalize your account')">
        <div class="space-y-6">
            <!-- Current Logo Display -->
            <div>
                <label
                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Current Logo') }}</label>
                <div class="mt-2">
                    @if ($existingLogo)
                        <div class="relative inline-block">
                            <img src="{{ Storage::url($existingLogo) }}" alt="Current Logo"
                                class="h-20 w-20 object-contain rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <button wire:click="removeLogo"
                                class="absolute -top-2 -right-2 rounded-full bg-red-600 hover:bg-red-700 text-white p-1 text-xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    @else
                        <div
                            class="flex h-20 w-20 items-center justify-center rounded-lg border-2 border-dashed border-zinc-300 dark:border-zinc-600 bg-zinc-50 dark:bg-zinc-800">
                            <svg class="w-8 h-8 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Logo Upload -->
            <div>
                <label
                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Upload New Logo') }}</label>
                <div class="mt-2">
                    <input type="file" wire:model="logo" accept="image/*"
                        class="block w-full text-sm text-zinc-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-lg file:border-0
                        file:text-sm file:font-semibold
                        file:bg-zinc-50 file:text-zinc-700
                        hover:file:bg-zinc-100
                        dark:file:bg-zinc-800 dark:file:text-zinc-300
                        dark:hover:file:bg-zinc-700
                        cursor-pointer" />

                    @error('logo')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror

                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Recommended: PNG or JPG format, maximum 2MB') }}
                    </p>
                </div>
            </div>

            <!-- Upload Button -->
            <div class="flex items-center gap-3">
                <button wire:click="updateLogo"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="!$logo">
                    {{ __('Upload Logo') }}
                </button>

                @if (session()->has('message'))
                    <div class="flex-1 p-3 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                        {{ session('message') }}
                    </div>
                @endif
            </div>
        </div>
    </x-settings.layout>
</section>

@script
    <script>
        $wire.on('logo-updated', () => {
            // Refresh the page to show the new logo in the sidebar
            window.location.reload();
        });

        $wire.on('logo-removed', () => {
            // Refresh the page to remove the logo from the sidebar
            window.location.reload();
        });
    </script>
@endscript
