<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('reports.index') }}" class="text-xs text-gray-500 hover:text-gray-700">&larr; Reports</a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Staff Ratings Report') }}</h2>
            </div>
            <a href="{{ route('reports.ratings.export', request()->query()) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                <x-icon name="download" class="w-3.5 h-3.5" /> Export CSV
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach (\App\Enums\OverallRating::cases() as $rating)
                    <div class="bg-white border border-gray-200 rounded-xl p-4">
                        <div class="text-2xl font-bold text-gray-800">{{ $distribution[$rating->value] ?? 0 }}</div>
                        <div class="text-sm text-gray-500">{{ $rating->value }}</div>
                    </div>
                @endforeach
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 border-b border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Staff Ratings</p>
                    <form method="GET" class="flex gap-3">
                        <select name="year" class="border-gray-300 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                            <option value="">All Years</option>
                            @foreach ($years as $year)
                                <option value="{{ $year }}" @selected(request('year') == $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reviewer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Year</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Overall Rating</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($appraisals as $appraisal)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $appraisal->employee->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $appraisal->reviewer->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $appraisal->year }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $appraisal->overall_rating->value }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">No rated appraisals found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="p-4">
                    {{ $appraisals->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
