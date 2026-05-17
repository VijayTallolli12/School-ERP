<?php

namespace App\Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApiLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
            'school_id' => ['nullable', 'integer', 'exists:schools,id'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ];
    }
}
