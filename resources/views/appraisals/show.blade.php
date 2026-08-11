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

            <x-appraisal-header :appraisal="$appraisal" />

            <div class="flex flex-wrap items-center justify-end gap-3">
                <a href="{{ route('appraisals.pdf', $appraisal) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                    <x-icon name="download" class="w-3.5 h-3.5" /> PDF
                </a>
                @can('updateAsEmployee', $appraisal)
                    <a href="{{ route('appraisals.edit', $appraisal) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">Complete My Section</a>
                @endcan
                @can('updateAsReviewer', $appraisal)
                    <a href="{{ route('appraisals.review', $appraisal) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">Complete Review</a>
                @endcan
                @can('sign', $appraisal)
                    <a href="{{ route('appraisals.sign', $appraisal) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700">Sign Off</a>
                @endcan
                @if (auth()->user()->isAdmin() && $appraisal->status === \App\Enums\AppraisalStatus::Draft)
                    <form method="POST" action="{{ route('admin.appraisals.open', $appraisal) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700">Open for Employee</button>
                    </form>
                @endif
            </div>

            @include('appraisals._tabs')
            @include('appraisals._signoff')
        </div>
    </div>
</x-app-layout>
