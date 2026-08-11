<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
    <h2 class="text-base font-bold text-gray-800 mb-4">Sign-off</h2>
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
        <div>
            <dt class="text-gray-500">Employee Signature</dt>
            <dd class="font-medium text-gray-900">{{ $appraisal->employee_signed_at ? 'Signed '.$appraisal->employee_signed_at->format('d M Y H:i') : 'Not yet signed' }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">Reviewer Signature</dt>
            <dd class="font-medium text-gray-900">{{ $appraisal->reviewer_signed_at ? 'Signed '.$appraisal->reviewer_signed_at->format('d M Y H:i') : 'Not yet signed' }}</dd>
        </div>
    </dl>
</div>
