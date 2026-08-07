<?php

namespace App\Modules\Driver\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SosAlertRequest extends FormRequest
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
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'message' => ['nullable', 'string', 'max:500'],
            'trip_id' => ['nullable', 'integer', 'exists:trips,id'],
        ];
    }
}
