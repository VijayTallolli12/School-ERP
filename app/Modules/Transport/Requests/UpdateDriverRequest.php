<?php

namespace App\Modules\Transport\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('transport.update');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();
        $driver = $this->route('driver');

        return [
            'name' => ['required', 'string', 'max:120'],
            'mobile' => ['required', 'string', 'max:20'],
            'license_number' => ['required', 'string', 'max:60', Rule::unique('drivers')->ignore($driver?->id)->where('school_id', $schoolId)],
            'license_expiry_date' => ['required', 'date'],
            'address' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'enable_login' => ['nullable', 'boolean'],
            'email' => [
                'nullable',
                'required_if:enable_login,1',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($driver?->user_id)->whereNull('deleted_at'),
            ],
            // Required only when the driver has no linked login account yet.
            'password' => [
                'nullable',
                Rule::requiredIf($this->enablingLoginForNewUser($driver?->user_id)),
                'string',
                'min:6',
                'confirmed',
            ],
            'password_confirmation' => ['nullable', 'string', 'same:password'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email is already linked to another account.',
            'password.required' => 'Password is required when creating a new driver login.',
            'password.min' => 'Password must be at least 6 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }

    private function enablingLoginForNewUser(?int $userId): bool
    {
        return filter_var($this->input('enable_login'), FILTER_VALIDATE_BOOLEAN) && $userId === null;
    }
}
