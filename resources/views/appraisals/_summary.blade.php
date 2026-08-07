<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
    <h1 class="text-lg font-bold text-gray-800 mb-4">INDEPENDENT SCHOOL STAFF APPRAISAL FORM</h1>
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
        <div><dt class="text-gray-500">Employee Name</dt><dd class="font-medium text-gray-900">{{ $appraisal->employee->name }}</dd></div>
        <div><dt class="text-gray-500">Position</dt><dd class="font-medium text-gray-900">{{ $appraisal->employee->position ?? '—' }}</dd></div>
        <div><dt class="text-gray-500">Appraisal Date</dt><dd class="font-medium text-gray-900">{{ optional($appraisal->appraisal_date)->format('d M Y') ?? '—' }}</dd></div>
        <div><dt class="text-gray-500">Reviewer(s)</dt><dd class="font-medium text-gray-900">{{ $appraisal->reviewer->name }}</dd></div>
        <div><dt class="text-gray-500">Type of Review</dt><dd class="font-medium text-gray-900">{{ $appraisal->cycle->term->value }} ({{ $appraisal->cycle->name }})</dd></div>
    </dl>
</div>

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
    <h2 class="text-base font-bold text-gray-800 mb-4">SECTION 1: REVIEW OF {{ strtoupper($appraisal->cycle->name) }} TARGETS</h2>
    <div class="space-y-4">
        @forelse ($appraisal->currentTargets as $target)
            <div class="border border-gray-200 rounded-md p-4">
                <div class="text-sm font-semibold text-gray-700">Target {{ $target->target_number }}:</div>
                <p class="text-sm text-gray-800 mt-1 whitespace-pre-line">{{ $target->target_text ?: '—' }}</p>
                <div class="mt-2 text-sm">
                    <span class="text-gray-500">Target Met:</span>
                    <span class="font-medium">{{ $target->target_met ? ucfirst($target->target_met->value) : 'Not yet answered' }}</span>
                </div>
                @if ($target->comments)
                    <div class="mt-1 text-sm"><span class="text-gray-500">Comments:</span> {{ $target->comments }}</div>
                @endif
            </div>
        @empty
            <p class="text-sm text-gray-500">No targets recorded for this cycle.</p>
        @endforelse
    </div>
</div>

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
    <h2 class="text-base font-bold text-gray-800 mb-4">SECTION 2: PERFORMANCE REVIEW</h2>

    <div class="mb-4">
        <h3 class="text-sm font-semibold text-gray-700">Self-Reflection</h3>
        <p class="text-xs text-gray-500 mb-1">Reflect on your performance over the last twelve months</p>
        <p class="text-sm text-gray-800 whitespace-pre-line">{{ $appraisal->self_reflection ?: '—' }}</p>
    </div>

    <div class="mb-4">
        <h3 class="text-sm font-semibold text-gray-700">Reviewer's Comments and Actions</h3>
        <p class="text-xs text-gray-500 mb-1">(to be completed by the appraiser)</p>
        <p class="text-sm text-gray-800 whitespace-pre-line">{{ $appraisal->reviewer_comments ?: '—' }}</p>
    </div>

    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
        <div><dt class="text-gray-500">Overall Rating</dt><dd class="font-medium text-gray-900">{{ $appraisal->overall_rating?->value ?? '—' }}</dd></div>
        <div><dt class="text-gray-500">Next Review Date</dt><dd class="font-medium text-gray-900">{{ optional($appraisal->next_review_date)->format('d M Y') ?? '—' }}</dd></div>
    </dl>
</div>

@if ($appraisal->nextYearTargets->isNotEmpty())
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h2 class="text-base font-bold text-gray-800 mb-4">SECTION 3: NEW TARGETS</h2>
        <div class="space-y-4">
            @foreach ($appraisal->nextYearTargets as $target)
                <div class="border border-gray-200 rounded-md p-4">
                    <div class="text-sm font-semibold text-gray-700">Target {{ $target->target_number }}:</div>
                    <p class="text-sm text-gray-800 mt-1 whitespace-pre-line">{{ $target->target_text ?: '—' }}</p>
                    @if ($target->action_text || $target->success_criteria)
                        <div class="mt-2 pl-4 border-l-2 border-gray-100 text-sm">
                            <div class="font-medium text-gray-700">Action to be Completed</div>
                            <div class="text-gray-800">{{ $target->action_text ?: '—' }}</div>
                            <div class="font-medium text-gray-700 mt-1">Success Criteria</div>
                            <div class="text-gray-500">{{ $target->success_criteria ?: '—' }}</div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif

@if ($appraisal->professionalDevelopment->isNotEmpty())
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h2 class="text-base font-bold text-gray-800 mb-1">SECTION 4: PROFESSIONAL DEVELOPMENT RECORD</h2>
        <p class="text-xs text-gray-500 mb-4">Online TES Course, Workshops, professional development sessions etc.</p>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 uppercase">
                        <th class="py-2 pr-4">Activity</th>
                        <th class="py-2 pr-4">Nature</th>
                        <th class="py-2 pr-4">Provider</th>
                        <th class="py-2 pr-4">Impact on Practice</th>
                        <th class="py-2 pr-4">Hours</th>
                        <th class="py-2">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($appraisal->professionalDevelopment as $pd)
                        <tr>
                            <td class="py-2 pr-4 text-gray-900">{{ $pd->activity_name }}</td>
                            <td class="py-2 pr-4 text-gray-500">{{ $pd->nature->value }}</td>
                            <td class="py-2 pr-4 text-gray-500">{{ $pd->provider ?? '—' }}</td>
                            <td class="py-2 pr-4 text-gray-500">{{ $pd->impact_on_practice ?? '—' }}</td>
                            <td class="py-2 pr-4 text-gray-500">{{ number_format($pd->hours, 2) }}</td>
                            <td class="py-2 text-gray-500">{{ optional($pd->activity_date)->format('d M Y') ?? '—' }}</td>
                        </tr>
                    @endforeach
                    <tr class="font-semibold">
                        <td class="py-2 pr-4 text-gray-700" colspan="4">Total</td>
                        <td class="py-2 pr-4 text-gray-900">{{ number_format($appraisal->professionalDevelopment->sum('hours'), 2) }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endif

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
    <h2 class="text-base font-bold text-gray-800 mb-4">Sign-off</h2>
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
        <div>
            <dt class="text-gray-500">Employee Signature</dt>
            <dd class="font-medium text-gray-900">{{ $appraisal->employee_signed_at ? 'Signed '.$appraisal->employee_signed_at->format('d M Y H:i') : 'Not yet signed' }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">Reviewer Signature</dt>
            <dd class="font-medium text-gray-900">{{ $appraisal->reviewer_signed_at ? 'Signed '.$appraisal->reviewer_signed_at->format('d M Y H:i') : 'Not yet signed' }}</dd>
        </div>
    </dl>
</div>
