<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Appraisal — {{ $appraisal->employee->name }}</title>
    <style>
        @page { margin: 28px 32px; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1f2937; }
        h1 { font-size: 18px; margin: 0 0 2px; color: #111827; }
        .subtitle { color: #6b7280; font-size: 10px; margin: 0 0 16px; }
        h2 { font-size: 12px; margin: 0 0 10px; color: #ffffff; background-color: #374151; padding: 6px 10px; }
        h3 { font-size: 11px; margin: 10px 0 2px; color: #374151; }
        p { margin: 0 0 8px; line-height: 1.4; }
        .muted { color: #6b7280; }
        .section { margin-bottom: 14px; border: 1px solid #e5e7eb; }
        .section-body { padding: 10px 12px; }
        table.fields { width: 100%; border-collapse: collapse; }
        table.fields td { padding: 4px 10px 4px 0; vertical-align: top; width: 50%; }
        .label { color: #6b7280; display: block; font-size: 8.5px; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 1px; }
        .card { border: 1px solid #e5e7eb; background-color: #f9fafb; padding: 8px 10px; margin-bottom: 8px; }
        .card:last-child { margin-bottom: 0; }
        .card .title { font-weight: bold; margin-bottom: 3px; color: #111827; }
        table.pd { width: 100%; border-collapse: collapse; font-size: 10px; }
        table.pd th { text-align: left; color: #6b7280; text-transform: uppercase; font-size: 8.5px; padding: 4px 6px; border-bottom: 1px solid #d1d5db; }
        table.pd td { padding: 5px 6px; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
        table.pd tr.total td { font-weight: bold; border-top: 1px solid #d1d5db; border-bottom: none; }
        table.signoff { width: 100%; border-collapse: collapse; }
        table.signoff td { width: 50%; padding: 10px; border: 1px solid #e5e7eb; background-color: #f9fafb; }
        .footer { margin-top: 16px; font-size: 8.5px; color: #9ca3af; text-align: center; }
        .header-bar { background-color: #111827; color: #ffffff; padding: 12px 14px; margin-bottom: 14px; }
        .header-bar h1 { color: #ffffff; }
        .header-bar .subtitle { color: #d1d5db; margin-bottom: 0; }
        .status-badge { display: inline-block; background-color: #374151; color: #ffffff; padding: 2px 8px; font-size: 9px; text-transform: uppercase; letter-spacing: 0.03em; }
    </style>
</head>
<body>
    <div class="header-bar">
        <h1>INDEPENDENT SCHOOL STAFF APPRAISAL FORM</h1>
        <p class="subtitle">{{ $appraisal->year }} Appraisal &middot; <span class="status-badge">{{ $appraisal->status->label() }}</span></p>
    </div>

    <div class="section">
        <div class="section-body">
            <table class="fields">
                <tr>
                    <td><span class="label">Employee Name</span>{{ $appraisal->employee->name }}</td>
                    <td><span class="label">Position</span>{{ $appraisal->employee->position ?? '—' }}</td>
                </tr>
                <tr>
                    <td><span class="label">Appraisal Date</span>{{ optional($appraisal->appraisal_date)->format('d M Y') ?? '—' }}</td>
                    <td><span class="label">Reviewer(s)</span>{{ $appraisal->reviewer->name }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section">
        <h2>SECTION 1: REVIEW OF {{ $appraisal->year }} TARGETS</h2>
        <div class="section-body">
            @forelse ($appraisal->currentTargets as $target)
                <div class="card">
                    <div class="title">Target {{ $target->target_number }}:</div>
                    <p>{{ $target->target_text ?: '—' }}</p>
                    @if ($target->action_text || $target->success_criteria)
                        <p><span class="muted">Action to be Completed:</span> {{ $target->action_text ?: '—' }}</p>
                        <p><span class="muted">Success Criteria:</span> {{ $target->success_criteria ?: '—' }}</p>
                    @endif
                    <p><span class="muted">Target Met:</span> {{ $target->target_met ? ucfirst($target->target_met->value) : 'Not yet answered' }}</p>
                    @if ($target->comments)
                        <p><span class="muted">Comments:</span> {{ $target->comments }}</p>
                    @endif
                </div>
            @empty
                <p class="muted">No targets recorded for this appraisal.</p>
            @endforelse
        </div>
    </div>

    <div class="section">
        <h2>SECTION 2: PERFORMANCE REVIEW</h2>
        <div class="section-body">
            <h3>Self-Reflection</h3>
            <p class="muted">Reflect on your performance over the last twelve months</p>
            <p>{{ $appraisal->self_reflection ?: '—' }}</p>

            <h3>Reviewer's Comments and Actions</h3>
            <p class="muted">(to be completed by the appraiser)</p>
            <p>{{ $appraisal->reviewer_comments ?: '—' }}</p>

            <table class="fields">
                <tr>
                    <td><span class="label">Overall Rating</span>{{ $appraisal->overall_rating?->value ?? '—' }}</td>
                    <td><span class="label">Next Review Date</span>{{ optional($appraisal->next_review_date)->format('d M Y') ?? '—' }}</td>
                </tr>
            </table>
        </div>
    </div>

    @if ($appraisal->nextYearTargets->isNotEmpty())
        <div class="section">
            <h2>SECTION 3: NEW TARGETS</h2>
            <div class="section-body">
                @foreach ($appraisal->nextYearTargets as $target)
                    <div class="card">
                        <div class="title">Target {{ $target->target_number }}:</div>
                        <p>{{ $target->target_text ?: '—' }}</p>
                        @if ($target->action_text || $target->success_criteria)
                            <p><span class="muted">Action to be Completed:</span> {{ $target->action_text ?: '—' }}</p>
                            <p><span class="muted">Success Criteria:</span> {{ $target->success_criteria ?: '—' }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($appraisal->professionalDevelopment->isNotEmpty())
        <div class="section">
            <h2>SECTION 4: PROFESSIONAL DEVELOPMENT RECORD</h2>
            <div class="section-body">
                <table class="pd">
                    <tr>
                        <th style="width: 26%;">Activity</th>
                        <th style="width: 10%;">Nature</th>
                        <th style="width: 14%;">Provider</th>
                        <th style="width: 30%;">Impact on Practice</th>
                        <th style="width: 8%;">Hours</th>
                        <th style="width: 12%;">Date</th>
                    </tr>
                    @foreach ($appraisal->professionalDevelopment as $pd)
                        <tr>
                            <td>{{ $pd->activity_name }}</td>
                            <td>{{ $pd->nature->value }}</td>
                            <td>{{ $pd->provider ?? '—' }}</td>
                            <td>{{ $pd->impact_on_practice ?? '—' }}</td>
                            <td>{{ number_format($pd->hours, 2) }}</td>
                            <td>{{ optional($pd->activity_date)->format('d M Y') ?? '—' }}</td>
                        </tr>
                    @endforeach
                    <tr class="total">
                        <td colspan="4">Total Hours</td>
                        <td>{{ number_format($appraisal->professionalDevelopment->sum('hours'), 2) }}</td>
                        <td></td>
                    </tr>
                </table>
            </div>
        </div>
    @endif

    <div class="section">
        <h2>SIGN-OFF</h2>
        <div class="section-body">
            <table class="signoff">
                <tr>
                    <td>
                        <span class="label">Employee Signature</span>
                        {{ $appraisal->employee_signed_at ? 'Signed electronically '.$appraisal->employee_signed_at->format('d M Y H:i') : 'Not yet signed' }}
                    </td>
                    <td>
                        <span class="label">Reviewer Signature</span>
                        {{ $appraisal->reviewer_signed_at ? 'Signed electronically '.$appraisal->reviewer_signed_at->format('d M Y H:i') : 'Not yet signed' }}
                    </td>
                </tr>
            </table>
            @if ($appraisal->completion_mode === \App\Enums\CompletionMode::Assisted)
                <p class="muted" style="margin-top: 6px;">Completion Mode: Assisted (In Person) — one or more sections/signatures were captured by the reviewer or admin during an in-person session.</p>
            @endif
        </div>
    </div>

    <div class="footer">
        Generated {{ now()->format('d M Y H:i') }} &middot; {{ config('app.name') }}
    </div>
</body>
</html>
