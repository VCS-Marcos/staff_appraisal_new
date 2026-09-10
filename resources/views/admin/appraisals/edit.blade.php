<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Appraisal') }}</h2>
    </x-slot>

    @php
        $initialTargets = old('targets', $appraisal->currentTargets->map(fn ($t) => [
            'id' => $t->id,
            'target_text' => $t->target_text,
            'action_text' => $t->action_text,
            'success_criteria' => $t->success_criteria,
        ])->values()->all());

        if (empty($initialTargets)) {
            $initialTargets = [['id' => '', 'target_text' => '', 'action_text' => '', 'success_criteria' => '']];
        }
    @endphp

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if ($appraisal->status !== \App\Enums\AppraisalStatus::Draft)
                <div class="p-4 bg-amber-50 border border-amber-200 text-amber-800 text-sm rounded-lg">
                    This appraisal is currently <strong>{{ $appraisal->status->label() }}</strong>. Editing it here will change a
                    record the employee and reviewer may already be working on. Any answers they have already given to a
                    target ("Target Met" and comments) are kept.
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.appraisals.update', $appraisal) }}" class="space-y-4" x-data="appraisalEditForm()">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label value="Cycle" />
                        <p class="mt-1 text-sm text-gray-800">{{ $appraisal->cycle->name }} ({{ $appraisal->cycle->term->value }})</p>
                        <p class="text-xs text-gray-500 mt-1">The cycle can't be changed. If it's wrong, delete this appraisal and create a new one.</p>
                    </div>

                    <div>
                        <x-input-label for="user_id" value="Employee being appraised" />
                        <select id="user_id" name="user_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" @selected(old('user_id', $appraisal->user_id) == $u->id)>{{ $u->name }} ({{ ucfirst($u->role->value) }})</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="reviewer_id" value="Reviewer" />
                        <select id="reviewer_id" name="reviewer_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" @selected(old('reviewer_id', $appraisal->reviewer_id) == $u->id)>{{ $u->name }} ({{ ucfirst($u->role->value) }})</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('reviewer_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="appraisal_date" value="Appraisal Date (optional)" />
                        <x-text-input id="appraisal_date" name="appraisal_date" type="date" class="block mt-1 w-full"
                                      :value="old('appraisal_date', optional($appraisal->appraisal_date)->format('Y-m-d'))" />
                        <x-input-error :messages="$errors->get('appraisal_date')" class="mt-2" />
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-1">Section 1 Targets</h3>
                        <p class="text-xs text-gray-500 mb-3">Fix wording or details here. Add or remove target cards as needed.</p>

                        <div class="space-y-3">
                            <template x-for="(target, index) in targets" :key="index">
                                <div class="border border-gray-200 rounded-md p-3">
                                    <input type="hidden" :name="'targets[' + index + '][id]'" :value="target.id">
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-sm font-medium text-gray-700" x-text="'Target ' + (index + 1)"></label>
                                        <button type="button" x-show="targets.length > 1" x-on:click="removeTarget(index)" class="text-xs text-red-600 hover:underline">Remove</button>
                                    </div>
                                    <textarea :name="'targets[' + index + '][target_text]'" x-model="target.target_text" rows="2" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Target"></textarea>

                                    <label class="block text-sm font-medium text-gray-700 mt-3">Action to be Completed</label>
                                    <textarea :name="'targets[' + index + '][action_text]'" x-model="target.action_text" rows="2" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>

                                    <label class="block text-sm font-medium text-gray-700 mt-3">Success Criteria</label>
                                    <textarea :name="'targets[' + index + '][success_criteria]'" x-model="target.success_criteria" rows="2" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                                </div>
                            </template>
                        </div>

                        <button type="button" x-on:click="addTarget()" class="mt-3 text-sm text-indigo-600 hover:underline">+ Add Target</button>
                    </div>

                    <div class="flex flex-wrap justify-end gap-3">
                        <a href="{{ route('admin.appraisals.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                            Cancel
                        </a>
                        <x-primary-button>Save Changes</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function appraisalEditForm() {
            return {
                targets: @json($initialTargets),
                addTarget() {
                    this.targets.push({ id: '', target_text: '', action_text: '', success_criteria: '' });
                },
                removeTarget(index) {
                    this.targets.splice(index, 1);
                },
            };
        }
    </script>
</x-app-layout>
