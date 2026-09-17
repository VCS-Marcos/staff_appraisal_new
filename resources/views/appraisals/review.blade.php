<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Review Appraisal') }} — {{ $appraisal->employee->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @php
                $initialNextYearTargets = old('next_year_targets') ?? (
                    $appraisal->nextYearTargets->isNotEmpty()
                        ? $appraisal->nextYearTargets->map(fn ($t) => [
                            'target_text' => $t->target_text,
                            'action_text' => $t->action_text,
                            'success_criteria' => $t->success_criteria,
                        ])->values()->all()
                        : [['target_text' => '', 'action_text' => '', 'success_criteria' => '']]
                );

                $errorKeys = collect($errors->keys());
                $errorTab = 'targets';
                if ($errorKeys->contains(fn ($k) => in_array($k, ['reviewer_comments', 'overall_rating', 'appraisal_date', 'next_review_date'], true))) {
                    $errorTab = 'performance';
                }
                if ($errorKeys->contains(fn ($k) => str_starts_with($k, 'next_year_targets'))) {
                    $errorTab = 'new-targets';
                }
            @endphp

            <x-appraisal-header :appraisal="$appraisal" />

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 text-sm">
                    <p class="font-semibold mb-1">Your review couldn't be submitted — please fix the following:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="review-form" method="POST" action="{{ route('appraisals.review.update', $appraisal) }}" class="space-y-6" x-data='{ tab: "{{ $errorTab }}", nextYearTargets: @json($initialNextYearTargets) }'>
                @csrf
                @method('PUT')

                <div class="flex flex-wrap items-center justify-end gap-3">
                    <a href="{{ route('appraisals.pdf', $appraisal) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                        <x-icon name="download" class="w-3.5 h-3.5" /> PDF
                    </a>
                    <button type="submit" name="intent" value="save" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                        Save Progress
                    </button>
                    <x-confirm-submit form="review-form" name="intent" value="submit"
                        label="Submit for Sign-off"
                        confirm-label="Yes, submit"
                        prompt="Move to sign-off? You won't be able to edit after this." />
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center gap-6 border-b border-gray-200 mb-6 overflow-x-auto">
                        <button type="button" @click="tab = 'targets'" :class="tab === 'targets' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="pb-3 -mb-px border-b-2 text-sm font-medium whitespace-nowrap">1 — Target Review</button>
                        <button type="button" @click="tab = 'performance'" :class="tab === 'performance' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="pb-3 -mb-px border-b-2 text-sm font-medium whitespace-nowrap">2 — Performance</button>
                        <button type="button" @click="tab = 'new-targets'" :class="tab === 'new-targets' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="pb-3 -mb-px border-b-2 text-sm font-medium whitespace-nowrap">3 — New Targets</button>
                        <button type="button" @click="tab = 'cpd'" :class="tab === 'cpd' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="pb-3 -mb-px border-b-2 text-sm font-medium whitespace-nowrap">4 — CPD Log</button>
                    </div>

                    <div x-show="tab === 'targets'">
                        <p class="text-xs text-gray-500 mb-4">Employee's responses for this appraisal.</p>
                        <div class="space-y-4">
                            @foreach ($appraisal->currentTargets as $target)
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
                                    <div class="mt-2 text-sm"><span class="text-gray-500">Target Met:</span> <span class="font-medium">{{ $target->target_met ? ucfirst($target->target_met->value) : '—' }}</span></div>
                                    @if ($target->comments)
                                        <div class="mt-1 text-sm"><span class="text-gray-500">Employee Comments:</span> {{ $target->comments }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div x-show="tab === 'performance'" x-cloak>
                        <div class="mb-6">
                            <h3 class="text-sm font-semibold text-gray-700">Self-Reflection</h3>
                            <p class="text-xs text-gray-500 mb-1">Employee's reflection on their performance over the last twelve months</p>
                            <p class="text-sm text-gray-800 whitespace-pre-line">{{ $appraisal->self_reflection ?: '—' }}</p>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="appraisal_date" value="Appraisal Date" />
                            <x-text-input id="appraisal_date" name="appraisal_date" type="date" class="block mt-1 w-full sm:w-64" :value="old('appraisal_date', optional($appraisal->appraisal_date)->format('Y-m-d'))" />
                            <p class="text-xs text-gray-500 mt-1">The date this appraisal meeting took place.</p>
                            <x-input-error :messages="$errors->get('appraisal_date')" class="mt-2" />
                        </div>

                        <x-input-label for="reviewer_comments" value="Reviewer's Comments and Actions" />
                        <textarea id="reviewer_comments" name="reviewer_comments" rows="8" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('reviewer_comments', $appraisal->reviewer_comments) }}</textarea>
                        <x-input-error :messages="$errors->get('reviewer_comments')" class="mt-2" />

                        <div class="mt-4">
                            <x-input-label value="Overall Rating" />
                            <div class="flex flex-col gap-2 mt-1">
                                @foreach (\App\Enums\OverallRating::cases() as $rating)
                                    <label class="inline-flex items-center text-sm">
                                        <input type="radio" name="overall_rating" value="{{ $rating->value }}"
                                               @checked(old('overall_rating', $appraisal->overall_rating?->value) === $rating->value)
                                               class="text-indigo-600 focus:ring-indigo-500">
                                        <span class="ms-2">{{ $rating->value }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors->get('overall_rating')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="next_review_date" value="Next Review Date" />
                            <x-text-input id="next_review_date" name="next_review_date" type="date" class="block mt-1 w-full sm:w-64" :value="old('next_review_date', optional($appraisal->next_review_date)->format('Y-m-d'))" />
                            <x-input-error :messages="$errors->get('next_review_date')" class="mt-2" />
                        </div>
                    </div>

                    <div x-show="tab === 'new-targets'" x-cloak>
                        <p class="text-xs text-gray-500 mb-4">Add a card for each target — set the target, the action to be completed, and the success criteria.</p>
                        <x-input-error :messages="$errors->get('next_year_targets')" class="mb-3" />

                        <div class="space-y-4">
                            <template x-for="(t, index) in nextYearTargets" :key="index">
                                <div class="border border-gray-200 rounded-md p-4">
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-sm font-medium text-gray-700" x-text="'Target ' + (index + 1)"></label>
                                        <button type="button" x-show="nextYearTargets.length > 1" x-on:click="nextYearTargets.splice(index, 1)" class="text-xs text-red-600 hover:underline">Remove</button>
                                    </div>
                                    <textarea :name="'next_year_targets[' + index + '][target_text]'" x-model="t.target_text" rows="2" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Target"></textarea>

                                    <label class="block text-sm font-medium text-gray-700 mt-3">Action to be Completed</label>
                                    <textarea :name="'next_year_targets[' + index + '][action_text]'" x-model="t.action_text" rows="2" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>

                                    <label class="block text-sm font-medium text-gray-700 mt-3">Success Criteria</label>
                                    <textarea :name="'next_year_targets[' + index + '][success_criteria]'" x-model="t.success_criteria" rows="2" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                                </div>
                            </template>
                        </div>

                        <button type="button" x-on:click="nextYearTargets.push({ target_text: '', action_text: '', success_criteria: '' })" class="mt-3 text-sm text-indigo-600 hover:underline">+ Add Target</button>
                    </div>

                    <div x-show="tab === 'cpd'" x-cloak>
                        <p class="text-xs text-gray-500 mb-4">Professional development activities logged by the employee this year.</p>
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
            </form>
        </div>
    </div>
</x-app-layout>
