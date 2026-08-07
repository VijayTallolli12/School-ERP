<?php

namespace App\Modules\Driver\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDriverLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && $this->user()->hasRole('Driver')
            && $this->user()->can('transport.update');
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'speed' => ['nullable', 'numeric', 'min:0'],
            'heading' => ['nullable', 'numeric', 'between:0,360'],
            'captured_at' => ['nullable', 'date'],
            'trip_id' => ['nullable', 'integer', 'exists:trips,id'],
        ];
    }
}
