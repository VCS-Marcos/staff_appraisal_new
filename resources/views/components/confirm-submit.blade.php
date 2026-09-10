@props([
    'label',
    'prompt' => 'Are you sure?',
    'confirmLabel' => 'Confirm',
    'name' => null,
    'value' => null,
])

@php
    $btnClass = 'inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700';
@endphp

<span x-data="{ confirming: false }" class="inline-flex items-center gap-2">
    <button type="button" x-show="!confirming" x-on:click="confirming = true" class="{{ $btnClass }}">
        {{ $label }}
    </button>

    <span x-show="confirming" x-cloak class="inline-flex flex-wrap items-center gap-2">
        <span class="text-xs text-gray-500">{{ $prompt }}</span>
        <button type="submit" @if ($name) name="{{ $name }}" @endif @if ($value !== null) value="{{ $value }}" @endif class="{{ $btnClass }}">
            {{ $confirmLabel }}
        </button>
        <button type="button" x-on:click="confirming = false" class="px-2 py-2 text-xs font-medium text-gray-500 hover:text-gray-700">
            Cancel
        </button>
    </span>
</span>
