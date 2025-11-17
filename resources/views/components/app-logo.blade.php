<div class="bg-white w-fit py-2 px-3">
    @if (auth()->check() && auth()->user()->logo_path)
        <img src="{{ Storage::url(auth()->user()->logo_path) }}" alt="{{ auth()->user()->name }}"
            class="max-h-12 object-contain">
    @else
        <img src="{{ asset('images/logos/pedabo_logo.png') }}" alt="Pedabo">
    @endif
</div>
