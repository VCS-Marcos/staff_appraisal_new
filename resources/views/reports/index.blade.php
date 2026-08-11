<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Reports') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('reports.completion') }}" class="bg-white border border-gray-200 rounded-xl p-5 hover:border-indigo-300 hover:shadow-sm transition">
                        <x-icon name="check-circle" class="w-6 h-6 text-indigo-600 mb-2" />
                        <div class="font-semibold text-gray-900">Appraisal Completion</div>
                        <p class="text-sm text-gray-500 mt-1">Track how many appraisals are completed per cycle, and where the rest stand in the workflow.</p>
                    </a>

                    <a href="{{ route('reports.ratings') }}" class="bg-white border border-gray-200 rounded-xl p-5 hover:border-indigo-300 hover:shadow-sm transition">
                        <x-icon name="list" class="w-6 h-6 text-indigo-600 mb-2" />
                        <div class="font-semibold text-gray-900">Staff Ratings</div>
                        <p class="text-sm text-gray-500 mt-1">See overall ratings given across completed appraisals, by staff member and cycle.</p>
                    </a>
                @endif

                <a href="{{ route('reports.pd-hours') }}" class="bg-white border border-gray-200 rounded-xl p-5 hover:border-indigo-300 hover:shadow-sm transition">
                    <x-icon name="reports" class="w-6 h-6 text-indigo-600 mb-2" />
                    <div class="font-semibold text-gray-900">Professional Development Hours</div>
                    <p class="text-sm text-gray-500 mt-1">{{ auth()->user()->isAdmin() ? 'CPD activities and hours logged by all staff.' : "CPD activities and hours logged by your team." }}</p>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
