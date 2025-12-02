@php
    $projectLogo = \App\Models\Setting::getValue('project_logo');
    $projectName = \App\Models\Setting::getValue('project_name', 'Vendra Invoice System');
@endphp

@if ($projectLogo)
    <img src="{{ Storage::url($projectLogo) }}" alt="{{ $projectName }}"
        {{ $attributes->merge(['class' => 'object-contain']) }}>
@else
    <x-app-logo-icon {{ $attributes }} />
@endif
