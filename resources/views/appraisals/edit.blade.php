<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $actingOnBehalf ? __('Complete Appraisal — In Person') : __('Complete My Appraisal') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @php
                $initialPd = old('pd') ?? $appraisal->professionalDevelopment->map(fn ($p) => [
                    'activity_name' => $p->activity_name,
                    'nature' => $p->nature->value,
                    'provider' => $p->provider,
                    'impact_on_practice' => $p->impact_on_practice,
                    'hours' => (string) $p->hours,
                    'activity_date' => optional($p->activity_date)->format('Y-m-d'),
                ])->values()->all();

                $errorKeys = collect($errors->keys());
                $errorTab = 'targets';
                if ($errorKeys->contains('self_reflection')) {
                    $errorTab = 'performance';
                }
                if ($errorKeys->contains(fn ($k) => str_starts_with($k, 'pd'))) {
                    $errorTab = 'cpd';
                }

                $submitLabel = $actingOnBehalf ? 'Submit on Their Behalf' : 'Submit to Reviewer';
                $submitPrompt = $actingOnBehalf
                    ? "This confirms {$appraisal->employee->name}'s answers above are accurate and ready for the reviewer."
                    : "Once submitted you can't make further changes until the reviewer responds.";
            @endphp

            <x-appraisal-header :appraisal="$appraisal" />

            @if ($actingOnBehalf)
                <div class="p-4 bg-amber-50 border border-amber-200 text-amber-800 text-sm rounded-lg">
                    You are completing <strong>{{ $appraisal->employee->name }}'s</strong> section on their behalf
                    during an in-person session. Go through each answer together with {{ $appraisal->employee->name }}
                    before submitting.
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 text-sm">
                    <p class="font-semibold mb-1">Your appraisal couldn't be submitted — please fix the following:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('appraisals.update', $appraisal) }}" class="space-y-6" x-data='{ tab: "{{ $errorTab }}", pd: @json($initialPd) }'>
                @csrf
                @method('PUT')

                <div class="flex flex-wrap items-center justify-end gap-3">
                    <a href="{{ route('appraisals.pdf', $appraisal) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                        <x-icon name="download" class="w-3.5 h-3.5" /> PDF
                    </a>
                    <button type="submit" name="intent" value="save" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                        Save Progress
                    </button>
                    <x-confirm-submit name="intent" value="submit"
                        :label="$submitLabel"
                        confirm-label="Yes, submit"
                        :prompt="$submitPrompt" />
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center gap-6 border-b border-gray-200 mb-6 overflow-x-auto">
                        <button type="button" @click="tab = 'targets'" :class="tab === 'targets' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="pb-3 -mb-px border-b-2 text-sm font-medium whitespace-nowrap">1 — Target Review</button>
                        <button type="button" @click="tab = 'performance'" :class="tab === 'performance' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="pb-3 -mb-px border-b-2 text-sm font-medium whitespace-nowrap">2 — Performance</button>
                        <button type="button" @click="tab = 'cpd'" :class="tab === 'cpd' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="pb-3 -mb-px border-b-2 text-sm font-medium whitespace-nowrap">4 — CPD Log</button>
                    </div>

                    <div x-show="tab === 'targets'">
                        <p class="text-xs text-gray-500 mb-4">For each target, indicate whether it was met and add any comments.</p>

                        <div class="space-y-6">
                            @foreach ($appraisal->currentTargets as $i => $target)
                                <div class="border border-gray-200 rounded-md p-4">
                                    <input type="hidden" name="targets[{{ $i }}][id]" value="{{ $target->id }}">
                                    <div class="text-sm font-semibold text-gray-700 mb-2">Target {{ $target->target_number }}:</div>
                                    <p class="text-sm text-gray-800 mb-3 whitespace-pre-line">{{ $target->target_text ?: '(No target text recorded)' }}</p>
                                    @if ($target->action_text || $target->success_criteria)
                                        <div class="mb-3 pl-4 border-l-2 border-gray-100 text-sm">
                                            <div class="font-medium text-gray-700">Action to be Completed</div>
                                            <div class="text-gray-800">{{ $target->action_text ?: '—' }}</div>
                                            <div class="font-medium text-gray-700 mt-1">Success Criteria</div>
                                            <div class="text-gray-500">{{ $target->success_criteria ?: '—' }}</div>
                                        </div>
                                    @endif

                                    <x-input-label value="Target Met" />
                                    <div class="flex gap-4 mt-1">
                                        @foreach (\App\Enums\TargetMet::cases() as $option)
                                            <label class="inline-flex items-center text-sm">
                                                <input type="radio" name="targets[{{ $i }}][target_met]" value="{{ $option->value }}"
                                                       @checked(old("targets.$i.target_met", $target->target_met?->value) === $option->value)
                                                       class="text-indigo-600 focus:ring-indigo-500">
                                                <span class="ms-2 capitalize">{{ $option->value }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <x-input-error :messages="$errors->get(\"targets.$i.target_met\")" class="mt-2" />

                                    <div class="mt-3">
                                        <x-input-label :for="'comments_'.$i" value="Comments" />
                                        <textarea id="comments_{{ $i }}" name="targets[{{ $i }}][comments]" rows="2" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old("targets.$i.comments", $target->comments) }}</textarea>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div x-show="tab === 'performance'" x-cloak>
                        <x-input-label for="self_reflection" value="Self-Reflection" />
                        <p class="text-xs text-gray-500 mb-1">Reflect on your performance over the last twelve months</p>
                        <textarea id="self_reflection" name="self_reflection" rows="10" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('self_reflection', $appraisal->self_reflection) }}</textarea>
                        <x-input-error :messages="$errors->get('self_reflection')" class="mt-2" />
                    </div>

                    <div x-show="tab === 'cpd'" x-cloak>
                        <p class="text-xs text-gray-500 mb-4">Online TES Course, Workshops, professional development sessions etc. Add a card for each activity.</p>

                        <div class="space-y-4">
                            <template x-for="(item, index) in pd" :key="index">
                                <div class="border border-gray-200 rounded-md p-4">
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-sm font-medium text-gray-700" x-text="'Activity ' + (index + 1)"></label>
                                        <button type="button" x-on:click="pd.splice(index, 1)" class="text-xs text-red-600 hover:underline">Remove</button>
                                    </div>

                                    <x-input-label value="Professional Development Activity" />
                                    <x-text-input type="text" class="block mt-1 w-full" x-bind:name="'pd[' + index + '][activity_name]'" x-model="item.activity_name" />

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
                                        <div>
                                            <x-input-label value="Nature" />
                                            <select x-bind:name="'pd[' + index + '][nature]'" x-model="item.nature" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                @foreach (\App\Enums\PdNature::cases() as $nature)
                                                    <option value="{{ $nature->value }}">{{ $nature->value }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <x-input-label value="Provider" />
                                            <x-text-input type="text" class="block mt-1 w-full" x-bind:name="'pd[' + index + '][provider]'" x-model="item.provider" />
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <x-input-label value="Impact on Practice" />
                                        <p class="text-xs text-gray-500 mb-1">State how the activity has impacted your performance in your job</p>
                                        <textarea x-bind:name="'pd[' + index + '][impact_on_practice]'" x-model="item.impact_on_practice" rows="2" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
                                        <div>
                                            <x-input-label value="Hours" />
                                            <x-text-input type="number" step="0.25" min="0" class="block mt-1 w-full" x-bind:name="'pd[' + index + '][hours]'" x-model="item.hours" />
                                        </div>
                                        <div>
                                            <x-input-label value="Date" />
                                            <x-text-input type="date" class="block mt-1 w-full" x-bind:name="'pd[' + index + '][activity_date]'" x-model="item.activity_date" />
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <button type="button" x-on:click="pd.push({ activity_name: '', nature: 'Online', provider: '', impact_on_practice: '', hours: '', activity_date: '' })" class="mt-3 text-sm text-indigo-600 hover:underline">+ Add Activity</button>
                        <x-input-error :messages="$errors->get('pd')" class="mt-2" />
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
