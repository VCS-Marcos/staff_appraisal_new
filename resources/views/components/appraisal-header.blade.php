@props(['appraisal'])

@php
    $employee = $appraisal->employee;
    $initials = collect(explode(' ', trim($employee->name)))
        ->filter()
        ->map(fn ($n) => mb_substr($n, 0, 1))
        ->take(2)
        ->implode('');

    $stages = [
        \App\Enums\AppraisalStatus::Draft,
        \App\Enums\AppraisalStatus::PendingEmployee,
        \App\Enums\AppraisalStatus::PendingReviewer,
        \App\Enums\AppraisalStatus::PendingSignoff,
        \App\Enums\AppraisalStatus::Completed,
    ];
    $currentIndex = array_search($appraisal->status, $stages, true);

    $stageColors = [
        \App\Enums\AppraisalStatus::Draft->value => 'bg-gray-200 text-gray-800',
        \App\Enums\AppraisalStatus::PendingEmployee->value => 'bg-accent-100 text-amber-900 ring-1 ring-accent-300',
        \App\Enums\AppraisalStatus::PendingReviewer->value => 'bg-indigo-100 text-indigo-800',
        \App\Enums\AppraisalStatus::PendingSignoff->value => 'bg-purple-100 text-purple-800',
        \App\Enums\AppraisalStatus::Completed->value => 'bg-green-100 text-green-800',
    ];
@endphp

<div class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
<div class="h-1.5 bg-indigo-700"></div>
<div class="p-6">
    <div class="flex items-center gap-4">
        @if ($employee->hasPhoto())
            <img src="{{ $employee->photoUrl() }}" alt="{{ $employee->name }}" class="w-16 h-16 rounded-full object-cover border border-gray-200 shrink-0">
        @else
            <div class="w-16 h-16 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-semibold text-base shrink-0">
                {{ strtoupper($initials) }}
            </div>
        @endif
        <div>
            <div class="font-semibold text-gray-900">{{ $employee->name }}</div>
            <div class="text-sm text-gray-500">
                {{ $employee->position ?: ucfirst($employee->role->value) }}
                @if ($employee->lineManager)
                    &middot; LM: {{ $employee->lineManager->name }}
                @endif
            </div>
            <div class="text-xs text-gray-400 mt-0.5">
                Reviewer: {{ $appraisal->reviewer->name }}
                @if ($appraisal->appraisal_date)
                    &middot; Appraisal Date: {{ $appraisal->appraisal_date->format('d M Y') }}@if ($appraisal->timeLabel()), {{ $appraisal->timeLabel() }}@endif
                @endif
                @if ($appraisal->venue)
                    &middot; Venue: {{ $appraisal->venue }}
                @endif
            </div>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2 mt-4">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700">
            {{ $appraisal->year }} Appraisal
        </span>

        @foreach ($stages as $i => $stage)
            <span class="text-gray-400 text-xs">&rsaquo;</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $i === $currentIndex ? $stageColors[$stage->value].' font-semibold shadow-sm' : ($i < $currentIndex ? 'bg-indigo-50 text-indigo-700' : 'bg-gray-50 text-gray-400') }}">
                {{ $stage->label() }}
            </span>
        @endforeach
    </div>
</div>
</div>
