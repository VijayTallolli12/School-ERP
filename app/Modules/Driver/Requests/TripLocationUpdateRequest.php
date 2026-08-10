<?php

namespace App\Modules\Driver\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TripLocationUpdateRequest extends FormRequest
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
            // Batch form sent by the driver app's background location task:
            // { locations: [{ lat, lng, speed, heading, accuracy, timestamp }] }
            'locations' => ['sometimes', 'array', 'max:50'],
            'locations.*.lat' => ['required', 'numeric', 'between:-90,90'],
            'locations.*.lng' => ['required', 'numeric', 'between:-180,180'],
            'locations.*.speed' => ['nullable', 'numeric', 'min:0'],
            'locations.*.heading' => ['nullable', 'numeric', 'between:0,360'],
            'locations.*.accuracy' => ['nullable', 'numeric', 'min:0'],
            'locations.*.timestamp' => ['nullable', 'date'],
            // Single-point form (realtime): { lat, lng, ... } or { latitude, longitude, ... }
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'latitude' => ['required_without:locations', 'numeric', 'between:-90,90'],
            'longitude' => ['required_without:locations', 'numeric', 'between:-180,180'],
            'speed' => ['nullable', 'numeric', 'min:0'],
            'heading' => ['nullable', 'numeric', 'between:0,360'],
            'accuracy' => ['nullable', 'numeric', 'min:0'],
            'timestamp' => ['nullable', 'date'],
        ];
    }
}