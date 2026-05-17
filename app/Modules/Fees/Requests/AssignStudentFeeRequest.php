<?php

namespace App\Modules\Fees\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignStudentFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('fees.create');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();

        return [
            'student_id' => ['required', Rule::exists('students', 'id')->where('school_id', $schoolId)],
            'academic_year_id' => ['required', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'fee_structure_id' => ['required', Rule::exists('fee_structures', 'id')->where('school_id', $schoolId)],
            'default_due_date' => ['nullable', 'date'],
        ];
    }
}
