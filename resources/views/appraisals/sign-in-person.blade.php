<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Complete Sign-off (In Person)') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 bg-amber-50 border border-amber-200 text-amber-800 text-sm rounded-lg">
                Use this screen only when <strong>{{ $appraisal->employee->name }}</strong> is physically present with you.
                They should type their own name below as their signature — this is recorded as captured in person by
                you, not as {{ $appraisal->employee->name }} having logged in themselves.
            </div>

            <x-appraisal-header :appraisal="$appraisal" />

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 text-sm">
                    <p class="font-semibold mb-1">Please fix the following:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @include('appraisals._tabs')
            @include('appraisals._signoff')

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h2 class="text-base font-bold text-gray-800 mb-4">Capture Signatures In Person</h2>

                <form method="POST" action="{{ route('appraisals.sign-in-person.submit', $appraisal) }}" class="space-y-4">
                    @csrf

                    @if ($appraisal->employee_signed_at === null)
                        <div>
                            <x-input-label for="employee_typed_name" :value="$appraisal->employee->name.' — type their full name to sign'" />
                            <x-text-input id="employee_typed_name" name="employee_typed_name" type="text" class="block mt-1 w-full sm:w-96" :value="old('employee_typed_name')" required autocomplete="off" />
                            <p class="text-xs text-gray-500 mt-1">Hand the keyboard to {{ $appraisal->employee->name }} — must match their account name exactly: {{ $appraisal->employee->name }}</p>
                            <x-input-error :messages="$errors->get('employee_typed_name')" class="mt-2" />
                        </div>
                    @else
                        <div class="text-sm text-gray-500">Employee already signed on {{ $appraisal->employee_signed_at->format('d M Y H:i') }}.</div>
                    @endif

                    @if ($appraisal->reviewer_signed_at === null)
                        <div>
                            <x-input-label for="reviewer_typed_name" value="Your full name (reviewer/admin conducting this session)" />
                            <x-text-input id="reviewer_typed_name" name="reviewer_typed_name" type="text" class="block mt-1 w-full sm:w-96" :value="old('reviewer_typed_name')" required autocomplete="off" />
                            <p class="text-xs text-gray-500 mt-1">Must match your own account name: {{ auth()->user()->name }}</p>
                            <x-input-error :messages="$errors->get('reviewer_typed_name')" class="mt-2" />
                        </div>
                    @else
                        <div class="text-sm text-gray-500">Reviewer already signed on {{ $appraisal->reviewer_signed_at->format('d M Y H:i') }}.</div>
                    @endif

                    <div class="flex items-start">
                        <input id="confirm_in_person" name="confirm_in_person" type="checkbox" value="1" class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm" @checked(old('confirm_in_person'))>
                        <label for="confirm_in_person" class="ms-2 text-sm text-gray-700">I confirm this is a one-on-one, in-person session and the signature(s) above were typed by the person(s) named, in my presence.</label>
                    </div>
                    <x-input-error :messages="$errors->get('confirm_in_person')" class="mt-2" />

                    <div class="flex justify-end">
                        <x-confirm-submit label="Complete Sign-off"
                            confirm-label="Yes, complete sign-off"
                            prompt="This finalizes the signature(s) captured in person and cannot be undone." />
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
