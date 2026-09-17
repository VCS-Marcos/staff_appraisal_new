<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @php
        $cardBase = 'bg-white border border-gray-200 rounded-xl p-4 block transition hover:border-indigo-300 hover:shadow-sm';
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (auth()->user()->isAdmin())
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <a href="{{ route('admin.users.index') }}" class="{{ $cardBase }}">
                        <div class="text-2xl font-bold text-gray-800">{{ $stats['users'] }}</div>
                        <div class="text-sm text-gray-500">Staff Accounts</div>
                    </a>
                    <a href="{{ route('admin.appraisals.index', ['year' => $stats['current_year']]) }}" class="{{ $cardBase }}">
                        <div class="text-2xl font-bold text-indigo-600">{{ $stats['current_year'] }}</div>
                        <div class="text-sm text-gray-500">Current Year</div>
                    </a>
                    <a href="{{ route('admin.appraisals.index', ['status' => 'in_progress']) }}" class="{{ $cardBase }}">
                        <div class="text-2xl font-bold text-amber-600">{{ $stats['pending_employee'] + $stats['pending_reviewer'] + $stats['pending_signoff'] }}</div>
                        <div class="text-sm text-gray-500">In Progress</div>
                    </a>
                    <a href="{{ route('admin.appraisals.index', ['status' => 'completed']) }}" class="{{ $cardBase }}">
                        <div class="text-2xl font-bold text-emerald-600">{{ $stats['completed'] }}</div>
                        <div class="text-sm text-gray-500">Completed</div>
                    </a>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Quick Actions</h3>
                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('admin.appraisals.create') }}" class="inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:underline">
                            <x-icon name="plus" class="w-4 h-4" /> Create an appraisal
                        </a>
                        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:underline">
                            <x-icon name="plus" class="w-4 h-4" /> Add a staff account
                        </a>
                        <a href="{{ route('admin.users.import') }}" class="inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:underline">
                            <x-icon name="upload" class="w-4 h-4" /> Import staff from CSV
                        </a>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <a href="{{ route('appraisals.index') }}" class="{{ $cardBase }}">
                        <div class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</div>
                        <div class="text-sm text-gray-500">{{ auth()->user()->isReviewer() ? 'Team Appraisals' : 'My Appraisals' }}</div>
                    </a>
                    <a href="{{ route('appraisals.index') }}" class="{{ $cardBase }}">
                        <div class="text-2xl font-bold text-amber-600">{{ $stats['pending'] }}</div>
                        <div class="text-sm text-gray-500">{{ auth()->user()->isReviewer() ? 'Awaiting My Review' : 'Awaiting My Action' }}</div>
                    </a>
                    <a href="{{ route('appraisals.index') }}" class="{{ $cardBase }}">
                        <div class="text-2xl font-bold text-indigo-600">{{ $stats['in_progress'] }}</div>
                        <div class="text-sm text-gray-500">In Progress</div>
                    </a>
                    <a href="{{ route('appraisals.index') }}" class="{{ $cardBase }}">
                        <div class="text-2xl font-bold text-emerald-600">{{ $stats['completed'] }}</div>
                        <div class="text-sm text-gray-500">Completed</div>
                    </a>
                </div>
            @endif

            @if (isset($teamAppraisals) && $teamAppraisals->isNotEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Your Team's Appraisals</h3>
                    <div class="divide-y divide-gray-100">
                        @foreach ($teamAppraisals as $appraisal)
                            <div class="py-3 flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-gray-800">{{ $appraisal->employee->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $appraisal->year }}</div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <x-status-badge :status="$appraisal->status" />
                                    <a href="{{ route('appraisals.show', $appraisal) }}" class="text-sm text-indigo-600 hover:underline">View</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">My Appraisals</h3>
                @if ($myAppraisals->isEmpty())
                    <p class="text-gray-500 text-sm">You have no appraisals yet.</p>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach ($myAppraisals as $appraisal)
                            <div class="py-3 flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-gray-800">{{ $appraisal->year }}</div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <x-status-badge :status="$appraisal->status" />
                                    <a href="{{ route('appraisals.show', $appraisal) }}" class="text-sm text-indigo-600 hover:underline">View</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
