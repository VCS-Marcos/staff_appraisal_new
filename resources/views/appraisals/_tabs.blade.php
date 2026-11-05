<div class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl p-6" x-data="{ tab: 'targets' }">
    <div class="flex items-center gap-6 border-b border-gray-200 mb-6 overflow-x-auto">
        <button type="button" @click="tab = 'targets'" :class="tab === 'targets' ? 'border-accent-400 text-indigo-900 font-semibold' : 'border-transparent text-gray-500 hover:text-indigo-700 hover:border-indigo-200'" class="pb-3 -mb-px border-b-[3px] text-sm font-medium whitespace-nowrap">1 — Target Review</button>
        <button type="button" @click="tab = 'performance'" :class="tab === 'performance' ? 'border-accent-400 text-indigo-900 font-semibold' : 'border-transparent text-gray-500 hover:text-indigo-700 hover:border-indigo-200'" class="pb-3 -mb-px border-b-[3px] text-sm font-medium whitespace-nowrap">2 — Performance</button>
        <button type="button" @click="tab = 'new-targets'" :class="tab === 'new-targets' ? 'border-accent-400 text-indigo-900 font-semibold' : 'border-transparent text-gray-500 hover:text-indigo-700 hover:border-indigo-200'" class="pb-3 -mb-px border-b-[3px] text-sm font-medium whitespace-nowrap">3 — New Targets</button>
        <button type="button" @click="tab = 'cpd'" :class="tab === 'cpd' ? 'border-accent-400 text-indigo-900 font-semibold' : 'border-transparent text-gray-500 hover:text-indigo-700 hover:border-indigo-200'" class="pb-3 -mb-px border-b-[3px] text-sm font-medium whitespace-nowrap">4 — CPD Log</button>
    </div>


    <div x-show="tab === 'targets'">
        <x-section-heading :title="'SECTION 1: REVIEW OF '.$appraisal->year.' TARGETS'" />
        @if ($appraisal->currentTargets->count() > 1)
            <div class="-mt-2 mb-3 flex justify-end gap-3 text-xs font-semibold">
                <button type="button" class="text-indigo-700 hover:underline" x-on:click="$dispatch('target-cards', { group: 'current', open: true })">Expand all</button>
                <button type="button" class="text-indigo-700 hover:underline" x-on:click="$dispatch('target-cards', { group: 'current', open: false })">Collapse all</button>
            </div>
        @endif
        <div class="space-y-3">
            @forelse ($appraisal->currentTargets as $target)
                <x-target-card :target="$target" collapsible :open="$loop->first" show-met group="current">
                    @if ($target->comments)
                        <x-target-voice who="Staff comments" tone="staff">{{ $target->comments }}</x-target-voice>
                    @endif
                    @if ($target->reviewer_comments)
                        <x-target-voice who="Reviewer comments" tone="reviewer">{{ $target->reviewer_comments }}</x-target-voice>
                    @endif
                </x-target-card>
            @empty
                <p class="text-sm text-gray-500">No targets recorded for this appraisal.</p>
            @endforelse
        </div>
    </div>

    <div x-show="tab === 'performance'" x-cloak>
        <x-section-heading title="SECTION 2: PERFORMANCE REVIEW" />
        <div class="space-y-4">
            <x-panel title="Self-reflection" hint="Reflect on your performance over the last twelve months" tone="staff">
                <div class="text-[15px] text-gray-900 whitespace-pre-line">{{ $appraisal->self_reflection ?: '—' }}</div>
            </x-panel>

            <x-panel title="Reviewer's comments and actions" hint="(to be completed by the appraiser)" tone="reviewer">
                <div class="text-[15px] text-gray-900 whitespace-pre-line">{{ $appraisal->reviewer_comments ?: '—' }}</div>
            </x-panel>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <x-fact label="Overall rating"><x-rating-pill :rating="$appraisal->overall_rating" /></x-fact>
                <x-fact label="Next review date">{{ optional($appraisal->next_review_date)->format('d M Y') ?? '—' }}</x-fact>
            </div>
        </div>
    </div>

    <div x-show="tab === 'new-targets'" x-cloak>
        <x-section-heading title="SECTION 3: NEW TARGETS" />
        @if ($appraisal->nextYearTargets->count() > 1)
            <div class="-mt-2 mb-3 flex justify-end gap-3 text-xs font-semibold">
                <button type="button" class="text-indigo-700 hover:underline" x-on:click="$dispatch('target-cards', { group: 'next', open: true })">Expand all</button>
                <button type="button" class="text-indigo-700 hover:underline" x-on:click="$dispatch('target-cards', { group: 'next', open: false })">Collapse all</button>
            </div>
        @endif
        <div class="space-y-3">
            @forelse ($appraisal->nextYearTargets as $target)
                <x-target-card :target="$target" collapsible :open="$loop->first" group="next" />
            @empty
                <p class="text-sm text-gray-500">No new targets recorded yet.</p>
            @endforelse
        </div>
    </div>

    <div x-show="tab === 'cpd'" x-cloak>
        <x-section-heading title="SECTION 4: PROFESSIONAL DEVELOPMENT RECORD" hint="Online TES Course, Workshops, professional development sessions etc." />
        @include('appraisals._pd-list')
    </div>
</div>
