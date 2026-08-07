@props(['status'])

@php
    $colors = match ($status) {
        \App\Enums\AppraisalStatus::Draft => 'bg-gray-100 text-gray-700',
        \App\Enums\AppraisalStatus::PendingEmployee => 'bg-amber-100 text-amber-800',
        \App\Enums\AppraisalStatus::PendingReviewer => 'bg-blue-100 text-blue-800',
        \App\Enums\AppraisalStatus::PendingSignoff => 'bg-purple-100 text-purple-800',
        \App\Enums\AppraisalStatus::Completed => 'bg-green-100 text-green-800',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium $colors"]) }}>
    {{ $status->label() }}
</span>
