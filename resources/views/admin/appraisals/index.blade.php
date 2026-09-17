<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('All Appraisals') }}</h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.appraisals.export', request()->query()) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                    <x-icon name="download" class="w-3.5 h-3.5" /> Export CSV
                </a>
                <a href="{{ route('admin.appraisals.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    <x-icon name="plus" class="w-3.5 h-3.5" /> Create Appraisal
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                @foreach (\App\Enums\AppraisalStatus::cases() as $status)
                    <a href="{{ route('admin.appraisals.index', array_merge(request()->except('page'), ['status' => $status->value])) }}"
                       class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 block transition hover:ring-2 hover:ring-indigo-200 {{ request('status') === $status->value ? 'ring-2 ring-indigo-400' : '' }}">
                        <div class="text-2xl font-bold text-gray-800">{{ $statusCounts[$status->value] ?? 0 }}</div>
                        <div class="text-xs text-gray-500">{{ $status->label() }}</div>
                    </a>
                @endforeach
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if (session('status'))
                    <div class="p-4 bg-green-50 text-green-700 text-sm">{{ session('status') }}</div>
                @endif

                <div class="p-4 border-b border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">All Appraisals</p>
                    <form method="GET" class="flex flex-wrap gap-3">
                        <div class="relative flex-1 min-w-[200px]">
                            <x-icon name="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by staff name..."
                                   class="w-full pl-9 border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <select name="year" class="border-gray-300 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                            <option value="">All Years</option>
                            @foreach ($years as $year)
                                <option value="{{ $year }}" @selected(request('year') == $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                        <select name="status" class="border-gray-300 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <option value="in_progress" @selected(request('status') === 'in_progress')>In Progress (any pending)</option>
                            @foreach (\App\Enums\AppraisalStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        <select name="completion_mode" class="border-gray-300 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                            <option value="">All Modes</option>
                            @foreach (\App\Enums\CompletionMode::cases() as $mode)
                                <option value="{{ $mode->value }}" @selected(request('completion_mode') === $mode->value)>{{ $mode->label() }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm text-gray-600 hover:bg-gray-50">Search</button>
                        @if (request('search') || request('year') || request('status') || request('completion_mode'))
                            <a href="{{ route('admin.appraisals.index') }}" class="inline-flex items-center justify-center px-4 py-2 text-sm text-gray-500 hover:text-gray-700">Clear</a>
                        @endif
                    </form>
                </div>

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reviewer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Year</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mode</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($appraisals as $appraisal)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $appraisal->employee->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $appraisal->reviewer->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $appraisal->year }}</td>
                                <td class="px-6 py-4 text-sm"><x-status-badge :status="$appraisal->status" /></td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $appraisal->completion_mode === \App\Enums\CompletionMode::Assisted ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $appraisal->completion_mode->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-right whitespace-nowrap">
                                    <x-action-menu>
                                        @if ($appraisal->status === \App\Enums\AppraisalStatus::Draft)
                                            <x-action-menu.form-item :action="route('admin.appraisals.open', $appraisal)" method="PATCH" icon="unlock" variant="success">
                                                Open for Employee
                                            </x-action-menu.form-item>
                                        @endif
                                        <x-action-menu.item :href="route('appraisals.show', $appraisal)" icon="eye">View</x-action-menu.item>
                                        <x-action-menu.item :href="route('admin.appraisals.edit', $appraisal)" icon="pencil">Edit</x-action-menu.item>
                                        @can('reopen', $appraisal)
                                            <x-action-menu.item :href="route('admin.appraisals.reopen', $appraisal)" icon="arrow-uturn-left" variant="warning">Send Back</x-action-menu.item>
                                        @endcan
                                        <x-action-menu.item :href="route('appraisals.pdf', $appraisal)" icon="download">Download</x-action-menu.item>
                                        <div class="my-1 border-t border-gray-100"></div>
                                        <x-confirm-delete menu-item :action="route('admin.appraisals.destroy', $appraisal)"
                                            prompt="Also removes its targets & CPD entries." />
                                    </x-action-menu>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">No appraisals found.</td>
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
