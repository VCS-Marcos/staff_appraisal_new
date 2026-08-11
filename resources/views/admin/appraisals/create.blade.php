<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Open an Appraisal') }}</h2>
    </x-slot>

    @php
        $defaultTargets = old('targets', [['target_text' => '', 'action_text' => '', 'success_criteria' => '']]);
    @endphp

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.appraisals.store') }}" class="space-y-4" x-data="appraisalCreateForm()">
                    @csrf

                    <div>
                        <x-input-label for="cycle_id" value="Cycle" />
                        <select id="cycle_id" name="cycle_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                            @foreach ($cycles as $cycle)
                                <option value="{{ $cycle->id }}" @selected(old('cycle_id', $selectedCycleId) == $cycle->id)>{{ $cycle->name }} ({{ $cycle->term->value }})</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('cycle_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="user_id" value="Employee being appraised" />
                        <select id="user_id" name="user_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required
                                x-on:change="onEmployeeChange($event.target.value)">
                            <option value="">— Select —</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" data-line-manager="{{ $u->line_manager_id }}" @selected(old('user_id') == $u->id)>{{ $u->name }} ({{ ucfirst($u->role->value) }})</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="reviewer_id" value="Reviewer" />
                        <select id="reviewer_id" name="reviewer_id" x-model="reviewerId" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                            <option value="">— Select —</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" @selected(old('reviewer_id') == $u->id)>{{ $u->name }} ({{ ucfirst($u->role->value) }})</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Defaults to the employee's line manager — change if needed.</p>
                        <x-input-error :messages="$errors->get('reviewer_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="appraisal_date" value="Appraisal Date (optional)" />
                        <x-text-input id="appraisal_date" name="appraisal_date" type="date" class="block mt-1 w-full" :value="old('appraisal_date')" />
                        <x-input-error :messages="$errors->get('appraisal_date')" class="mt-2" />
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-1">Section 1 Targets</h3>
                        <p class="text-xs text-gray-500 mb-3">Carried forward from last cycle's new targets, if any. Add or remove target cards as needed.</p>

                        <div class="space-y-3">
                            <template x-for="(target, index) in targets" :key="index">
                                <div class="border border-gray-200 rounded-md p-3">
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

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('admin.appraisals.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                            Cancel
                        </a>
                        <x-primary-button>Create as Draft</x-primary-button>
                    </div>
                    <p class="text-xs text-gray-500">The appraisal is created as a draft. Use "Open for Employee" from the appraisals list when you're ready to notify them.</p>
                </form>
            </div>
        </div>
    </div>

    <script>
        function appraisalCreateForm() {
            return {
                reviewerId: '{{ old('reviewer_id') }}',
                targets: @json($defaultTargets),
                addTarget() {
                    this.targets.push({ target_text: '', action_text: '', success_criteria: '' });
                },
                removeTarget(index) {
                    this.targets.splice(index, 1);
                },
                onEmployeeChange(userId) {
                    const option = document.querySelector(`#user_id option[value="${userId}"]`);
                    const lineManagerId = option ? option.dataset.lineManager : '';
                    if (lineManagerId) {
                        this.reviewerId = lineManagerId;
                    }

                    if (!userId) return;
                    fetch(`{{ url('admin/appraisals/prior-targets') }}/${userId}`)
                        .then(r => r.json())
                        .then(data => {
                            const targets = data.targets || [];
                            const isUntouched = this.targets.length === 1
                                && !this.targets[0].target_text && !this.targets[0].action_text && !this.targets[0].success_criteria;
                            if (targets.length && isUntouched) {
                                this.targets = targets;
                            }
                        });
                },
            };
        }
    </script>
</x-app-layout>
