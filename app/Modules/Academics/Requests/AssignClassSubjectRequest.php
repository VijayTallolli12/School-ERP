<?php

namespace App\Modules\Academics\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignClassSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('academics.create');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();

        return [
            'academic_year_id' => ['required', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'class_id' => ['required', Rule::exists('classes', 'id')->where('school_id', $schoolId)],
            'subject_id' => [
                'required',
                Rule::exists('subjects', 'id')->where('school_id', $schoolId),
                Rule::unique('class_subjects')->where(fn ($query) => $query
                    ->where('school_id', $schoolId)
                    ->where('academic_year_id', $this->input('academic_year_id'))
                    ->where('class_id', $this->input('class_id'))),
            ],
            'teacher_id' => ['nullable', 'exists:users,id'],
            'weekly_periods' => ['nullable', 'integer', 'min:0', 'max:80'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
