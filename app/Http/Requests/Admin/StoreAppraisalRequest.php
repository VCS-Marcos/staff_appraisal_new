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
                Rule::unique('appraisals', 'user_id')->where('cycle_id', $this->input('cycle_id')),
            ],
            'reviewer_id' => ['required', 'integer', 'different:user_id', Rule::exists('users', 'id')],
            'cycle_id' => ['required', 'integer', Rule::exists('appraisal_cycles', 'id')],
            'appraisal_date' => ['nullable', 'date'],
            'targets' => ['array'],
            'targets.*' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.unique' => 'This employee already has an appraisal for the selected cycle.',
        ];
    }
}
