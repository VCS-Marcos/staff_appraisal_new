{{-- PDF version of a target card (title bar, numbered action/criteria table, comment strips). Expects $target; optional $showMet. --}}
@php
    $pairs = $target->actionPairs();
    $metStyles = [
        'yes' => ['Yes', '#d1fae5', '#065f46'],
        'partially' => ['Partially', '#fef3c7', '#78350f'],
        'no' => ['No', '#fee2e2', '#991b1b'],
    ];
    [$metText, $metBg, $metFg] = $metStyles[$target->target_met?->value] ?? ['Not answered', '#f3f4f6', '#4b5563'];
@endphp
<table class="tcard" cellspacing="0" cellpadding="0">
    <tr>
        <td class="thead">
            <table cellspacing="0" cellpadding="0" style="width: 100%;">
                <tr>
                    <td class="tchip">TARGET {{ $target->target_number }}</td>
                    <td class="ttitle">{{ $target->target_text ?: '—' }}</td>
                    @if (! empty($showMet))
                        <td class="tmet"><span style="background-color: {{ $metBg }}; color: {{ $metFg }}; font-weight: bold; padding: 2px 8px; font-size: 9px;">{{ $metText }}</span></td>
                    @endif
                </tr>
            </table>
        </td>
    </tr>
    @if ($pairs !== [])
        <tr>
            <td class="tbody">
                <table class="pairs" cellspacing="0" cellpadding="0">
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 47%;">Action to be completed</th>
                        <th style="width: 48%;">Success criteria</th>
                    </tr>
                    @foreach ($pairs as $n => $pair)
                        <tr>
                            <td class="n">{{ $n + 1 }}</td>
                            <td>{{ $pair['action_text'] !== '' ? $pair['action_text'] : '—' }}</td>
                            <td>{{ $pair['success_criteria'] !== '' ? $pair['success_criteria'] : '—' }}</td>
                        </tr>
                    @endforeach
                </table>
            </td>
        </tr>
    @endif
    @if (! empty($showMet) && ($target->comments || $target->reviewer_comments))
        <tr>
            <td class="tbody tcomments">
                @if ($target->comments)
                    <table class="voice staff" cellspacing="0" cellpadding="0"><tr><td><span class="label">Staff comments</span>{{ $target->comments }}</td></tr></table>
                @endif
                @if ($target->reviewer_comments)
                    <table class="voice reviewer" cellspacing="0" cellpadding="0"><tr><td><span class="label">Reviewer comments</span>{{ $target->reviewer_comments }}</td></tr></table>
                @endif
            </td>
        </tr>
    @endif
</table>
