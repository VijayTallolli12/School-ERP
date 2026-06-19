<?php

namespace App\Modules\Transport\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('transport.create');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();

        return [
            'vehicle_number' => ['required', 'string', 'max:40', Rule::unique('vehicles')->where('school_id', $schoolId)],
            'vehicle_name' => ['required', 'string', 'max:120'],
            'vehicle_type' => ['required', Rule::in(['bus', 'van', 'car', 'other'])],
            'capacity' => ['required', 'integer', 'min:1', 'max:9999'],
            'driver_id' => ['nullable', 'integer', 'exists:drivers,id'],
            'attendant' => ['nullable', 'string', 'max:120'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
