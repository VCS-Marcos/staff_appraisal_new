<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Send Appraisal Back') }}</h2>
    </x-slot>

    @php
        $options = [];
        if (in_array($appraisal->status, [\App\Enums\AppraisalStatus::PendingReviewer, \App\Enums\AppraisalStatus::PendingSignoff, \App\Enums\AppraisalStatus::Completed], true)) {
            $options['pending_employee'] = 'Awaiting Employee — let the employee correct their answers';
        }
        if (in_array($appraisal->status, [\App\Enums\AppraisalStatus::PendingSignoff, \App\Enums\AppraisalStatus::Completed], true)) {
            $options['pending_reviewer'] = 'Awaiting Reviewer — let the reviewer correct their comments/rating';
        }
        $hasSignature = $appraisal->employee_signed_at !== null || $appraisal->reviewer_signed_at !== null;
    @endphp

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="mb-4">
                    <div class="font-semibold text-gray-900">{{ $appraisal->employee->name }}</div>
                    <div class="text-sm text-gray-500">{{ $appraisal->cycle->name }} &middot; {{ $appraisal->cycle->term->value }} &middot; currently <strong>{{ $appraisal->status->label() }}</strong></div>
                </div>

                @if ($hasSignature)
                    <div class="p-4 bg-amber-50 border border-amber-200 text-amber-800 text-sm rounded-lg mb-4">
                        This appraisal already has a signature captured. Sending it back will <strong>clear both
                        signatures</strong> — whoever signed will need to sign again once it's finalized, since the
                        record they signed may no longer match what's in it.
                    </div>
                @endif

                @if (empty($options))
                    <p class="text-sm text-gray-500">This appraisal is at a stage that can't be sent back.</p>
                    <a href="{{ route('admin.appraisals.index') }}" class="inline-flex items-center mt-4 px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">Back</a>
                @else
                    <form id="reopen-form" method="POST" action="{{ route('admin.appraisals.reopen.submit', $appraisal) }}" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        <div>
                            <x-input-label for="target_status" value="Send back to" />
                            <select id="target_status" name="target_status" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                @foreach ($options as $value => $label)
                                    <option value="{{ $value }}" @selected(old('target_status') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('target_status')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="reason" value="Reason" />
                            <p class="text-xs text-gray-500 mb-1">Recorded in the Activity Log — e.g. "Fixing a typo in self-reflection".</p>
                            <textarea id="reason" name="reason" rows="2" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>{{ old('reason') }}</textarea>
                            <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                        </div>

                        <div class="flex justify-end gap-3">
                            <a href="{{ route('admin.appraisals.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                                Cancel
                            </a>
                            <x-confirm-submit form="reopen-form" label="Send Back" confirm-label="Yes, send it back"
                                prompt="This clears any existing signatures and notifies the employee or reviewer that it's reopened." />
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
