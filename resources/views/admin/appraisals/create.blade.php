<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-indigo-900 leading-tight">{{ __('Create an Appraisal') }}</h2>
    </x-slot>

    @php
        $defaultTargets = old('targets', [['target_text' => '', 'actions' => [['action_text' => '', 'success_criteria' => '']]]]);
    @endphp

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl p-6">
                <form method="POST" action="{{ route('admin.appraisals.store') }}" class="space-y-4" x-data="appraisalCreateForm()">
                    @csrf

                    <div>
                        <x-input-label for="year" value="Year" />
                        <select id="year" name="year" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                            @foreach ($years as $year)
                                <option value="{{ $year }}" @selected(old('year', $selectedYear) == $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('year')" class="mt-2" />
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

                    @if (! auth()->user()->isAdmin())
                        <div>
                            <x-input-label value="Reviewer" />
                            <p class="mt-1 text-sm text-gray-800">{{ auth()->user()->name }} (you)</p>
                            <p class="text-xs text-gray-500 mt-1">Appraisals you create are assigned to you as the reviewer. You can only pick staff you manage or review.</p>
                            <x-input-error :messages="$errors->get('reviewer_id')" class="mt-2" />
                        </div>
                    @else
                    <div>
                        <x-input-label for="reviewer_id" value="Reviewer" />
                        <select id="reviewer_id" name="reviewer_id" x-model="reviewerId" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                            <option value="">— Select —</option>
                            @foreach ($reviewers as $u)
                                <option value="{{ $u->id }}" @selected(old('reviewer_id') == $u->id)>{{ $u->name }} ({{ ucfirst($u->role->value) }})</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Defaults to the employee's line manager — change if needed.</p>
                        <x-input-error :messages="$errors->get('reviewer_id')" class="mt-2" />
                    </div>
                    @endif

                    <div>
                        <x-input-label for="appraisal_date" value="Appraisal Date" />
                        <x-text-input id="appraisal_date" name="appraisal_date" type="date" class="block mt-1 w-full" :value="old('appraisal_date')" required />
                        <x-input-error :messages="$errors->get('appraisal_date')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="appraisal_time" value="Appraisal Time (optional)" />
                        <x-text-input id="appraisal_time" name="appraisal_time" type="time" class="block mt-1 w-full" :value="old('appraisal_time')" />
                        <x-input-error :messages="$errors->get('appraisal_time')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="venue" value="Venue / Location (optional)" />
                        <x-text-input id="venue" name="venue" type="text" maxlength="255" class="block mt-1 w-full" :value="old('venue')" placeholder="e.g. Principal's office" />
                        <x-input-error :messages="$errors->get('venue')" class="mt-2" />
                    </div>

                    <div>
                        <h3 class="text-sm font-bold text-indigo-900 mb-1">Section 1 Targets</h3>
                        <p class="text-xs text-gray-500 mb-3">Carried forward from last year's new targets, if any. Add or remove target cards as needed.</p>

                        <div class="space-y-3">
                            <template x-for="(target, index) in targets" :key="target._k">
                                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                                    <div class="flex items-center justify-between border-b border-indigo-100 bg-indigo-50 px-4 py-2.5">
                                        <div class="flex items-center gap-2 text-sm font-bold text-indigo-900">
                                            <span class="grid h-6 w-6 place-items-center rounded-md bg-indigo-900 text-xs text-white" x-text="index + 1"></span>
                                            <span x-text="'Target ' + (index + 1)"></span>
                                        </div>
                                        <button type="button" :style="targets.length > 1 ? '' : 'display:none'" x-on:click="removeTarget(index)" class="text-xs font-medium text-red-600 hover:underline">Remove target</button>
                                    </div>
                                    <div class="space-y-4 p-4">
                                        <div>
                                            <label class="label-caps mb-1 block">Target</label>
                                            <textarea :name="'targets[' + index + '][target_text]'" x-model="target.target_text" rows="2" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm" placeholder="What is the target?"></textarea>
                                        </div>

                                        <div>
                                            <div class="mb-2 flex items-center justify-between">
                                                <span class="label-caps">Actions &amp; success criteria</span>
                                                <span class="text-xs text-gray-500" x-text="targets[index].actions.length + (targets[index].actions.length === 1 ? ' pair' : ' pairs')"></span>
                                            </div>

                                            <div class="space-y-2.5">
                                                <template x-for="(pair, j) in targets[index].actions" :key="pair._k">
                                                    <div class="rounded-lg border border-l-4 border-gray-200 border-l-indigo-600 bg-slate-50 p-3">
                                                        <div class="mb-2 flex items-center justify-between">
                                                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-700">
                                                                <span class="grid h-5 w-5 place-items-center rounded-full bg-indigo-600 text-[11px] text-white" x-text="j + 1"></span>
                                                                <span x-text="'Action ' + (j + 1)"></span>
                                                            </span>
                                                            <button type="button" :style="targets[index].actions.length > 1 ? '' : 'display:none'" x-on:click="removeAction(index, j)" class="text-xs font-medium text-red-600 hover:underline">Remove</button>
                                                        </div>
                                                        <div class="grid gap-3 sm:grid-cols-2">
                                                            <div>
                                                                <label class="label-caps mb-1 block">Action to be completed</label>
                                                                <textarea :name="'targets[' + index + '][actions][' + j + '][action_text]'" x-model="pair.action_text" rows="3" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm"></textarea>
                                                            </div>
                                                            <div>
                                                                <label class="label-caps mb-1 block">Success criteria</label>
                                                                <textarea :name="'targets[' + index + '][actions][' + j + '][success_criteria]'" x-model="pair.success_criteria" rows="3" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm" placeholder="How will we know it is done?"></textarea>
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

                    <div class="flex flex-wrap justify-end gap-3">
                        <a href="{{ auth()->user()->isAdmin() ? route('admin.appraisals.index') : route('appraisals.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" name="intent" value="draft" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                            Save as Draft
                        </button>
                        <button type="submit" name="intent" value="open" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            Save &amp; Open for Employee
                        </button>
                    </div>
                    <p class="text-xs text-gray-500">"Save as Draft" keeps the appraisal hidden from the employee until you open it from the appraisals list. "Save &amp; Open for Employee" opens it immediately and notifies them.</p>
                </form>
            </div>
        </div>
    </div>

    <script>
        function appraisalCreateForm() {
            return {
                reviewerId: '{{ old('reviewer_id') }}',
                targets: @json($defaultTargets),
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
                    this.targets.push({ _k: this.key(), target_text: '', actions: [{ _k: this.key(), action_text: '', success_criteria: '' }] });
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
                                && !this.targets[0].target_text
                                && this.targets[0].actions.every(p => !p.action_text && !p.success_criteria);
                            if (targets.length && isUntouched) {
                                this.targets = targets;
                                this.normalizeTargets();
                            }
                        });
                },
            };
        }
    </script>
</x-app-layout>
