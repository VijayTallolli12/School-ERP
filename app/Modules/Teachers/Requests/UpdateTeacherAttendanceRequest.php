<?php

namespace App\Modules\Teachers\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeacherAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('teachers.update');
    }

    public function rules(): array
    {
        return [
            'attendance_date' => ['required', 'date', 'before_or_equal:today'],
            'status' => ['required', Rule::in(['present', 'absent', 'late', 'half_day', 'excused'])],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
