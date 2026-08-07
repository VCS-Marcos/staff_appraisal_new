<?php

namespace App\Http\Requests;

use App\Enums\PdNature;
use App\Enums\TargetMet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateAppraisalEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('updateAsEmployee', $this->route('appraisal'));
    }

    public function rules(): array
    {
        $submitting = $this->input('intent') === 'submit';

        return [
            'self_reflection' => [$submitting ? 'required' : 'nullable', 'string'],
            'targets' => ['array'],
            'targets.*.id' => ['required', 'integer'],
            'targets.*.target_met' => [$submitting ? 'required' : 'nullable', new Enum(TargetMet::class)],
            'targets.*.comments' => ['nullable', 'string'],

            'pd' => ['array'],
            'pd.*.activity_name' => ['nullable', 'string', 'max:255'],
            'pd.*.nature' => ['nullable', new Enum(PdNature::class)],
            'pd.*.provider' => ['nullable', 'string', 'max:255'],
            'pd.*.impact_on_practice' => ['nullable', 'string'],
            'pd.*.hours' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'pd.*.activity_date' => ['nullable', 'date'],
        ];
    }
}
