<div class="bg-white overflow-hidden shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl p-6">
    <h2 class="text-base font-bold text-indigo-900 mb-4">Sign-off</h2>
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
        @foreach ([['Employee signature', $appraisal->employee_signed_at], ['Reviewer signature', $appraisal->reviewer_signed_at]] as [$label, $signedAt])
            <div class="rounded-lg border-l-4 px-4 py-3 {{ $signedAt ? 'border-emerald-500 bg-emerald-50' : 'border-accent-400 bg-accent-50' }}">
                <dt class="label-caps">{{ $label }}</dt>
                <dd class="text-[15px] font-semibold {{ $signedAt ? 'text-emerald-800' : 'text-amber-900' }}">{{ $signedAt ? 'Signed '.$signedAt->format('d M Y H:i') : 'Not yet signed' }}</dd>
            </div>
        @endforeach
    </dl>
</div>
