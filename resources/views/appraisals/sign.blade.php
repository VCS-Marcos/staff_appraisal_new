<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-indigo-900 leading-tight">{{ __('Sign Off') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-notice>
                Please review the appraisal below carefully. Signing confirms this is an accurate record of the appraisal.
            </x-notice>

            <x-appraisal-header :appraisal="$appraisal" />

            @include('appraisals._tabs')
            @include('appraisals._signoff')

            <x-panel edit title="Confirm and sign" hint="Your typed name works as your signature.">
                <form method="POST" action="{{ route('appraisals.sign.submit', $appraisal) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="typed_name" class="label-caps mb-1 block">Type your full name to sign</label>
                        <x-text-input id="typed_name" name="typed_name" type="text" class="block w-full sm:w-96" :value="old('typed_name')" required autocomplete="off" />
                        <p class="text-xs text-gray-500 mt-1">Must match your account name: {{ auth()->user()->name }}</p>
                        <x-input-error :messages="$errors->get('typed_name')" class="mt-2" />
                    </div>

                    <label for="confirm" class="flex cursor-pointer items-start gap-2 rounded-lg bg-slate-50 p-3 ring-1 ring-gray-900/5">
                        <input id="confirm" name="confirm" type="checkbox" value="1" class="mt-0.5 rounded border-gray-300 text-indigo-600 shadow-sm" @checked(old('confirm'))>
                        <span class="text-sm text-gray-800">I confirm the information above is an accurate record of this appraisal.</span>
                    </label>
                    <x-input-error :messages="$errors->get('confirm')" class="mt-2" />

                    <div class="flex justify-end">
                        <x-primary-button>Sign &amp; Submit</x-primary-button>
                    </div>
                </form>
            </x-panel>
        </div>
    </div>
</x-app-layout>
