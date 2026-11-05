{{-- Read-only CPD log: one card per activity, then a total. Expects $appraisal. --}}
@php($items = $appraisal->professionalDevelopment)

@if ($items->isNotEmpty())
    <div class="space-y-3">
        @foreach ($items as $i => $pd)
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/10">
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 px-4 py-3">
                    <span class="grid h-6 w-6 shrink-0 place-items-center rounded-md bg-indigo-900 text-xs font-bold text-white">{{ $i + 1 }}</span>
                    <span class="min-w-0 flex-1 text-base font-semibold text-gray-900">{{ $pd->activity_name }}</span>
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $pd->nature->value === 'Online' ? 'bg-indigo-100 text-indigo-800' : 'bg-emerald-100 text-emerald-800' }}">{{ $pd->nature->value }}</span>
                    <span class="whitespace-nowrap text-xs font-semibold text-gray-600">{{ number_format($pd->hours, 2) }} h</span>
                </div>
                <div class="grid grid-cols-1 gap-x-4 gap-y-3 border-t border-gray-100 px-5 py-3 sm:grid-cols-2">
                    <div>
                        <div class="label-caps">Provider</div>
                        <div class="text-[15px] text-gray-900">{{ $pd->provider ?: '—' }}</div>
                    </div>
                    <div>
                        <div class="label-caps">Date</div>
                        <div class="text-[15px] text-gray-900">{{ optional($pd->activity_date)->format('d M Y') ?? '—' }}</div>
                    </div>
                    <div class="sm:col-span-2">
                        <div class="label-caps">Impact on practice</div>
                        <div class="text-[15px] text-gray-900 whitespace-pre-line">{{ $pd->impact_on_practice ?: '—' }}</div>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="flex items-center justify-between rounded-xl border-l-4 border-accent-400 bg-accent-50 px-4 py-3 ring-1 ring-gray-900/10">
            <span class="text-sm font-bold text-indigo-900">Total hours</span>
            <span class="text-base font-bold text-indigo-900">{{ number_format($items->sum('hours'), 2) }}</span>
        </div>
    </div>
@else
    <p class="text-sm text-gray-500">No professional development activity logged yet.</p>
@endif
