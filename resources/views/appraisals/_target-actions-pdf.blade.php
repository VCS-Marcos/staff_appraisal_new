{{-- PDF version of the target's action / success-criteria pairs. Expects $target (AppraisalTarget). --}}
@php($pairs = $target->actionPairs())
@foreach ($pairs as $n => $pair)
    <p><span class="muted">Action to be Completed{{ count($pairs) > 1 ? ' '.($n + 1) : '' }}:</span> {{ $pair['action_text'] !== '' ? $pair['action_text'] : '—' }}</p>
    <p><span class="muted">Success Criteria{{ count($pairs) > 1 ? ' '.($n + 1) : '' }}:</span> {{ $pair['success_criteria'] !== '' ? $pair['success_criteria'] : '—' }}</p>
@endforeach
