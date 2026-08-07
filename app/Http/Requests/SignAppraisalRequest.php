<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class SignAppraisalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('sign', $this->route('appraisal'));
    }

    public function rules(): array
    {
        return [
            'typed_name' => ['required', 'string'],
            'confirm' => ['accepted'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $typed = trim((string) $this->input('typed_name'));

            if ($typed !== '' && strcasecmp($typed, trim($this->user()->name)) !== 0) {
                $validator->errors()->add('typed_name', 'Please type your full name exactly as it appears on your account.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'confirm.accepted' => 'You must confirm this is an accurate record before signing.',
        ];
    }
}
