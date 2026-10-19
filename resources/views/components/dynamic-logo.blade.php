@php
    $projectLogo = \App\Models\Setting::getValue('project_logo');
    $projectName = \App\Models\Setting::getValue('project_name', 'Vendra Invoice System');
    $fallbackLogo = asset('images/logos/pedabo_logo.png');
@endphp

<img src="{{ $projectLogo ? Storage::url($projectLogo) : $fallbackLogo }}" alt="{{ $projectName }}"
    onerror="this.onerror=null;this.src='{{ $fallbackLogo }}';" {{ $attributes->merge(['class' => 'object-contain']) }}>
