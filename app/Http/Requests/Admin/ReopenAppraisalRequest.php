<?php

namespace App\Http\Requests\Admin;

use App\Enums\AppraisalStatus;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReopenAppraisalRequest extends FormRequest
{
    /**
     * Statuses a given target status may validly be sent back from.
     */
    private const VALID_FROM = [
        'pending_employee' => ['pending_reviewer', 'pending_signoff', 'completed'],
        'pending_reviewer' => ['pending_signoff', 'completed'],
    ];

    public function authorize(): bool
    {
        return $this->user()->can('reopen', $this->route('appraisal'));
    }

    public function rules(): array
    {
        return [
            'target_status' => ['required', Rule::in(array_keys(self::VALID_FROM))],
            'reason' => ['required', 'string', 'max:500'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $target = $this->input('target_status');
            $current = $this->route('appraisal')->status;

            if (! $target || ! isset(self::VALID_FROM[$target])) {
                return;
            }

            if (! in_array($current->value, self::VALID_FROM[$target], true)) {
                $validator->errors()->add(
                    'target_status',
                    "This appraisal can't be sent back to {$this->targetLabel($target)} from its current stage ({$current->label()})."
                );
            }
        });
    }

    private function targetLabel(string $value): string
    {
        return AppraisalStatus::from($value)->label();
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Please give a short reason — this is recorded in the Activity Log.',
        ];
    }
}
