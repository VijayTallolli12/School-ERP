<?php

namespace App\Modules\Transport\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRouteStopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('transport.create');
    }

    public function rules(): array
    {
        return [
            'route_id' => ['required', 'integer', 'exists:routes,id'],
            'stop_name' => ['required', 'string', 'max:255'],
            'pickup_time' => ['nullable', 'date_format:H:i'],
            'drop_time' => ['nullable', 'date_format:H:i'],
            'sequence' => ['required', 'integer', 'min:0', 'max:9999'],
        ];
    }
}
