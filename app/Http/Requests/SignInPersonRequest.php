<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class SignInPersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('signOnBehalf', $this->route('appraisal'));
    }

    public function rules(): array
    {
        $appraisal = $this->route('appraisal');

        return [
            'employee_typed_name' => [$appraisal->employee_signed_at === null ? 'required' : 'nullable', 'string'],
            'reviewer_typed_name' => [$appraisal->reviewer_signed_at === null ? 'required' : 'nullable', 'string'],
            'confirm_in_person' => ['accepted'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $appraisal = $this->route('appraisal');

            if ($appraisal->employee_signed_at === null) {
                $typed = trim((string) $this->input('employee_typed_name'));

                if ($typed !== '' && strcasecmp($typed, trim($appraisal->employee->name)) !== 0) {
                    $validator->errors()->add(
                        'employee_typed_name',
                        "Please type {$appraisal->employee->name}'s full name exactly as it appears on their account."
                    );
                }
            }

            if ($appraisal->reviewer_signed_at === null) {
                $typed = trim((string) $this->input('reviewer_typed_name'));

                if ($typed !== '' && strcasecmp($typed, trim($this->user()->name)) !== 0) {
                    $validator->errors()->add(
                        'reviewer_typed_name',
                        'Please type your own full name exactly as it appears on your account.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'confirm_in_person.accepted' => 'You must confirm this signing took place in person before continuing.',
        ];
    }
}
