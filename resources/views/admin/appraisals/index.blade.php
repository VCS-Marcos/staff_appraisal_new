<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('All Appraisals') }}</h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.appraisals.export', request()->query()) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                    ⬇ Export CSV
                </a>
                <a href="{{ route('admin.appraisals.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    + Open Appraisal
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                @foreach (\App\Enums\AppraisalStatus::cases() as $status)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                        <div class="text-2xl font-bold text-gray-800">{{ $statusCounts[$status->value] ?? 0 }}</div>
                        <div class="text-xs text-gray-500">{{ $status->label() }}</div>
                    </div>
                @endforeach
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if (session('status'))
                    <div class="p-4 bg-green-50 text-green-700 text-sm">{{ session('status') }}</div>
                @endif

                <form method="GET" class="p-4 flex gap-3 border-b border-gray-100">
                    <select name="cycle_id" class="border-gray-300 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                        <option value="">All Cycles</option>
                        @foreach ($cycles as $cycle)
                            <option value="{{ $cycle->id }}" @selected(request('cycle_id') == $cycle->id)>{{ $cycle->name }} ({{ $cycle->term->value }})</option>
                        @endforeach
                    </select>
                    <select name="status" class="border-gray-300 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        @foreach (\App\Enums\AppraisalStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </form>

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reviewer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cycle</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($appraisals as $appraisal)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $appraisal->employee->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $appraisal->reviewer->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $appraisal->cycle->name }} &middot; {{ $appraisal->cycle->term->value }}</td>
                                <td class="px-6 py-4 text-sm"><x-status-badge :status="$appraisal->status" /></td>
                                <td class="px-6 py-4 text-sm text-right space-x-3">
                                    @if ($appraisal->status === \App\Enums\AppraisalStatus::Draft)
                                        <form method="POST" action="{{ route('admin.appraisals.open', $appraisal) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-green-700 hover:underline">Open for Employee</button>
                                        </form>
                                    @endif
                                    <a href="{{ route('appraisals.show', $appraisal) }}" class="text-indigo-600 hover:underline">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">No appraisals found.</td>
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
