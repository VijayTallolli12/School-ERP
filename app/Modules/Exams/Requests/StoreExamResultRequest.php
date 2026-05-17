<?php

namespace App\Modules\Exams\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExamResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('exams.update');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();

        return [
            'exam_id' => ['required', Rule::exists('exams', 'id')->where('school_id', $schoolId)],
            'student_id' => ['required', Rule::exists('students', 'id')->where('school_id', $schoolId)],
            'marks_obtained' => ['required', 'integer', 'min:0'],
            'grade' => ['nullable', 'string', 'max:50'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
