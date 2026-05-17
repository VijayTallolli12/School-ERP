<?php

namespace App\Modules\Teachers\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('teachers.create');
    }

    public function rules(): array
    {
        return [
            'teacher_id' => ['required', 'integer', Rule::exists('teachers', 'id')],
            'leave_type' => ['required', Rule::in(['sick', 'casual', 'personal', 'maternity', 'other'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
