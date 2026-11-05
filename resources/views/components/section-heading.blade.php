@props(['title', 'hint' => null])

{{-- Standard heading for each numbered section (targets, performance, new targets, CPD). --}}
<div {{ $attributes->merge(['class' => 'mb-4']) }}>
    <h2 class="text-base font-bold text-indigo-900">{{ $title }}</h2>
    @if ($hint)
        <p class="mt-0.5 text-xs text-gray-500">{{ $hint }}</p>
    @endif
</div>
