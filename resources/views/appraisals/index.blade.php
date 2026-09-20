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

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="p-4 bg-green-50 text-green-700 text-sm rounded-md">{{ session('status') }}</div>
            @endif

            @if ($teamAppraisals->isNotEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide p-4 pb-0">As Reviewer — Your Team</p>
                    <table class="min-w-full divide-y divide-gray-200 mt-4">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Year</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($teamAppraisals as $appraisal)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $appraisal->employee->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $appraisal->year }}</td>
                                    <td class="px-6 py-4 text-sm"><x-status-badge :status="$appraisal->status" /></td>
                                    <td class="px-6 py-4 text-sm text-right whitespace-nowrap">
                                        <x-action-menu>
                                            @can('open', $appraisal)
                                                <x-action-menu.form-item :action="route('admin.appraisals.open', $appraisal)" method="PATCH" icon="unlock" variant="success">Open for Employee</x-action-menu.form-item>
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
                                            <x-action-menu.item :href="route('appraisals.pdf', $appraisal)" icon="download">Download</x-action-menu.item>
                                        </x-action-menu>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide p-4 pb-0">My Appraisal History</p>
                <table class="min-w-full divide-y divide-gray-200 mt-4">
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
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">You have no appraisals yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
