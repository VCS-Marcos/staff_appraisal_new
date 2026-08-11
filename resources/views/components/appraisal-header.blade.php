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
        \App\Enums\AppraisalStatus::PendingEmployee->value => 'bg-amber-100 text-amber-800',
        \App\Enums\AppraisalStatus::PendingReviewer->value => 'bg-blue-100 text-blue-800',
        \App\Enums\AppraisalStatus::PendingSignoff->value => 'bg-purple-100 text-purple-800',
        \App\Enums\AppraisalStatus::Completed->value => 'bg-green-100 text-green-800',
    ];
@endphp

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-semibold text-sm shrink-0">
            {{ strtoupper($initials) }}
        </div>
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
                    &middot; Appraisal Date: {{ $appraisal->appraisal_date->format('d M Y') }}
                @endif
            </div>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2 mt-4">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700">
            {{ $appraisal->cycle->name }} &middot; {{ $appraisal->cycle->term->value }}
        </span>

        @foreach ($stages as $i => $stage)
            <span class="text-gray-300 text-xs">&rsaquo;</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $i === $currentIndex ? $stageColors[$stage->value] : ($i < $currentIndex ? 'bg-gray-100 text-gray-400' : 'bg-gray-50 text-gray-300') }}">
                {{ $stage->label() }}
            </span>
        @endforeach
    </div>
</div>
