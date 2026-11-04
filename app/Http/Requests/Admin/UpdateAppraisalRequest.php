<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppraisalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('edit', $this->route('appraisal'));
    }

    protected function prepareForValidation(): void
    {
        // Only an admin may change who is appraised or who reviews; for a reviewer these
        // are pinned to the current values so a tampered request can't reassign them.
        if (! $this->user()->isAdmin()) {
            $appraisal = $this->route('appraisal');
            $this->merge(['user_id' => $appraisal->user_id, 'reviewer_id' => $appraisal->reviewer_id]);
        }
    }

    public function rules(): array
    {
        $appraisal = $this->route('appraisal');

        return [
            'user_id' => [
                'required', 'integer', Rule::exists('users', 'id'),
                Rule::unique('appraisals', 'user_id')
                    ->where('year', $appraisal->year)
                    ->ignore($appraisal->id),
            ],
            // Reviewer/admin accounts only — but keep the current reviewer valid so re-saving never fails.
            'reviewer_id' => [
                'required', 'integer', 'different:user_id',
                Rule::exists('users', 'id')->where(fn ($q) => $q->whereIn('role', ['reviewer', 'admin'])->orWhere('id', $appraisal->reviewer_id)),
            ],
            'appraisal_date' => ['required', 'date'],
            'appraisal_time' => ['nullable', 'date_format:H:i'],
            'venue' => ['nullable', 'string', 'max:255'],
            'targets' => ['array'],
            'targets.*.id' => ['nullable', 'integer'],
            'targets.*.target_text' => ['nullable', 'string'],
            'targets.*.actions' => ['nullable', 'array'],
            'targets.*.actions.*.action_text' => ['nullable', 'string'],
            'targets.*.actions.*.success_criteria' => ['nullable', 'string'],
            // Legacy single-pair fields: still accepted so a form opened before the multi-action update can be saved.
            'targets.*.action_text' => ['nullable', 'string'],
            'targets.*.success_criteria' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.unique' => 'This employee already has a different appraisal for this year.',
        ];
    }
}
