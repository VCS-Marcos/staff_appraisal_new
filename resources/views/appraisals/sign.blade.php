<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Sign Off') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 bg-blue-50 text-blue-800 text-sm rounded-md">
                Please review the appraisal below carefully. Signing confirms this is an accurate record of the appraisal.
            </div>

            @include('appraisals._summary')

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h2 class="text-base font-bold text-gray-800 mb-4">Confirm and Sign</h2>

                <form method="POST" action="{{ route('appraisals.sign.submit', $appraisal) }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="typed_name" value="Type your full name to sign" />
                        <x-text-input id="typed_name" name="typed_name" type="text" class="block mt-1 w-full sm:w-96" :value="old('typed_name')" required autocomplete="off" />
                        <p class="text-xs text-gray-500 mt-1">Must match your account name: {{ auth()->user()->name }}</p>
                        <x-input-error :messages="$errors->get('typed_name')" class="mt-2" />
                    </div>

                    <div class="flex items-start">
                        <input id="confirm" name="confirm" type="checkbox" value="1" class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm" @checked(old('confirm'))>
                        <label for="confirm" class="ms-2 text-sm text-gray-700">I confirm the information above is an accurate record of this appraisal.</label>
                    </div>
                    <x-input-error :messages="$errors->get('confirm')" class="mt-2" />

                    <div class="flex justify-end">
                        <x-primary-button>Sign &amp; Submit</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
