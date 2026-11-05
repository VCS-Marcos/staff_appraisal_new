@props(['rating' => null])

@php
    $classes = match ($rating?->value) {
        'Exceeded All Targets' => 'bg-emerald-100 text-emerald-800',
        'Met All Targets' => 'bg-indigo-100 text-indigo-800',
        'Did Not Meet All Targets' => 'bg-red-100 text-red-800',
        default => 'bg-gray-100 text-gray-600',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold $classes"]) }}>{{ $rating?->value ?? 'Not rated yet' }}</span>
