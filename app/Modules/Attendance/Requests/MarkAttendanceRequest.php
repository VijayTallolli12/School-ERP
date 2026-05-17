<?php

namespace App\Modules\Attendance\Requests;

use App\Core\Tenant\SchoolContext;
use App\Modules\Attendance\Models\Attendance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MarkAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('attendance.create');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();
        $statuses = array_keys(Attendance::getStatuses());

        return [
            'student_id' => [
                'required',
                'integer',
                Rule::exists('students', 'id')->where('school_id', $schoolId),
            ],
            'class_section_id' => [
                'required',
                'integer',
                Rule::exists('class_section', 'id')->where('school_id', $schoolId),
            ],
            'academic_year_id' => [
                'required',
                'integer',
                Rule::exists('academic_years', 'id')->where('school_id', $schoolId),
            ],
            'attendance_date' => ['required', 'date'],
            'status' => ['required', Rule::in($statuses)],
            'remarks' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'Student is required',
            'status.required' => 'Attendance status is required',
            'attendance_date.required' => 'Attendance date is required',
        ];
    }
}
