<?php

namespace App\Modules\Transport\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRouteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('transport.update');
    }

    public function rules(): array
    {
        return [
            'route_name' => ['required', 'string', 'max:120'],
            'start_point' => ['required', 'string', 'max:255'],
            'end_point' => ['required', 'string', 'max:255'],
            'distance' => ['nullable', 'numeric', 'min:0'],
            'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
            'driver_id' => ['nullable', 'integer', 'exists:drivers,id'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
