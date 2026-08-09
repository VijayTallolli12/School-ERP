<?php

namespace App\Modules\Transport\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('transport.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:new,acknowledged,resolved'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}