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
        /* Section title bar: brand navy with the logo's yellow as an accent. */
        h2 { font-size: 11px; margin: 0; color: #ffffff; background-color: #1a2d5a; border-left: 5px solid #f2df2f; padding: 7px 10px; letter-spacing: 0.02em; }
        p { margin: 0 0 8px; line-height: 1.4; }
        .muted { color: #6b7280; }
        .section { margin-bottom: 14px; border: 1px solid #d9e0ee; }
        .section.details { border-top: 4px solid #1d3d87; }
        .section-body { padding: 10px 12px; }
        .hint { color: #6b7280; font-size: 9px; margin: 0 0 6px; }
        table.fields { width: 100%; border-collapse: collapse; }
        table.fields td { padding: 4px 10px 4px 0; vertical-align: top; width: 50%; }
        .label { color: #6b7280; display: block; font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 2px; }

        /* Target card */
        table.tcard { width: 100%; border: 1px solid #d1d5db; border-collapse: collapse; margin-bottom: 10px; page-break-inside: avoid; }
        td.thead { background-color: #f8fafc; padding: 7px 8px; border-bottom: 1px solid #e5e7eb; }
        td.tchip { width: 58px; background-color: #1a2d5a; color: #ffffff; font-size: 8px; font-weight: bold; text-align: center; padding: 4px 6px; letter-spacing: 0.05em; white-space: nowrap; }
        td.ttitle { padding-left: 9px; font-size: 11.5px; font-weight: bold; color: #111827; vertical-align: middle; }
        td.tmet { width: 80px; text-align: right; vertical-align: middle; }
        td.tbody { padding: 6px 8px; }
        table.pairs { width: 100%; border-collapse: collapse; }
        table.pairs th { background-color: #eef3fc; color: #1b336d; font-size: 7.5px; text-transform: uppercase; letter-spacing: 0.05em; text-align: left; padding: 4px 6px; }
        table.pairs td { padding: 5px 6px; vertical-align: top; border-bottom: 1px solid #eef0f5; }
        table.pairs td.n { color: #244aa5; font-weight: bold; }
        td.tcomments { background-color: #f8fafc; border-top: 1px solid #e5e7eb; padding: 6px 8px 2px; }

        /* Whose words they are: grey strip = staff, the logo's yellow = reviewer */
        table.voice { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        table.voice td { padding: 5px 9px; }
        table.voice.staff td { background-color: #ffffff; border: 1px solid #e5e7eb; border-left: 4px solid #94a3b8; }
        table.voice.reviewer td { background-color: #fffdeb; border: 1px solid #f4eba6; border-left: 4px solid #f2df2f; }
        .strip { margin-bottom: 8px; }

        /* CPD */
        table.pd { width: 100%; border-collapse: collapse; font-size: 10px; }
        table.pd th { background-color: #eef3fc; color: #1b336d; text-align: left; text-transform: uppercase; letter-spacing: 0.05em; font-size: 7.5px; padding: 5px 6px; }
        table.pd td { padding: 5px 6px; border-bottom: 1px solid #eef0f5; vertical-align: top; }
        table.pd tr.alt td { background-color: #f8fafc; }
        table.pd tr.total td { font-weight: bold; background-color: #fffdeb; border-top: 2px solid #f2df2f; border-bottom: none; color: #1a2d5a; }

        /* Sign-off */
        table.signoff { width: 100%; border-collapse: separate; border-spacing: 0; }
        table.signoff td { width: 50%; padding: 8px 10px; }
        table.signoff td.first { border-right: 8px solid #ffffff; }
        table.signoff td.signed { background-color: #ecfdf5; border-left: 4px solid #10b981; }
        table.signoff td.unsigned { background-color: #fffdeb; border-left: 4px solid #f2df2f; }

        .footer { margin-top: 16px; padding-top: 6px; border-top: 2px solid #f2df2f; font-size: 8.5px; color: #9ca3af; text-align: center; }
        .header-bar { background-color: #1a2d5a; border-bottom: 3px solid #f2df2f; color: #ffffff; padding: 12px 14px; margin-bottom: 14px; }
        .header-bar h1 { color: #ffffff; }
        .header-bar .subtitle { color: #d1d5db; margin-bottom: 0; }
        .status-badge { display: inline-block; background-color: #f2df2f; color: #1a2d5a; font-weight: bold; padding: 2px 8px; font-size: 9px; text-transform: uppercase; letter-spacing: 0.03em; }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/school-logo.png');
        $logoData = config('app.branding') && is_file($logoPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath)) : null;
    @endphp
    <div class="header-bar">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                @if ($logoData)
                    <td style="width: 90px; background-color: #ffffff; padding: 4px; text-align: center;">
                        <img src="{{ $logoData }}" style="height: 54px;" alt="">
                    </td>
                @endif
                <td style="padding-left: 12px; vertical-align: middle;">
                    <h1>{{ config('app.pdf_title') }}</h1>
                    <p class="subtitle">{{ $appraisal->year }} Appraisal &middot; <span class="status-badge">{{ $appraisal->status->label() }}</span></p>
                </td>
            </tr>
        </table>
    </div>

    @php
        $photoData = $appraisal->employee->hasPhoto() && $appraisal->employee->photo
            ? 'data:image/jpeg;base64,'.base64_encode($appraisal->employee->photo->data)
            : null;
    @endphp
    <div class="section details">
        <div class="section-body">
            <table style="width: 100%; border-collapse: collapse;">
            <tr>
            @if ($photoData)
                <td style="width: 84px; vertical-align: top; padding-right: 12px;">
                    <img src="{{ $photoData }}" style="width: 72px; height: 72px; border: 1px solid #d1d5db;" alt="">
                </td>
            @endif
            <td style="vertical-align: top;">
            <table class="fields">
                <tr>
                    <td><span class="label">Employee Name</span>{{ $appraisal->employee->name }}</td>
                    <td><span class="label">Position</span>{{ $appraisal->employee->position ?? '—' }}</td>
                </tr>
                <tr>
                    <td><span class="label">Appraisal Date</span>{{ optional($appraisal->appraisal_date)->format('d M Y') ?? '—' }}@if ($appraisal->timeLabel()), {{ $appraisal->timeLabel() }}@endif</td>
                    <td><span class="label">Reviewer(s)</span>{{ $appraisal->reviewer->name }}</td>
                </tr>
                @if ($appraisal->venue)
                    <tr>
                        <td colspan="2"><span class="label">Venue / Location</span>{{ $appraisal->venue }}</td>
                    </tr>
                @endif
            </table>
            </td>
            </tr>
            </table>
        </div>
    </div>

    <div class="section">
        <h2>SECTION 1: REVIEW OF {{ $appraisal->year }} TARGETS</h2>
        <div class="section-body">
            @forelse ($appraisal->currentTargets as $target)
                @include('appraisals._pdf-target', ['target' => $target, 'showMet' => true])
            @empty
                <p class="muted">No targets recorded for this appraisal.</p>
            @endforelse
        </div>
    </div>

    <div class="section">
        <h2>SECTION 2: PERFORMANCE REVIEW</h2>
        <div class="section-body">
            <table class="voice staff strip" cellspacing="0" cellpadding="0">
                <tr><td>
                    <span class="label">Self-reflection</span>
                    <p class="hint">Reflect on your performance over the last twelve months</p>
                    {{ $appraisal->self_reflection ?: '—' }}
                </td></tr>
            </table>

            <table class="voice reviewer strip" cellspacing="0" cellpadding="0">
                <tr><td>
                    <span class="label">Reviewer's comments and actions</span>
                    <p class="hint">(to be completed by the appraiser)</p>
                    {{ $appraisal->reviewer_comments ?: '—' }}
                </td></tr>
            </table>

            <table class="fields" style="margin-top: 4px;">
                <tr>
                    <td><span class="label">Overall Rating</span><strong>{{ $appraisal->overall_rating?->value ?? '—' }}</strong></td>
                    <td><span class="label">Next Review Date</span><strong>{{ optional($appraisal->next_review_date)->format('d M Y') ?? '—' }}</strong></td>
                </tr>
            </table>
        </div>
    </div>

    @if ($appraisal->nextYearTargets->isNotEmpty())
        <div class="section">
            <h2>SECTION 3: NEW TARGETS</h2>
            <div class="section-body">
                @foreach ($appraisal->nextYearTargets as $target)
                    @include('appraisals._pdf-target', ['target' => $target])
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
                        <tr class="{{ $loop->even ? 'alt' : '' }}">
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
                    <td class="first {{ $appraisal->employee_signed_at ? 'signed' : 'unsigned' }}">
                        <span class="label">Employee Signature</span>
                        <strong>{{ $appraisal->employee_signed_at ? 'Signed electronically '.$appraisal->employee_signed_at->format('d M Y H:i') : 'Not yet signed' }}</strong>
                    </td>
                    <td class="{{ $appraisal->reviewer_signed_at ? 'signed' : 'unsigned' }}">
                        <span class="label">Reviewer Signature</span>
                        <strong>{{ $appraisal->reviewer_signed_at ? 'Signed electronically '.$appraisal->reviewer_signed_at->format('d M Y H:i') : 'Not yet signed' }}</strong>
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
