<?php

namespace App\Modules\Users\Requests;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.update');
    }

    public function rules(): array
    {
        /** @var User $target */
        $target = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($target->id)->whereNull('deleted_at')],
            'phone' => ['nullable', 'string', 'max:30'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'role' => ['sometimes', 'required', Rule::exists('roles', 'name')],
            'school_id' => ['nullable', Rule::exists('schools', 'id')],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    public function messages(): array
    {
        return [
            'role.exists' => 'The selected role is invalid.',
            'school_id.exists' => 'The selected school does not exist.',
        ];
    }
}