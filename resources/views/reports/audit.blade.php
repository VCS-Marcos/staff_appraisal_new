<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('reports.index') }}" class="text-xs text-gray-500 hover:text-gray-700">&larr; Reports</a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Activity Log') }}</h2>
            </div>
            <a href="{{ route('reports.audit.export', request()->query()) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                <x-icon name="download" class="w-3.5 h-3.5" /> Export CSV
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 border-b border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">All User Activity</p>
                    <form method="GET" class="flex flex-wrap gap-3">
                        <select name="user_id" class="border-gray-300 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                            <option value="">All users</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>
                            @endforeach
                        </select>
                        <select name="action" class="border-gray-300 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                            <option value="">All actions</option>
                            @foreach ($actions as $action)
                                <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                            @endforeach
                        </select>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-500">
                            From
                            <input type="date" name="from" value="{{ request('from') }}" class="border-gray-300 rounded-md shadow-sm text-sm">
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-500">
                            To
                            <input type="date" name="to" value="{{ request('to') }}" class="border-gray-300 rounded-md shadow-sm text-sm">
                        </label>
                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm text-gray-600 hover:bg-gray-50">Apply</button>
                        @if (request('user_id') || request('action') || request('from') || request('to'))
                            <a href="{{ route('reports.audit') }}" class="inline-flex items-center justify-center px-4 py-2 text-sm text-gray-500 hover:text-gray-700">Clear</a>
                        @endif
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">When</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Details</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($logs as $log)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ optional($log->created_at)->format('d M Y H:i') }}</td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $log->user?->name ?? 'System' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $log->action }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ $log->description ?? '—' }}
                                        <span class="block text-xs text-gray-400">{{ $log->entity_type }} #{{ $log->entity_id }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-400">{{ $log->ip_address ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">No activity recorded for this filter.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
