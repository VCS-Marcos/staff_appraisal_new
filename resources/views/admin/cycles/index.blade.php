<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Appraisal Cycles') }}</h2>
            <a href="{{ route('admin.cycles.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                <x-icon name="plus" class="w-3.5 h-3.5" /> New Cycle
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if (session('status'))
                    <div class="p-4 bg-green-50 text-green-700 text-sm">{{ session('status') }}</div>
                @endif
                <div class="flex items-center gap-3 p-4 pb-0">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        {{ request()->boolean('active') ? 'Active Cycles' : 'All Cycles' }}
                    </p>
                    @if (request()->boolean('active'))
                        <a href="{{ route('admin.cycles.index') }}" class="text-xs text-indigo-600 hover:underline">Show all</a>
                    @endif
                </div>
                <table class="min-w-full divide-y divide-gray-200 mt-2">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Term</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dates</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Appraisals</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($cycles as $cycle)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $cycle->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $cycle->term->value }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $cycle->start_date->format('d M Y') }} – {{ $cycle->end_date->format('d M Y') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <a href="{{ route('admin.appraisals.index', ['cycle_id' => $cycle->id]) }}" class="text-indigo-600 hover:underline">{{ $cycle->appraisals_count }}</a>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="{{ $cycle->is_active ? 'text-green-700' : 'text-gray-500' }}">
                                        {{ $cycle->is_active ? 'Active' : 'Closed' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-right space-x-3">
                                    <a href="{{ route('admin.appraisals.create', ['cycle_id' => $cycle->id]) }}" class="text-indigo-600 hover:underline">Create Appraisal</a>
                                    <a href="{{ route('admin.cycles.edit', $cycle) }}" class="text-indigo-600 hover:underline">Edit</a>
                                    <form method="POST" action="{{ route('admin.cycles.destroy', $cycle) }}" class="inline" onsubmit="return confirm('Delete this cycle?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4">
                    {{ $cycles->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
