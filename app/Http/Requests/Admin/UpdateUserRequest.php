<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use App\Http\Requests\Admin\Concerns\HasPhotoRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    use HasPhotoRules;

    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('user'));
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => ['required', new Enum(UserRole::class)],
            'position' => ['nullable', 'string', 'max:150'],
            'line_manager_id' => ['nullable', 'integer', Rule::exists('users', 'id'), 'not_in:'.$user->id],
            'is_active' => ['boolean'],
            'photo' => self::photoRules(),
            'remove_photo' => ['boolean'],
        ];
    }
}
