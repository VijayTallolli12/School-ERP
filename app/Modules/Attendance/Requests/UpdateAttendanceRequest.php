<?php

namespace App\Modules\Attendance\Requests;

use App\Modules\Attendance\Models\Attendance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('attendance.update');
    }

    public function rules(): array
    {
        $statuses = array_keys(Attendance::getStatuses());

        return [
            'status' => ['required', Rule::in($statuses)],
            'remarks' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Attendance status is required',
        ];
    }
}
