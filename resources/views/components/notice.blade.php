@props(['tone' => 'info'])

@php
    $classes = match ($tone) {
        'warning' => 'border-accent-400 bg-accent-50 text-amber-900',
        'danger' => 'border-red-500 bg-red-50 text-red-900',
        'success' => 'border-emerald-500 bg-emerald-50 text-emerald-900',
        default => 'border-indigo-500 bg-indigo-50 text-indigo-900',
    };
@endphp

<div {{ $attributes->merge(['class' => "rounded-xl border-l-4 px-4 py-3 text-sm $classes", 'role' => 'alert']) }}>
    {{ $slot }}
</div>
