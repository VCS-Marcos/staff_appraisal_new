<?php

namespace App\Http\Requests\Admin;

use App\Enums\CycleTerm;
use App\Models\AppraisalCycle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreCycleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', AppraisalCycle::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'term' => ['required', new Enum(CycleTerm::class)],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_active' => ['boolean'],
        ];
    }
}
