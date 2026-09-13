@props([
    'action',
    'method' => 'POST',
    'icon' => null,
    'variant' => 'default',
])

@php
    $colorClass = match ($variant) {
        'danger' => 'text-red-600 hover:bg-red-50',
        'warning' => 'text-amber-700 hover:bg-amber-50',
        'success' => 'text-emerald-700 hover:bg-emerald-50',
        default => 'text-gray-700 hover:bg-gray-50',
    };
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif
    <button type="submit" {{ $attributes->merge(['class' => "w-full flex items-center gap-2.5 px-4 py-2 text-sm text-left $colorClass"]) }}>
        @if ($icon)
            <x-icon :name="$icon" class="w-4 h-4 shrink-0" />
        @endif
        {{ $slot }}
    </button>
</form>
