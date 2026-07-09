<div class="max-w-4xl mx-auto p-6">
    <div class="bg-gradient-to-r from-emerald-600 to-teal-600 rounded-xl shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Create Product</h1>
                <p class="text-emerald-100">Add reusable item details for invoice lines</p>
            </div>
            <a href="{{ route('products.index') }}" class="text-white hover:text-emerald-200 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </a>
        </div>
    </div>

    @include('livewire.products.product-form', ['submitLabel' => 'Create Product'])
</div>
