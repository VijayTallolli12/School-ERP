<?php

namespace App\Modules\Attendance\Requests;

use App\Core\Tenant\SchoolContext;
use App\Modules\Attendance\Models\Attendance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttendanceReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('attendance.reports');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();
        $statuses = array_keys(Attendance::getStatuses());

        return [
            'class_section_id' => [
                'nullable',
                'integer',
                Rule::exists('class_section', 'id')->where('school_id', $schoolId),
            ],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'status' => ['nullable', Rule::in($statuses)],
            'academic_year_id' => [
                'nullable',
                'integer',
                Rule::exists('academic_years', 'id')->where('school_id', $schoolId),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filterPayload(): array
    {
        return array_filter($this->validated(), fn ($v) => $v !== null && $v !== '');
    }
}
