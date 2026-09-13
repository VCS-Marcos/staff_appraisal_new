@props([
    'action',
    'label' => 'Delete',
    'prompt' => 'This cannot be undone.',
    'icon' => 'trash',
    'menuItem' => false,
])

<div x-data="{ open: false }" class="{{ $menuItem ? '' : 'inline-block' }}">
    <button type="button" @click="open = true"
        {{ $attributes->merge(['class' => $menuItem
            ? 'w-full flex items-center gap-2.5 px-4 py-2 text-sm text-left text-red-600 hover:bg-red-50'
            : 'text-red-600 hover:underline'
        ]) }}>
        @if ($menuItem)
            <x-icon :name="$icon" class="w-4 h-4 shrink-0" />
        @endif
        {{ $label }}
    </button>

    <template x-teleport="body">
        <div x-show="open" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50"
             @click.self="open = false"
             @keydown.escape.window="open = false"
             style="display: none;">
            <div x-show="open" x-transition class="bg-white rounded-lg shadow-xl max-w-sm w-full p-6">
                <div class="flex items-start gap-3">
                    <div class="shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                        <x-icon name="trash" class="w-5 h-5 text-red-600" />
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">{{ $label }}?</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ $prompt }}</p>
                    </div>
                </div>
                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" @click="open = false" class="px-4 py-2 text-xs font-semibold text-gray-700 uppercase tracking-widest rounded-md border border-gray-300 hover:bg-gray-50">
                        Cancel
                    </button>
                    <form method="POST" action="{{ $action }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                            {{ $label }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
