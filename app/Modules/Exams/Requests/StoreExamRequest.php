<?php

namespace App\Modules\Exams\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('exams.create');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();

        return [
            'exam_name' => ['required', 'string', 'max:150'],
            'exam_type' => ['required', 'string', Rule::in(['Monthly', 'Quarterly', 'Half Yearly', 'Annual', 'Class Test', 'Practical'])],
            'academic_year_id' => ['required', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'class_section_id' => ['required', Rule::exists('class_section', 'id')->where('school_id', $schoolId)],
            'subject_id' => ['required', Rule::exists('subjects', 'id')->where('school_id', $schoolId)],
            'exam_date' => ['required', 'date'],
            'maximum_marks' => ['required', 'integer', 'min:1'],
            'pass_marks' => ['required', 'integer', 'min:0', 'lte:maximum_marks'],
            'status' => ['required', Rule::in(['scheduled', 'completed', 'canceled'])],
            'is_published' => ['nullable', 'boolean'],
        ];
    }
}
