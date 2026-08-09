<?php

namespace App\Modules\Transport\Requests;

use App\Modules\Transport\Models\Driver;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetDriverPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('transport.update');
    }

    public function rules(): array
    {
        return [
            'password' => ['required', Password::defaults(), 'confirmed'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            /** @var Driver $driver */
            $driver = $this->route('driver');
            if (! $driver->user) {
                $validator->errors()->add('driver', 'This driver does not have a login account yet. Enable Driver Login to create one.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }
}
