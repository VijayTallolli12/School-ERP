<?php

namespace App\Modules\Lifecycle\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IssueTcRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('student_lifecycle.tc');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();

        return [
            'student_id' => ['required', 'integer', Rule::exists('students', 'id')->where('school_id', $schoolId)],
            'tc_no' => ['nullable', 'string', 'max:50'],
            'transferred_on' => ['nullable', 'date'],
            'tc_issued_on' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:2000'],
            'conduct' => ['nullable', 'string', 'max:100'],
            'destination_school' => ['nullable', 'string', 'max:255'],
        ];
    }
}
