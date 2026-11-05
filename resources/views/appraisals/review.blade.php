<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-indigo-900 leading-tight">{{ __('Review Appraisal') }} — {{ $appraisal->employee->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @php
                $initialNextYearTargets = old('next_year_targets') ?? (
                    $appraisal->nextYearTargets->isNotEmpty()
                        ? $appraisal->nextYearTargets->map(fn ($t) => [
                            'target_text' => $t->target_text,
                            'actions' => $t->actionPairs(),
                        ])->values()->all()
                        : [['target_text' => '', 'actions' => [['action_text' => '', 'success_criteria' => '']]]]
                );

                $errorKeys = collect($errors->keys());
                $errorTab = 'targets';
                if ($errorKeys->contains(fn ($k) => in_array($k, ['reviewer_comments', 'overall_rating', 'next_review_date'], true))) {
                    $errorTab = 'performance';
                }
                if ($errorKeys->contains(fn ($k) => str_starts_with($k, 'next_year_targets'))) {
                    $errorTab = 'new-targets';
                }
            @endphp

            <x-appraisal-header :appraisal="$appraisal" />

            @if ($errors->any())
                <x-notice tone="danger">
                    <p class="font-semibold mb-1">Your review couldn't be submitted — please fix the following:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-notice>
            @endif

            <form id="review-form" method="POST" action="{{ route('appraisals.review.update', $appraisal) }}" class="space-y-6" x-data="appraisalReviewForm({{ Js::from(['tab' => $errorTab, 'nextYearTargets' => $initialNextYearTargets]) }})">
                @csrf
                @method('PUT')

                <div class="flex flex-wrap items-center justify-end gap-3">
                    <button type="submit" name="intent" value="save" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                        Save Progress
                    </button>
                    <x-confirm-submit form="review-form" name="intent" value="submit"
                        label="Submit for Sign-off"
                        confirm-label="Yes, submit"
                        prompt="Move to sign-off? You won't be able to edit after this." />
                </div>

                <div class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl p-6">
                    <div class="flex items-center gap-6 border-b border-gray-200 mb-6 overflow-x-auto">
                        <button type="button" @click="tab = 'targets'" :class="tab === 'targets' ? 'border-accent-400 text-indigo-900 font-semibold' : 'border-transparent text-gray-500 hover:text-indigo-700 hover:border-indigo-200'" class="pb-3 -mb-px border-b-[3px] text-sm font-medium whitespace-nowrap">1 — Target Review</button>
                        <button type="button" @click="tab = 'performance'" :class="tab === 'performance' ? 'border-accent-400 text-indigo-900 font-semibold' : 'border-transparent text-gray-500 hover:text-indigo-700 hover:border-indigo-200'" class="pb-3 -mb-px border-b-[3px] text-sm font-medium whitespace-nowrap">2 — Performance</button>
                        <button type="button" @click="tab = 'new-targets'" :class="tab === 'new-targets' ? 'border-accent-400 text-indigo-900 font-semibold' : 'border-transparent text-gray-500 hover:text-indigo-700 hover:border-indigo-200'" class="pb-3 -mb-px border-b-[3px] text-sm font-medium whitespace-nowrap">3 — New Targets</button>
                        <button type="button" @click="tab = 'cpd'" :class="tab === 'cpd' ? 'border-accent-400 text-indigo-900 font-semibold' : 'border-transparent text-gray-500 hover:text-indigo-700 hover:border-indigo-200'" class="pb-3 -mb-px border-b-[3px] text-sm font-medium whitespace-nowrap">4 — CPD Log</button>
                    </div>

                    <div x-show="tab === 'targets'">
                        <x-section-heading :title="'SECTION 1: REVIEW OF '.$appraisal->year.' TARGETS'" hint="Employee's responses for this appraisal." />
                        <div class="space-y-4">
                            @foreach ($appraisal->currentTargets as $i => $target)
                                <input type="hidden" name="targets[{{ $i }}][id]" value="{{ $target->id }}">
                                <x-target-card :target="$target" show-met>
                                    @if ($target->comments)
                                        <x-target-voice who="Staff comments" tone="staff">{{ $target->comments }}</x-target-voice>
                                    @endif

                                    <div class="rounded-lg border-l-4 border-accent-400 bg-accent-50 px-3 py-2">
                                        <label for="reviewer_comments_{{ $i }}" class="label-caps block mb-1">Reviewer comments (optional)</label>
                                        <textarea id="reviewer_comments_{{ $i }}" name="targets[{{ $i }}][reviewer_comments]" rows="2" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm">{{ old("targets.$i.reviewer_comments", $target->reviewer_comments) }}</textarea>
                                        <x-input-error :messages="$errors->get('targets.'.$i.'.reviewer_comments')" class="mt-2" />
                                    </div>
                                </x-target-card>
                            @endforeach
                        </div>
                    </div>

                    <div x-show="tab === 'performance'" x-cloak>
                        <x-section-heading title="SECTION 2: PERFORMANCE REVIEW" />
                        <div class="space-y-4">
                            <x-panel tone="staff" title="Self-reflection" hint="Employee's reflection on their performance over the last twelve months">
                                <div class="text-[15px] text-gray-900 whitespace-pre-line">{{ $appraisal->self_reflection ?: '—' }}</div>
                            </x-panel>

                            {{-- Set when the appraisal is created (or edited by an admin); read-only for the reviewer. --}}
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                <x-fact label="Appraisal date">{{ optional($appraisal->appraisal_date)->format('d M Y') ?? '—' }}</x-fact>
                                <x-fact label="Time">{{ $appraisal->timeLabel() ?? '—' }}</x-fact>
                                <x-fact label="Venue / location">{{ $appraisal->venue ?: '—' }}</x-fact>
                            </div>

                            <x-panel edit tone="reviewer" title="Reviewer's comments and actions" hint="(to be completed by the appraiser)">
                                <textarea id="reviewer_comments" name="reviewer_comments" rows="8" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm">{{ old('reviewer_comments', $appraisal->reviewer_comments) }}</textarea>
                                <x-input-error :messages="$errors->get('reviewer_comments')" class="mt-2" />
                            </x-panel>

                            <x-panel edit title="Overall rating" hint="Choose the one that best describes this year.">
                                <div class="grid gap-2 sm:grid-cols-3">
                                    @foreach (\App\Enums\OverallRating::cases() as $rating)
                                        @php
                                            $ratingColour = match ($rating->value) {
                                                'Exceeded All Targets' => 'peer-checked:border-emerald-600 peer-checked:bg-emerald-50 peer-checked:text-emerald-900',
                                                'Met All Targets' => 'peer-checked:border-indigo-600 peer-checked:bg-indigo-50 peer-checked:text-indigo-900',
                                                default => 'peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-900',
                                            };
                                        @endphp
                                        <label class="cursor-pointer">
                                            <input type="radio" name="overall_rating" value="{{ $rating->value }}"
                                                   @checked(old('overall_rating', $appraisal->overall_rating?->value) === $rating->value)
                                                   class="peer sr-only">
                                            <span class="flex h-full items-center justify-center rounded-lg border-2 border-gray-200 bg-white px-3 py-3 text-center text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-500 peer-focus-visible:ring-offset-2 {{ $ratingColour }}">{{ $rating->value }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <x-input-error :messages="$errors->get('overall_rating')" class="mt-2" />
                            </x-panel>

                            <x-panel edit title="Next review date">
                                <x-text-input id="next_review_date" name="next_review_date" type="date" class="block w-full sm:w-64" :value="old('next_review_date', optional($appraisal->next_review_date)->format('Y-m-d'))" />
                                <x-input-error :messages="$errors->get('next_review_date')" class="mt-2" />
                            </x-panel>
                        </div>
                    </div>

                    <div x-show="tab === 'new-targets'" x-cloak>
                        <x-section-heading title="SECTION 3: NEW TARGETS" hint="Add a card for each target — set the target, then one or more actions with their success criteria." />
                        <x-input-error :messages="$errors->get('next_year_targets')" class="mb-3" />

                        <div class="space-y-4">
                            <template x-for="(t, index) in nextYearTargets" :key="t._k">
                                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                                    <div class="flex items-center justify-between border-b border-indigo-100 bg-indigo-50 px-4 py-2.5">
                                        <div class="flex items-center gap-2 text-sm font-bold text-indigo-900">
                                            <span class="grid h-6 w-6 place-items-center rounded-md bg-indigo-900 text-xs text-white" x-text="index + 1"></span>
                                            <span x-text="'Target ' + (index + 1)"></span>
                                        </div>
                                        <button type="button" :style="nextYearTargets.length > 1 ? '' : 'display:none'" x-on:click="nextYearTargets.splice(index, 1)" class="text-xs font-medium text-red-600 hover:underline">Remove target</button>
                                    </div>
                                    <div class="space-y-4 p-4">
                                        <div>
                                            <label class="label-caps mb-1 block">Target</label>
                                            <textarea :name="'next_year_targets[' + index + '][target_text]'" x-model="t.target_text" rows="2" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm" placeholder="What is the target?"></textarea>
                                        </div>

                                        <div>
                                            <div class="mb-2 flex items-center justify-between">
                                                <span class="label-caps">Actions &amp; success criteria</span>
                                                <span class="text-xs text-gray-500" x-text="nextYearTargets[index].actions.length + (nextYearTargets[index].actions.length === 1 ? ' pair' : ' pairs')"></span>
                                            </div>

                                            <div class="space-y-2.5">
                                                <template x-for="(pair, j) in nextYearTargets[index].actions" :key="pair._k">
                                                    <div class="rounded-lg border border-l-4 border-gray-200 border-l-indigo-600 bg-slate-50 p-3">
                                                        <div class="mb-2 flex items-center justify-between">
                                                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-700">
                                                                <span class="grid h-5 w-5 place-items-center rounded-full bg-indigo-600 text-[11px] text-white" x-text="j + 1"></span>
                                                                <span x-text="'Action ' + (j + 1)"></span>
                                                            </span>
                                                            <button type="button" :style="nextYearTargets[index].actions.length > 1 ? '' : 'display:none'" x-on:click="removeAction(index, j)" class="text-xs font-medium text-red-600 hover:underline">Remove</button>
                                                        </div>
                                                        <div class="grid gap-3 sm:grid-cols-2">
                                                            <div>
                                                                <label class="label-caps mb-1 block">Action to be completed</label>
                                                                <textarea :name="'next_year_targets[' + index + '][actions][' + j + '][action_text]'" x-model="pair.action_text" rows="3" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm"></textarea>
                                                            </div>
                                                            <div>
                                                                <label class="label-caps mb-1 block">Success criteria</label>
                                                                <textarea :name="'next_year_targets[' + index + '][actions][' + j + '][success_criteria]'" x-model="pair.success_criteria" rows="3" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm" placeholder="How will we know it is done?"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>

                                            <button type="button" x-on:click="addAction(index)" class="mt-2.5 w-full rounded-lg border-2 border-dashed border-indigo-200 py-2.5 text-sm font-semibold text-indigo-700 transition hover:border-indigo-300 hover:bg-indigo-50">+ Add another action &amp; success criteria</button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <button type="button" x-on:click="addTarget()" class="mt-3 inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-semibold text-indigo-700 shadow-sm transition hover:bg-indigo-50">+ Add Target</button>
                    </div>

                    <div x-show="tab === 'cpd'" x-cloak>
                        <x-section-heading title="SECTION 4: PROFESSIONAL DEVELOPMENT RECORD" hint="Professional development activities logged by the employee this year." />
                        @include('appraisals._pd-list')
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function appraisalReviewForm(initial) {
            return {
                tab: initial.tab,
                nextYearTargets: initial.nextYearTargets,
                uid: 0,
                // Unique keys (_k) let the page remove exactly the block that was clicked; they are never submitted.
                key() {
                    return ++this.uid;
                },
                init() {
                    // Every target needs at least one action / success-criteria pair to type into.
                    this.nextYearTargets.forEach(t => {
                        if (!Array.isArray(t.actions) || t.actions.length === 0) {
                            t.actions = [{ action_text: t.action_text || '', success_criteria: t.success_criteria || '' }];
                        }
                        t._k = t._k || this.key();
                        t.actions.forEach(p => { p._k = p._k || this.key(); });
                    });
                },
                addTarget() {
                    this.nextYearTargets.push({ _k: this.key(), target_text: '', actions: [{ _k: this.key(), action_text: '', success_criteria: '' }] });
                },
                addAction(index) {
                    this.nextYearTargets[index].actions.push({ _k: this.key(), action_text: '', success_criteria: '' });
                },
                removeAction(index, pairIndex) {
                    this.nextYearTargets[index].actions.splice(pairIndex, 1);
                },
            };
        }
    </script>
</x-app-layout>
