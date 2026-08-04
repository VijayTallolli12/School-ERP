<?php

namespace App\Modules\Lifecycle\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PromoteStudentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('student_lifecycle.promote');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();

        return [
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', Rule::exists('students', 'id')->where('school_id', $schoolId)],
            'to_class_section_id' => ['required', 'integer', Rule::exists('class_section', 'id')->where('school_id', $schoolId)],
            'to_academic_year_id' => ['required', 'integer', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'roll_no' => ['nullable', 'string', 'max:30'],
            'roll_numbers' => ['nullable', 'array'],
        ];
    }
}
