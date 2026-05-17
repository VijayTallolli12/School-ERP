<?php

namespace App\Modules\Exams\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExamResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('exams.update');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();
        $resultId = $this->route('result')?->id;

        return [
            'exam_id' => ['required', Rule::exists('exams', 'id')->where('school_id', $schoolId)],
            'student_id' => [
                'required',
                Rule::exists('students', 'id')->where('school_id', $schoolId),
                Rule::unique('exam_results')->where(fn ($query) => $query->where('exam_id', $this->input('exam_id')))->ignore($resultId),
            ],
            'marks_obtained' => ['required', 'integer', 'min:0'],
            'grade' => ['nullable', 'string', 'max:50'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
