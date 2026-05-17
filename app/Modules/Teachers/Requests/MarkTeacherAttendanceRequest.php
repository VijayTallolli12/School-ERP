<?php

namespace App\Modules\Teachers\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MarkTeacherAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('teachers.create');
    }

    public function rules(): array
    {
        return [
            'teacher_id' => ['required', 'integer', Rule::exists('teachers', 'id')],
            'attendance_date' => ['required', 'date', 'before_or_equal:today'],
            'status' => ['required', Rule::in(['present', 'absent', 'late', 'half_day', 'excused'])],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
