@props(['label'])

{{-- A small labelled value tile (date, venue, rating ...). --}}
<div {{ $attributes->merge(['class' => 'rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-gray-900/10']) }}>
    <div class="label-caps">{{ $label }}</div>
    <div class="mt-0.5 text-[15px] font-semibold text-gray-900">{{ $slot }}</div>
</div>
