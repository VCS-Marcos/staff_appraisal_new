<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-indigo-900 leading-tight">
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

                // Unique key per CPD card (never submitted) so removing a middle card removes exactly that card.
                $initialPd = collect($initialPd)->map(fn ($p, $i) => $p + ['_k' => $i + 1])->values()->all();

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
                <x-notice tone="warning">
                    You are completing <strong>{{ $appraisal->employee->name }}'s</strong> section on their behalf
                    during an in-person session. Go through each answer together with {{ $appraisal->employee->name }}
                    before submitting.
                </x-notice>
            @endif

            @if ($errors->any())
                <x-notice tone="danger">
                    <p class="font-semibold mb-1">Your appraisal couldn't be submitted — please fix the following:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-notice>
            @endif

            <form id="appraisal-edit-form" method="POST" action="{{ route('appraisals.update', $appraisal) }}" class="space-y-6" x-data='{ tab: "{{ $errorTab }}", pd: @json($initialPd) }'>
                @csrf
                @method('PUT')

                <div class="flex flex-wrap items-center justify-end gap-3">
                    <button type="submit" name="intent" value="save" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                        Save Progress
                    </button>
                    <x-confirm-submit form="appraisal-edit-form" name="intent" value="submit"
                        :label="$submitLabel"
                        confirm-label="Yes, submit"
                        :prompt="$submitPrompt" />
                </div>

                <div class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl p-6">
                    <div class="flex items-center gap-6 border-b border-gray-200 mb-6 overflow-x-auto">
                        <button type="button" @click="tab = 'targets'" :class="tab === 'targets' ? 'border-accent-400 text-indigo-900 font-semibold' : 'border-transparent text-gray-500 hover:text-indigo-700 hover:border-indigo-200'" class="pb-3 -mb-px border-b-[3px] text-sm font-medium whitespace-nowrap">1 — Target Review</button>
                        <button type="button" @click="tab = 'performance'" :class="tab === 'performance' ? 'border-accent-400 text-indigo-900 font-semibold' : 'border-transparent text-gray-500 hover:text-indigo-700 hover:border-indigo-200'" class="pb-3 -mb-px border-b-[3px] text-sm font-medium whitespace-nowrap">2 — Performance</button>
                        <button type="button" @click="tab = 'cpd'" :class="tab === 'cpd' ? 'border-accent-400 text-indigo-900 font-semibold' : 'border-transparent text-gray-500 hover:text-indigo-700 hover:border-indigo-200'" class="pb-3 -mb-px border-b-[3px] text-sm font-medium whitespace-nowrap">4 — CPD Log</button>
                    </div>

                    <div x-show="tab === 'targets'">
                        <x-section-heading :title="'SECTION 1: REVIEW OF '.$appraisal->year.' TARGETS'" hint="For each target, indicate whether it was met and add any comments." />

                        <div class="space-y-4">
                            @foreach ($appraisal->currentTargets as $i => $target)
                                <input type="hidden" name="targets[{{ $i }}][id]" value="{{ $target->id }}">
                                <x-target-card :target="$target">
                                    <div>
                                        <div class="label-caps mb-1.5">Target met</div>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach (\App\Enums\TargetMet::cases() as $option)
                                                @php
                                                    $checkedColour = match ($option->value) {
                                                        'yes' => 'peer-checked:bg-emerald-600 peer-checked:border-emerald-600 peer-checked:text-white',
                                                        'partially' => 'peer-checked:bg-amber-400 peer-checked:border-amber-400 peer-checked:text-amber-950',
                                                        default => 'peer-checked:bg-red-600 peer-checked:border-red-600 peer-checked:text-white',
                                                    };
                                                @endphp
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="targets[{{ $i }}][target_met]" value="{{ $option->value }}"
                                                           @checked(old("targets.$i.target_met", $target->target_met?->value) === $option->value)
                                                           class="peer sr-only">
                                                    <span class="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-1.5 text-sm font-semibold capitalize text-gray-700 shadow-sm transition hover:bg-gray-50 peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-500 peer-focus-visible:ring-offset-2 {{ $checkedColour }}">{{ $option->value }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        <x-input-error :messages="$errors->get('targets.'.$i.'.target_met')" class="mt-2" />
                                    </div>

                                    <div>
                                        <label for="comments_{{ $i }}" class="label-caps block mb-1">Staff comments</label>
                                        <textarea id="comments_{{ $i }}" name="targets[{{ $i }}][comments]" rows="2" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm">{{ old("targets.$i.comments", $target->comments) }}</textarea>
                                    </div>
                                </x-target-card>
                            @endforeach
                        </div>
                    </div>

                    <div x-show="tab === 'performance'" x-cloak>
                        <x-section-heading title="SECTION 2: PERFORMANCE REVIEW" />
                        <x-panel edit tone="staff" title="Self-reflection" hint="Reflect on your performance over the last twelve months">
                            <textarea id="self_reflection" name="self_reflection" rows="10" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm">{{ old('self_reflection', $appraisal->self_reflection) }}</textarea>
                            <x-input-error :messages="$errors->get('self_reflection')" class="mt-2" />
                        </x-panel>
                    </div>

                    <div x-show="tab === 'cpd'" x-cloak>
                        <x-section-heading title="SECTION 4: PROFESSIONAL DEVELOPMENT RECORD" hint="Online TES Course, Workshops, professional development sessions etc. Add a card for each activity." />

                        <div class="space-y-4">
                            <template x-for="(item, index) in pd" :key="item._k">
                                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                                    <div class="flex items-center justify-between border-b border-indigo-100 bg-indigo-50 px-4 py-2.5">
                                        <div class="flex items-center gap-2 text-sm font-bold text-indigo-900">
                                            <span class="grid h-6 w-6 place-items-center rounded-md bg-indigo-900 text-xs text-white" x-text="index + 1"></span>
                                            <span x-text="'Activity ' + (index + 1)"></span>
                                        </div>
                                        <button type="button" x-on:click="pd.splice(index, 1)" class="text-xs font-medium text-red-600 hover:underline">Remove activity</button>
                                    </div>

                                    <div class="space-y-4 p-4">
                                        <div>
                                            <label class="label-caps mb-1 block">Professional development activity</label>
                                            <x-text-input type="text" class="block w-full" x-bind:name="'pd[' + index + '][activity_name]'" x-model="item.activity_name" />
                                        </div>

                                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                            <div>
                                                <label class="label-caps mb-1 block">Nature</label>
                                                <select x-bind:name="'pd[' + index + '][nature]'" x-model="item.nature" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm">
                                                    @foreach (\App\Enums\PdNature::cases() as $nature)
                                                        <option value="{{ $nature->value }}">{{ $nature->value }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="label-caps mb-1 block">Provider</label>
                                                <x-text-input type="text" class="block w-full" x-bind:name="'pd[' + index + '][provider]'" x-model="item.provider" />
                                            </div>
                                        </div>

                                        <div>
                                            <label class="label-caps mb-1 block">Impact on practice</label>
                                            <p class="mb-1 text-xs text-gray-500">State how the activity has impacted your performance in your job</p>
                                            <textarea x-bind:name="'pd[' + index + '][impact_on_practice]'" x-model="item.impact_on_practice" rows="2" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm"></textarea>
                                        </div>

                                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                            <div>
                                                <label class="label-caps mb-1 block">Hours</label>
                                                <x-text-input type="number" step="0.25" min="0" class="block w-full" x-bind:name="'pd[' + index + '][hours]'" x-model="item.hours" />
                                            </div>
                                            <div>
                                                <label class="label-caps mb-1 block">Date</label>
                                                <x-text-input type="date" class="block w-full" x-bind:name="'pd[' + index + '][activity_date]'" x-model="item.activity_date" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <button type="button" x-on:click="pd.push({ _k: Date.now() + Math.random(), activity_name: '', nature: 'Online', provider: '', impact_on_practice: '', hours: '', activity_date: '' })" class="mt-3 inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-semibold text-indigo-700 shadow-sm transition hover:bg-indigo-50">+ Add Activity</button>
                        <x-input-error :messages="$errors->get('pd')" class="mt-2" />
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
