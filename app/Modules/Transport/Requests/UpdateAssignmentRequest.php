<?php

namespace App\Modules\Transport\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('transport.update');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();
        $assignment = $this->route('assignment');

        return [
            'student_id' => ['required', 'integer', Rule::exists('students', 'id')->where('school_id', $schoolId), Rule::unique('transport_assignments')->ignore($assignment?->id)->where('school_id', $schoolId)],
            'route_id' => ['nullable', 'integer', Rule::exists('routes', 'id')->where('school_id', $schoolId)],
            'route_stop_id' => ['nullable', 'integer', Rule::exists('route_stops', 'id')->where('school_id', $schoolId)],
            'vehicle_id' => ['nullable', 'integer', Rule::exists('vehicles', 'id')->where('school_id', $schoolId)],
            'pickup_point' => ['nullable', 'string', 'max:255'],
            'monthly_fee' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
