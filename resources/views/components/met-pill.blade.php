@props(['met' => null])

@php
    $map = [
        'yes' => ['Yes', 'bg-emerald-100 text-emerald-800'],
        'partially' => ['Partially', 'bg-amber-100 text-amber-900'],
        'no' => ['No', 'bg-red-100 text-red-800'],
    ];
    [$text, $classes] = $map[$met?->value] ?? ['Not answered', 'bg-gray-100 text-gray-600'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center whitespace-nowrap px-2.5 py-0.5 rounded-full text-xs font-bold $classes"]) }}>{{ $text }}</span>
