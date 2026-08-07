<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Review Appraisal') }} — {{ $appraisal->employee->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h2 class="text-base font-bold text-gray-800 mb-4">SECTION 1: REVIEW OF {{ strtoupper($appraisal->cycle->name) }} TARGETS <span class="text-xs font-normal text-gray-500">(employee's responses)</span></h2>
                <div class="space-y-4">
                    @foreach ($appraisal->currentTargets as $target)
                        <div class="border border-gray-200 rounded-md p-4">
                            <div class="text-sm font-semibold text-gray-700">Target {{ $target->target_number }}:</div>
                            <p class="text-sm text-gray-800 mt-1 whitespace-pre-line">{{ $target->target_text ?: '—' }}</p>
                            <div class="mt-2 text-sm"><span class="text-gray-500">Target Met:</span> <span class="font-medium">{{ $target->target_met ? ucfirst($target->target_met->value) : '—' }}</span></div>
                            @if ($target->comments)
                                <div class="mt-1 text-sm"><span class="text-gray-500">Employee Comments:</span> {{ $target->comments }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700">Self-Reflection</h3>
                <p class="text-xs text-gray-500 mb-1">Employee's reflection on their performance over the last twelve months</p>
                <p class="text-sm text-gray-800 whitespace-pre-line">{{ $appraisal->self_reflection ?: '—' }}</p>
            </div>

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
            @endphp

            <form method="POST" action="{{ route('appraisals.review.update', $appraisal) }}" class="space-y-6" x-data='{ nextYearTargets: @json($initialNextYearTargets) }'>
                @csrf
                @method('PUT')

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h2 class="text-base font-bold text-gray-800 mb-4">SECTION 2: PERFORMANCE REVIEW <span class="text-xs font-normal text-gray-500">(to be completed by the appraiser)</span></h2>

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

                @if ($appraisal->cycle->term === \App\Enums\CycleTerm::EndOfYear)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-base font-bold text-gray-800 mb-1">SECTION 3: NEW TARGETS FOR {{ \Illuminate\Support\Carbon::parse($appraisal->cycle->end_date)->year }}</h2>
                        <p class="text-xs text-gray-500 mb-4">For end-of-year review only. Add a card for each target — set the target, the action to be completed, and the success criteria.</p>
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
                @endif

                <div class="flex justify-end gap-3">
                    <button type="submit" name="intent" value="save" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                        Save Progress
                    </button>
                    <button type="submit" name="intent" value="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                            onclick="return confirm('Submit this review? The appraisal will move to sign-off.');">
                        Submit for Sign-off
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
