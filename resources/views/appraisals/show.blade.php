<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Staff Appraisal') }}</h2>
            <x-status-badge :status="$appraisal->status" />
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="p-4 bg-green-50 text-green-700 text-sm rounded-md">{{ session('status') }}</div>
            @endif

            @include('appraisals._summary')

            <div class="flex flex-wrap items-center gap-4">
                <a href="{{ route('appraisals.pdf', $appraisal) }}" class="text-sm text-gray-700 hover:underline">⬇ Download PDF</a>
                @can('updateAsEmployee', $appraisal)
                    <a href="{{ route('appraisals.edit', $appraisal) }}" class="text-indigo-600 hover:underline text-sm">Complete My Section →</a>
                @endcan
                @can('updateAsReviewer', $appraisal)
                    <a href="{{ route('appraisals.review', $appraisal) }}" class="text-indigo-600 hover:underline text-sm">Complete Review →</a>
                @endcan
                @can('sign', $appraisal)
                    <a href="{{ route('appraisals.sign', $appraisal) }}" class="text-green-700 hover:underline text-sm font-semibold">Sign Off →</a>
                @endcan
                @if (auth()->user()->isAdmin() && $appraisal->status === \App\Enums\AppraisalStatus::Draft)
                    <form method="POST" action="{{ route('admin.appraisals.open', $appraisal) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="text-green-700 hover:underline text-sm">Open for Employee →</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
