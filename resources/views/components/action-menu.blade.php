@props(['align' => 'right'])

<div x-data="{
        open: false,
        top: 0,
        left: 0,
        position() {
            const rect = $refs.trigger.getBoundingClientRect();
            this.top = rect.bottom + window.scrollY + 4;
            this.left = {{ $align === 'right' ? 'true' : 'false' }}
                ? rect.right + window.scrollX - 192
                : rect.left + window.scrollX;
        },
        toggle() {
            if (this.open) { this.open = false; return; }
            this.position();
            this.open = true;
        },
     }"
     @scroll.window="open = false"
     @resize.window="open = false"
     class="inline-block">
    <button type="button" x-ref="trigger" @click="toggle()"
        class="inline-flex items-center justify-center w-8 h-8 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-400"
        aria-haspopup="true" :aria-expanded="open">
        <span class="sr-only">Open actions</span>
        <x-icon name="dots-vertical" class="w-5 h-5" />
    </button>

    <template x-teleport="body">
        <div x-show="open" x-cloak x-transition.origin.top.right
             @click.outside="open = false" @keydown.escape.window="open = false"
             @click="open = false"
             :style="`position:absolute; top:${top}px; left:${left}px;`"
             class="z-40 w-48 rounded-md bg-white shadow-lg ring-1 ring-black/5 py-1">
            {{ $slot }}
        </div>
    </template>
</div>
