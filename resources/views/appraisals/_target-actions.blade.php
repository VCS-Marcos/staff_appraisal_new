{{-- Read-only list of a target's action / success-criteria pairs. Expects $target (AppraisalTarget). --}}
@php($pairs = $target->actionPairs())
@if ($pairs !== [])
    <div class="mt-2 space-y-2">
        @foreach ($pairs as $n => $pair)
            <div class="pl-4 border-l-2 border-gray-100 text-sm">
                <div class="font-medium text-gray-700">Action to be Completed{{ count($pairs) > 1 ? ' '.($n + 1) : '' }}</div>
                <div class="text-gray-800 whitespace-pre-line">{{ $pair['action_text'] !== '' ? $pair['action_text'] : '—' }}</div>
                <div class="font-medium text-gray-700 mt-1">Success Criteria{{ count($pairs) > 1 ? ' '.($n + 1) : '' }}</div>
                <div class="text-gray-500 whitespace-pre-line">{{ $pair['success_criteria'] !== '' ? $pair['success_criteria'] : '—' }}</div>
            </div>
        @endforeach
    </div>
@endif
