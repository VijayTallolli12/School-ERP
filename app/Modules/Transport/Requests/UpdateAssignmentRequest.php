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
            'student_id' => ['required', 'integer', 'exists:students,id', Rule::unique('transport_assignments')->ignore($assignment?->id)->where('school_id', $schoolId)],
            'route_id' => ['nullable', 'integer', 'exists:routes,id'],
            'route_stop_id' => ['nullable', 'integer', 'exists:route_stops,id'],
            'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
            'pickup_point' => ['nullable', 'string', 'max:255'],
            'monthly_fee' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
