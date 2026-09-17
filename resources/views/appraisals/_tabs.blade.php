<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6" x-data="{ tab: 'targets' }">
    <div class="flex items-center gap-6 border-b border-gray-200 mb-6 overflow-x-auto">
        <button type="button" @click="tab = 'targets'" :class="tab === 'targets' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="pb-3 -mb-px border-b-2 text-sm font-medium whitespace-nowrap">1 — Target Review</button>
        <button type="button" @click="tab = 'performance'" :class="tab === 'performance' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="pb-3 -mb-px border-b-2 text-sm font-medium whitespace-nowrap">2 — Performance</button>
        <button type="button" @click="tab = 'new-targets'" :class="tab === 'new-targets' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="pb-3 -mb-px border-b-2 text-sm font-medium whitespace-nowrap">3 — New Targets</button>
        <button type="button" @click="tab = 'cpd'" :class="tab === 'cpd' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="pb-3 -mb-px border-b-2 text-sm font-medium whitespace-nowrap">4 — CPD Log</button>
    </div>

    <div x-show="tab === 'targets'">
        <h2 class="text-base font-bold text-gray-800 mb-4">SECTION 1: REVIEW OF {{ $appraisal->year }} TARGETS</h2>
        <div class="space-y-4">
            @forelse ($appraisal->currentTargets as $target)
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
                    <div class="mt-2 text-sm">
                        <span class="text-gray-500">Target Met:</span>
                        <span class="font-medium">{{ $target->target_met ? ucfirst($target->target_met->value) : 'Not yet answered' }}</span>
                    </div>
                    @if ($target->comments)
                        <div class="mt-1 text-sm"><span class="text-gray-500">Comments:</span> {{ $target->comments }}</div>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-500">No targets recorded for this appraisal.</p>
            @endforelse
        </div>
    </div>

    <div x-show="tab === 'performance'" x-cloak>
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

    <div x-show="tab === 'new-targets'" x-cloak>
        <h2 class="text-base font-bold text-gray-800 mb-4">SECTION 3: NEW TARGETS</h2>
        <div class="space-y-4">
            @forelse ($appraisal->nextYearTargets as $target)
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
            @empty
                <p class="text-sm text-gray-500">No new targets recorded yet.</p>
            @endforelse
        </div>
    </div>

    <div x-show="tab === 'cpd'" x-cloak>
        <h2 class="text-base font-bold text-gray-800 mb-1">SECTION 4: PROFESSIONAL DEVELOPMENT RECORD</h2>
        <p class="text-xs text-gray-500 mb-4">Online TES Course, Workshops, professional development sessions etc.</p>
        @if ($appraisal->professionalDevelopment->isNotEmpty())
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
        @else
            <p class="text-sm text-gray-500">No professional development activity logged yet.</p>
        @endif
    </div>
</div>
