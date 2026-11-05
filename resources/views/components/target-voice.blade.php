@props(['who', 'tone' => 'staff'])

{{-- One person's comment on a target: staff comments get a grey strip, reviewer comments the logo's yellow. --}}
<div {{ $attributes->merge(['class' => 'rounded-lg border-l-4 px-3 py-2 '.($tone === 'reviewer' ? 'border-accent-400 bg-accent-50' : 'border-slate-400 bg-white ring-1 ring-gray-900/5')]) }}>
    <div class="label-caps">{{ $who }}</div>
    <div class="text-[15px] text-gray-900 whitespace-pre-line">{{ $slot }}</div>
</div>
