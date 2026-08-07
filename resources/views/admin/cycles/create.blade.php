<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('New Appraisal Cycle') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.cycles.store') }}" class="space-y-4">
                    @csrf
                    @include('admin.cycles._form', ['cycle' => null])
                    <div class="flex justify-end">
                        <x-primary-button>Create Cycle</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
