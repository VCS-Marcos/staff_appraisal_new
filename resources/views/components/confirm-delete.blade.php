@props([
    'action',
    'label' => 'Delete',
    'prompt' => 'This cannot be undone.',
])

<form method="POST" action="{{ $action }}" class="inline-flex items-center" x-data="{ confirming: false }">
    @csrf
    @method('DELETE')

    <button type="button" x-show="!confirming" x-on:click="confirming = true" class="text-red-600 hover:underline">
        {{ $label }}
    </button>

    <span x-show="confirming" x-cloak class="inline-flex flex-wrap items-center gap-1.5">
        <span class="text-xs text-gray-400">{{ $prompt }}</span>
        <button type="submit" class="text-red-600 font-semibold hover:underline">Confirm</button>
        <button type="button" x-on:click="confirming = false" class="text-xs text-gray-400 hover:text-gray-600">cancel</button>
    </span>
</form>
