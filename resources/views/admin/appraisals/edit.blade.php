<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Appraisal') }}</h2>
    </x-slot>

    @php
        $initialTargets = old('targets', $appraisal->currentTargets->map(fn ($t) => [
            'id' => $t->id,
            'target_text' => $t->target_text,
            'actions' => $t->actionPairs(),
        ])->values()->all());

        if (empty($initialTargets)) {
            $initialTargets = [['id' => '', 'target_text' => '', 'actions' => [['action_text' => '', 'success_criteria' => '']]]];
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
                        <x-input-label value="Year" />
                        <p class="mt-1 text-sm text-gray-800">{{ $appraisal->year }}</p>
                        <p class="text-xs text-gray-500 mt-1">The year can't be changed. If it's wrong, delete this appraisal and create a new one.</p>
                    </div>

                    @if (auth()->user()->isAdmin())
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
                                @foreach ($reviewers as $u)
                                    <option value="{{ $u->id }}" @selected(old('reviewer_id', $appraisal->reviewer_id) == $u->id)>{{ $u->name }} ({{ ucfirst($u->role->value) }})</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('reviewer_id')" class="mt-2" />
                        </div>
                    @else
                        <div>
                            <x-input-label value="Employee being appraised" />
                            <p class="mt-1 text-sm text-gray-800">{{ $appraisal->employee->name }}</p>
                        </div>

                        <div>
                            <x-input-label value="Reviewer" />
                            <p class="mt-1 text-sm text-gray-800">{{ $appraisal->reviewer->name }} (you)</p>
                            <p class="text-xs text-gray-500 mt-1">Only an admin can change the employee or reviewer. You can update the appraisal date and the targets.</p>
                        </div>
                    @endif

                    <div>
                        <x-input-label for="appraisal_date" value="Appraisal Date" />
                        <x-text-input id="appraisal_date" name="appraisal_date" type="date" class="block mt-1 w-full"
                                      :value="old('appraisal_date', optional($appraisal->appraisal_date)->format('Y-m-d'))" required />
                        <x-input-error :messages="$errors->get('appraisal_date')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="appraisal_time" value="Appraisal Time (optional)" />
                        <x-text-input id="appraisal_time" name="appraisal_time" type="time" class="block mt-1 w-full"
                                      :value="old('appraisal_time', $appraisal->timeLabel())" />
                        <x-input-error :messages="$errors->get('appraisal_time')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="venue" value="Venue / Location (optional)" />
                        <x-text-input id="venue" name="venue" type="text" maxlength="255" class="block mt-1 w-full"
                                      :value="old('venue', $appraisal->venue)" placeholder="e.g. Principal's office" />
                        <x-input-error :messages="$errors->get('venue')" class="mt-2" />
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-1">Section 1 Targets</h3>
                        <p class="text-xs text-gray-500 mb-3">Fix wording or details here. Add or remove target cards as needed.</p>

                        <div class="space-y-3">
                            <template x-for="(target, index) in targets" :key="target._k">
                                <div class="border border-gray-200 rounded-md p-3">
                                    <input type="hidden" :name="'targets[' + index + '][id]'" :value="target.id">
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-sm font-medium text-gray-700" x-text="'Target ' + (index + 1)"></label>
                                        <button type="button" :style="targets.length > 1 ? '' : 'display:none'" x-on:click="removeTarget(index)" class="text-xs text-red-600 hover:underline">Remove</button>
                                    </div>
                                    <textarea :name="'targets[' + index + '][target_text]'" x-model="target.target_text" rows="2" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Target"></textarea>

                                    <template x-for="(pair, j) in targets[index].actions" :key="pair._k">
                                        <div class="mt-3 pl-3 border-l-2 border-indigo-100">
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-semibold text-gray-500 uppercase" x-text="'Action ' + (j + 1)"></span>
                                                <button type="button" :style="targets[index].actions.length > 1 ? '' : 'display:none'" x-on:click="removeAction(index, j)" class="text-xs text-red-600 hover:underline">Remove</button>
                                            </div>

                                            <label class="block text-sm font-medium text-gray-700 mt-1">Action to be Completed</label>
                                            <textarea :name="'targets[' + index + '][actions][' + j + '][action_text]'" x-model="pair.action_text" rows="2" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>

                                            <label class="block text-sm font-medium text-gray-700 mt-3">Success Criteria</label>
                                            <textarea :name="'targets[' + index + '][actions][' + j + '][success_criteria]'" x-model="pair.success_criteria" rows="2" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                                        </div>
                                    </template>

                                    <button type="button" x-on:click="addAction(index)" class="mt-3 text-sm text-indigo-600 hover:underline">+ Add another action &amp; success criteria</button>
                                </div>
                            </template>
                        </div>

                        <button type="button" x-on:click="addTarget()" class="mt-3 text-sm text-indigo-600 hover:underline">+ Add Target</button>
                    </div>

                    <div class="flex flex-wrap justify-end gap-3">
                        <a href="{{ auth()->user()->isAdmin() ? route('admin.appraisals.index') : route('appraisals.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
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
                uid: 0,
                init() {
                    this.normalizeTargets();
                },
                // Unique keys (_k) let the page remove exactly the block that was clicked; they are never submitted.
                key() {
                    return ++this.uid;
                },
                // Every target needs at least one action / success-criteria pair to type into.
                normalizeTargets() {
                    this.targets.forEach(t => {
                        if (!Array.isArray(t.actions) || t.actions.length === 0) {
                            t.actions = [{ action_text: t.action_text || '', success_criteria: t.success_criteria || '' }];
                        }
                        t._k = t._k || this.key();
                        t.actions.forEach(p => { p._k = p._k || this.key(); });
                    });
                },
                addTarget() {
                    this.targets.push({ _k: this.key(), id: '', target_text: '', actions: [{ _k: this.key(), action_text: '', success_criteria: '' }] });
                },
                removeTarget(index) {
                    this.targets.splice(index, 1);
                },
                addAction(index) {
                    this.targets[index].actions.push({ _k: this.key(), action_text: '', success_criteria: '' });
                },
                removeAction(index, pairIndex) {
                    this.targets[index].actions.splice(pairIndex, 1);
                },
            };
        }
    </script>
</x-app-layout>
