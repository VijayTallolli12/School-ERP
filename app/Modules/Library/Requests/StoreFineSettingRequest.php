<?php

namespace App\Modules\Library\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFineSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('library.create');
    }

    public function rules(): array
    {
        return [
            'fine_per_day' => ['required', 'numeric', 'min:0'],
            'max_fine' => ['nullable', 'numeric', 'min:0'],
            'grace_period_days' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
