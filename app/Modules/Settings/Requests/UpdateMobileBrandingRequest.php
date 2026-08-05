<?php

namespace App\Modules\Settings\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMobileBrandingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.update');
    }

    public function rules(): array
    {
        return [
            'brand.primary_color' => ['required', 'string', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'brand.secondary_color' => ['required', 'string', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'brand.primary_color.regex' => 'Primary color must be a valid hex color (e.g. #2563eb).',
            'brand.secondary_color.regex' => 'Secondary color must be a valid hex color (e.g. #64748b).',
        ];
    }
}
