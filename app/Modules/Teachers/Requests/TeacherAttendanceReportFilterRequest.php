<?php

namespace App\Modules\Teachers\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeacherAttendanceReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('teachers.reports');
    }

    public function rules(): array
    {
        return [
            'teacher_id' => ['nullable', 'integer', Rule::exists('teachers', 'id')],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ];
    }

    public function filterPayload(): array
    {
        return [
            'teacher_id' => $this->input('teacher_id'),
            'from_date' => $this->input('from_date'),
            'to_date' => $this->input('to_date'),
        ];
    }
}
