<?php

namespace App\Http\Requests\Admin;

use App\Models\Appraisal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppraisalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Appraisal::class);
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required', 'integer', Rule::exists('users', 'id'),
                Rule::unique('appraisals', 'user_id')->where('year', $this->input('year')),
            ],
            'reviewer_id' => ['required', 'integer', 'different:user_id', Rule::exists('users', 'id')],
            'year' => ['required', 'integer', 'between:2000,2100'],
            'appraisal_date' => ['nullable', 'date'],
            'intent' => ['nullable', 'string', 'in:draft,open'],
            'targets' => ['array'],
            'targets.*.target_text' => ['nullable', 'string'],
            'targets.*.action_text' => ['nullable', 'string'],
            'targets.*.success_criteria' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.unique' => 'This employee already has an appraisal for the selected year.',
        ];
    }
}
