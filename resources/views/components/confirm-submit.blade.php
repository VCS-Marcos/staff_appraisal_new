@props([
    'label',
    'prompt' => 'Are you sure?',
    'confirmLabel' => 'Confirm',
    'name' => null,
    'value' => null,
    'form' => null,
])

@php
    $btnClass = 'inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700';
@endphp

<div x-data="{ open: false }" class="inline-flex">
    <button type="button" @click="open = true" class="{{ $btnClass }}">
        {{ $label }}
    </button>

    <template x-teleport="body">
        <div x-show="open" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50"
             @click.self="open = false"
             @keydown.escape.window="open = false"
             style="display: none;">
            <div x-show="open" x-transition class="bg-white rounded-lg shadow-xl max-w-sm w-full p-6">
                <h3 class="text-sm font-semibold text-gray-900">{{ $label }}?</h3>
                <p class="text-sm text-gray-500 mt-2">{{ $prompt }}</p>
                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" @click="open = false" class="px-4 py-2 text-xs font-semibold text-gray-600 uppercase tracking-widest rounded-md border border-gray-300 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                        @if ($form) form="{{ $form }}" @endif
                        @if ($name) name="{{ $name }}" @endif
                        @if ($value !== null) value="{{ $value }}" @endif
                        class="{{ $btnClass }}">
                        {{ $confirmLabel }}
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
