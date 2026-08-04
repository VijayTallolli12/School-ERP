<?php

namespace App\Modules\Transport\Requests;

use App\Core\Tenant\SchoolContext;
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
        $schoolId = app(SchoolContext::class)->id();

        return [
            'route_name' => ['required', 'string', 'max:120'],
            'start_point' => ['required', 'string', 'max:255'],
            'end_point' => ['required', 'string', 'max:255'],
            'distance' => ['nullable', 'numeric', 'min:0'],
            'vehicle_id' => ['nullable', 'integer', Rule::exists('vehicles', 'id')->where('school_id', $schoolId)],
            'driver_id' => ['nullable', 'integer', Rule::exists('drivers', 'id')->where('school_id', $schoolId)],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
