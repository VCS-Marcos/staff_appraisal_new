<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('My Appraisals') }}</h2>
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cycle</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($teamAppraisals as $appraisal)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $appraisal->employee->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $appraisal->cycle->name }} &middot; {{ $appraisal->cycle->term->value }}</td>
                                    <td class="px-6 py-4 text-sm"><x-status-badge :status="$appraisal->status" /></td>
                                    <td class="px-6 py-4 text-sm text-right space-x-3">
                                        @if ($appraisal->status === \App\Enums\AppraisalStatus::PendingReviewer)
                                            <a href="{{ route('appraisals.review', $appraisal) }}" class="text-indigo-600 hover:underline">Review</a>
                                        @endif
                                        <a href="{{ route('appraisals.show', $appraisal) }}" class="text-gray-600 hover:underline">View</a>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cycle</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($myAppraisals as $appraisal)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $appraisal->cycle->name }} &middot; {{ $appraisal->cycle->term->value }}</td>
                                <td class="px-6 py-4 text-sm"><x-status-badge :status="$appraisal->status" /></td>
                                <td class="px-6 py-4 text-sm text-right space-x-3">
                                    @if ($appraisal->status === \App\Enums\AppraisalStatus::PendingEmployee)
                                        <a href="{{ route('appraisals.edit', $appraisal) }}" class="text-indigo-600 hover:underline">Complete</a>
                                    @endif
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
