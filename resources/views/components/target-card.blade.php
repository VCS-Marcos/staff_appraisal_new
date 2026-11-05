@props([
    'target',
    'collapsible' => false,   // header toggles the body open/closed
    'open' => true,           // initial state when collapsible
    'showMet' => false,       // show the "Target met" pill in the header
    'group' => 'current',     // which "Expand all / Collapse all" buttons control this card
])

@php
    $pairs = $target->actionPairs();
    $count = count($pairs);
@endphp

<div x-data="{ open: {{ $open || ! $collapsible ? 'true' : 'false' }} }"
     @target-cards.window="if ($event.detail.group === '{{ $group }}') open = $event.detail.open"
     class="rounded-xl bg-white ring-1 ring-gray-900/10 shadow-sm overflow-hidden">

    {{-- Header: always visible. When collapsible it doubles as the summary row. --}}
    @if ($collapsible)
        <button type="button" @click="open = ! open" :aria-expanded="open.toString()"
                class="w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-indigo-50/60 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-inset transition">
    @else
        <div class="flex items-center gap-3 px-4 py-3">
    @endif
            <span class="shrink-0 inline-block rounded-md bg-indigo-900 px-2 py-0.5 text-[11px] font-bold uppercase tracking-wider text-white">Target {{ $target->target_number }}</span>
            <span class="min-w-0 flex-1 text-base font-semibold text-gray-900" :class="open ? 'whitespace-pre-line' : 'truncate'">{{ $target->target_text ?: '—' }}</span>
            @if ($count > 0)
                <span class="hidden sm:inline shrink-0 text-xs text-gray-500">{{ $count }} {{ $count === 1 ? 'action' : 'actions' }}</span>
            @endif
            @if ($showMet)
                <x-met-pill :met="$target->target_met" class="shrink-0" />
            @endif
            @if ($collapsible)
                <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
            @endif
    @if ($collapsible)
        </button>
    @else
        </div>
    @endif

    <div x-show="open" @if ($collapsible) x-cloak @endif>
        @if ($count > 0)
            <div class="border-t border-gray-100 px-5 pt-3 pb-1">
                <div class="label-caps mb-1">Actions &amp; success criteria</div>
                <div class="divide-y divide-gray-100">
                    @foreach ($pairs as $n => $pair)
                        <div class="grid grid-cols-1 gap-x-4 gap-y-2 py-3 sm:grid-cols-[1.75rem_1fr_1fr]">
                            <div class="hidden sm:grid h-6 w-6 place-items-center rounded-full bg-indigo-50 text-xs font-bold text-indigo-700">{{ $n + 1 }}</div>
                            <div>
                                <div class="label-caps">Action to be completed @if ($count > 1)<span class="sm:hidden">{{ $n + 1 }}</span>@endif</div>
                                <div class="text-[15px] text-gray-900 whitespace-pre-line">{{ $pair['action_text'] !== '' ? $pair['action_text'] : '—' }}</div>
                            </div>
                            <div>
                                <div class="label-caps">Success criteria</div>
                                <div class="text-[15px] text-gray-900 whitespace-pre-line">{{ $pair['success_criteria'] !== '' ? $pair['success_criteria'] : '—' }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($slot->isNotEmpty())
            <div class="space-y-3 border-t border-gray-100 bg-slate-50 px-5 py-4">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>
