<?php

namespace App\Http\Requests;

use App\Enums\OverallRating;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateAppraisalReviewerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('updateAsReviewer', $this->route('appraisal'));
    }

    public function rules(): array
    {
        $submitting = $this->input('intent') === 'submit';

        return [
            'appraisal_date' => ['nullable', 'date'],
            'reviewer_comments' => [$submitting ? 'required' : 'nullable', 'string'],
            'overall_rating' => [$submitting ? 'required' : 'nullable', new Enum(OverallRating::class)],
            'next_review_date' => ['nullable', 'date'],

            'next_year_targets' => ['array'],
            'next_year_targets.*.target_text' => ['nullable', 'string'],
            'next_year_targets.*.action_text' => ['nullable', 'string'],
            'next_year_targets.*.success_criteria' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $submitting = $this->input('intent') === 'submit';

            if (! $submitting) {
                return;
            }

            $hasAtLeastOneTarget = collect($this->input('next_year_targets', []))
                ->contains(fn ($target) => filled($target['target_text'] ?? null));

            if (! $hasAtLeastOneTarget) {
                $validator->errors()->add('next_year_targets', 'Set at least one target for next year before submitting your review.');
            }
        });
    }
}
