<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Professional Development Hours Report') }}</h2>
            <a href="{{ route('reports.pd-hours.export', request()->query()) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                ⬇ Export CSV
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="GET" class="p-4 flex gap-3 border-b border-gray-100">
                    <select name="cycle_id" class="border-gray-300 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                        <option value="">All Cycles</option>
                        @foreach ($cycles as $cycle)
                            <option value="{{ $cycle->id }}" @selected(request('cycle_id') == $cycle->id)>{{ $cycle->name }} ({{ $cycle->term->value }})</option>
                        @endforeach
                    </select>
                </form>

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Staff</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Activities Logged</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Hours</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($summary as $row)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $row->user_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $row->activity_count }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ number_format($row->total_hours, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">No professional development activity logged yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
