@props([
    'title' => null,
    'hint' => null,
    'tone' => 'brand',   // brand (blue) | staff (grey) | reviewer (the logo's yellow)
    'edit' => false,     // tinted header = a place where the user types
])

@php
    $bar = match ($tone) {
        'staff' => 'border-l-slate-400 bg-white',
        'reviewer' => 'border-l-accent-400 bg-accent-50',
        default => 'border-l-indigo-600 bg-white',
    };
@endphp

{{-- One block of content in a section. Same card look as the target cards; the left bar says whose words they are. --}}
<div {{ $attributes->merge(['class' => "overflow-hidden rounded-xl border-l-4 shadow-sm ring-1 ring-gray-900/10 $bar"]) }}>
    @if ($title)
        <div class="border-b px-4 py-3 {{ $edit ? 'border-indigo-100 bg-indigo-50' : 'border-gray-100' }}">
            <div class="text-sm font-bold text-indigo-900">{{ $title }}</div>
            @if ($hint)
                <div class="mt-0.5 text-xs text-gray-500">{{ $hint }}</div>
            @endif
        </div>
    @endif
    <div class="p-4">
        {{ $slot }}
    </div>
</div>
