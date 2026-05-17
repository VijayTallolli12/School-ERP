<?php

namespace App\Modules\Users\Requests;

use App\Models\School;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->whereNull('deleted_at')],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', Password::defaults(), 'confirmed'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'role' => ['required', Rule::exists('roles', 'name')],
            'school_id' => ['nullable', Rule::exists('schools', 'id')],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    public function messages(): array
    {
        return [
            'role.exists' => 'The selected role is invalid.',
            'school_id.exists' => 'The selected school does not exist.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }
}