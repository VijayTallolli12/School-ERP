<?php

namespace App\Modules\Homework\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHomeworkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('homework.update');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();
        $classSectionId = $this->input('class_section_id');

        $rules = [
            'academic_year_id' => ['required', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'class_section_id' => ['required', Rule::exists('class_section', 'id')->where('school_id', $schoolId)],
            'subject_id' => [
                'required',
                Rule::exists('subjects', 'id')->where('school_id', $schoolId),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'assigned_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:assigned_date'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,zip', 'max:10240'],
            'remove_attachment' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];

        if ($classSectionId) {
            $classSection = \App\Modules\Academics\Models\ClassSection::find($classSectionId);
            if ($classSection) {
                $rules['subject_id'][] = Rule::exists('class_subjects', 'subject_id')
                    ->where('class_id', $classSection->class_id);
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'due_date.after_or_equal' => 'Due date cannot be before the assigned date.',
        ];
    }
}
