<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('reports.index') }}" class="text-xs text-gray-500 hover:text-gray-700">&larr; Reports</a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Appraisal Completion Report') }}</h2>
            </div>
            <a href="{{ route('reports.completion.export', request()->query()) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                <x-icon name="download" class="w-3.5 h-3.5" /> Export CSV
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 border-b border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Appraisal Completion</p>
                    <form method="GET" class="flex gap-3">
                        <select name="cycle_id" class="border-gray-300 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                            <option value="">All Cycles</option>
                            @foreach ($cycles as $cycle)
                                <option value="{{ $cycle->id }}" @selected(request('cycle_id') == $cycle->id)>{{ $cycle->name }} ({{ $cycle->term->value }})</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cycle</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Draft</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Awaiting Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Awaiting Reviewer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Awaiting Sign-off</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Completed</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Completion</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($rows as $row)
                            @php
                                $percent = $row->total > 0 ? round($row->completed / $row->total * 100) : 0;
                            @endphp
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $row->cycle_name }} &middot; {{ $row->cycle_term }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $row->total }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $row->draft }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $row->pending_employee }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $row->pending_reviewer }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $row->pending_signoff }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $row->completed }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex items-center gap-2">
                                        <div class="w-20 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                            <div class="h-full bg-emerald-500" style="width: {{ $percent }}%"></div>
                                        </div>
                                        <span class="text-gray-700 font-medium">{{ $percent }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-sm text-gray-500">No appraisal cycles found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
