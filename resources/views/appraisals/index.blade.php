<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('My Appraisals') }}</h2>
            @can('create', \App\Models\Appraisal::class)
                <a href="{{ route('admin.appraisals.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    <x-icon name="plus" class="w-3.5 h-3.5" /> Create Appraisal
                </a>
            @endcan
        </div>
    </x-slot>

    @php
        $teamKeys = ['t_search', 't_year', 't_status', 'team_page'];
        $mineKeys = ['m_year', 'm_status', 'mine_page'];
    @endphp

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="p-4 bg-green-50 text-green-700 text-sm rounded-md">{{ session('status') }}</div>
            @endif

            @if ($hasTeam)
                <div id="team-appraisals" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 border-b border-gray-100">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">As Reviewer — Your Team</p>
                        <form method="GET" action="{{ route('appraisals.index') }}#team-appraisals" class="flex flex-wrap gap-3">
                            {{-- Keep the other list's filters when this one is submitted. --}}
                            @foreach (request()->except($teamKeys) as $key => $value)
                                @if (is_scalar($value))
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <div class="relative flex-1 min-w-[200px]">
                                <x-icon name="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                                <input type="text" name="t_search" value="{{ request('t_search') }}" placeholder="Search by staff name..."
                                       class="w-full pl-9 border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <select name="t_year" class="border-gray-300 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                                <option value="">All Years</option>
                                @foreach ($teamYears as $year)
                                    <option value="{{ $year }}" @selected(request('t_year') == $year)>{{ $year }}</option>
                                @endforeach
                            </select>
                            <select name="t_status" class="border-gray-300 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                                <option value="">All Statuses</option>
                                <option value="in_progress" @selected(request('t_status') === 'in_progress')>In Progress (any pending)</option>
                                @foreach (\App\Enums\AppraisalStatus::cases() as $status)
                                    <option value="{{ $status->value }}" @selected(request('t_status') === $status->value)>{{ $status->label() }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm text-gray-600 hover:bg-gray-50">Search</button>
                            @if (request('t_search') || request('t_year') || request('t_status'))
                                <a href="{{ route('appraisals.index', request()->except($teamKeys)) }}#team-appraisals" class="inline-flex items-center justify-center px-4 py-2 text-sm text-gray-500 hover:text-gray-700">Clear</a>
                            @endif
                        </form>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Year</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($teamAppraisals as $appraisal)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $appraisal->employee->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $appraisal->year }}</td>
                                    <td class="px-6 py-4 text-sm"><x-status-badge :status="$appraisal->status" /></td>
                                    <td class="px-6 py-4 text-sm text-right whitespace-nowrap">
                                        <x-action-menu>
                                            @can('open', $appraisal)
                                                <x-action-menu.form-item :action="route('admin.appraisals.open', $appraisal)" method="PATCH" icon="unlock" variant="success">Open for Employee</x-action-menu.form-item>
                                            @endcan
                                            @can('edit', $appraisal)
                                                <x-action-menu.item :href="route('admin.appraisals.edit', $appraisal)" icon="pencil">Edit</x-action-menu.item>
                                            @endcan
                                            @can('updateAsEmployee', $appraisal)
                                                <x-action-menu.item :href="route('appraisals.edit', $appraisal)" icon="pencil" variant="warning">Complete In Person</x-action-menu.item>
                                            @endcan
                                            @if ($appraisal->status === \App\Enums\AppraisalStatus::PendingReviewer)
                                                <x-action-menu.item :href="route('appraisals.review', $appraisal)" icon="pencil">Review</x-action-menu.item>
                                            @endif
                                            @can('sign', $appraisal)
                                                <x-action-menu.item :href="route('appraisals.sign', $appraisal)" icon="check-circle" variant="success">Sign Off</x-action-menu.item>
                                            @endcan
                                            @can('signOnBehalf', $appraisal)
                                                <x-action-menu.item :href="route('appraisals.sign-in-person', $appraisal)" icon="check-circle" variant="warning">Complete Sign-off (In Person)</x-action-menu.item>
                                            @endcan
                                            <x-action-menu.item :href="route('appraisals.show', $appraisal)" icon="eye">View</x-action-menu.item>
                                            @if ($appraisal->isCompleted())
                                                <x-action-menu.item :href="route('appraisals.pdf', $appraisal)" icon="download">Download PDF</x-action-menu.item>
                                            @endif
                                        </x-action-menu>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">No appraisals match your filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="p-4">
                        {{ $teamAppraisals->fragment('team-appraisals')->links() }}
                    </div>
                </div>
            @endif

            <div id="my-appraisals" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 border-b border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide {{ $myYears->isNotEmpty() ? 'mb-3' : '' }}">My Appraisal History</p>
                    @if ($myYears->isNotEmpty())
                        <form method="GET" action="{{ route('appraisals.index') }}#my-appraisals" class="flex flex-wrap gap-3">
                            @foreach (request()->except($mineKeys) as $key => $value)
                                @if (is_scalar($value))
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <select name="m_year" class="border-gray-300 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                                <option value="">All Years</option>
                                @foreach ($myYears as $year)
                                    <option value="{{ $year }}" @selected(request('m_year') == $year)>{{ $year }}</option>
                                @endforeach
                            </select>
                            <select name="m_status" class="border-gray-300 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                                <option value="">All Statuses</option>
                                <option value="in_progress" @selected(request('m_status') === 'in_progress')>In Progress (any pending)</option>
                                @foreach (\App\Enums\AppraisalStatus::cases() as $status)
                                    <option value="{{ $status->value }}" @selected(request('m_status') === $status->value)>{{ $status->label() }}</option>
                                @endforeach
                            </select>
                            @if (request('m_year') || request('m_status'))
                                <a href="{{ route('appraisals.index', request()->except($mineKeys)) }}#my-appraisals" class="inline-flex items-center justify-center px-4 py-2 text-sm text-gray-500 hover:text-gray-700">Clear</a>
                            @endif
                        </form>
                    @endif
                </div>

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Year</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($myAppraisals as $appraisal)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $appraisal->year }}</td>
                                <td class="px-6 py-4 text-sm"><x-status-badge :status="$appraisal->status" /></td>
                                <td class="px-6 py-4 text-sm text-right space-x-3">
                                    @if ($appraisal->status === \App\Enums\AppraisalStatus::PendingEmployee)
                                        <a href="{{ route('appraisals.edit', $appraisal) }}" class="text-indigo-600 hover:underline">Complete</a>
                                    @endif
                                    @can('sign', $appraisal)
                                        <a href="{{ route('appraisals.sign', $appraisal) }}" class="text-emerald-600 hover:underline">Sign Off</a>
                                    @endcan
                                    <a href="{{ route('appraisals.show', $appraisal) }}" class="text-gray-600 hover:underline">View</a>
                                    @if ($appraisal->isCompleted())
                                        <a href="{{ route('appraisals.pdf', $appraisal) }}" class="text-gray-600 hover:underline">PDF</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">
                                    {{ $myYears->isEmpty() ? 'You have no appraisals yet.' : 'No appraisals match your filters.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="p-4">
                    {{ $myAppraisals->fragment('my-appraisals')->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
