<?php

namespace App\Modules\Attendance\Requests;

use App\Core\Tenant\SchoolContext;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Students\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class BulkMarkAttendanceRequest extends FormRequest
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
            'students' => ['required', 'array', 'min:1'],
            'students.*' => ['required', Rule::in($statuses)],
            'remarks' => ['nullable', 'array'],
            'remarks.*' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $classSectionId = (int) $this->input('class_section_id');
            $allowedIds = Student::query()
                ->whereHas('sessions', function ($q) use ($classSectionId): void {
                    $q->where('class_section_id', $classSectionId)
                        ->where('status', 'active');
                })
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->all();

            foreach (array_keys($this->input('students', [])) as $studentId) {
                if (! in_array((string) $studentId, $allowedIds, true)) {
                    $validator->errors()->add('students', 'One or more students are not in the selected class section.');

                    return;
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'class_section_id.required' => 'Class section is required',
            'academic_year_id.required' => 'Academic year is required',
            'attendance_date.required' => 'Attendance date is required',
            'students.required' => 'At least one student attendance must be marked',
            'students.min' => 'At least one student attendance must be marked',
        ];
    }
}
