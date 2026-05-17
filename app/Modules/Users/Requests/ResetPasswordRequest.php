<?php

namespace App\Modules\Users\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.update');
    }

    public function rules(): array
    {
        return [
            'password' => ['required', Password::defaults(), 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }
}