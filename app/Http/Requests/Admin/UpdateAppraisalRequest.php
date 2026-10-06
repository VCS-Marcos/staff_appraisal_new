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
            'reviewer_id' => ['required', 'integer', 'different:user_id', Rule::exists('users', 'id')],
            'appraisal_date' => ['nullable', 'date'],
            'targets' => ['array'],
            'targets.*.id' => ['nullable', 'integer'],
            'targets.*.target_text' => ['nullable', 'string'],
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
