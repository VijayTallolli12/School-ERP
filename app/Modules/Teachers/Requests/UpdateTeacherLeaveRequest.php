<?php

namespace App\Modules\Teachers\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeacherLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('teachers.update');
    }

    public function rules(): array
    {
        return [
            'leave_type' => ['required', Rule::in(['sick', 'casual', 'personal', 'maternity', 'other'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
