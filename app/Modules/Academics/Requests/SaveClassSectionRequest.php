<?php

namespace App\Modules\Academics\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveClassSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can($this->route('classSection') ? 'academics.update' : 'academics.create');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();

        return [
            'class_id' => ['required', Rule::exists('classes', 'id')->where('school_id', $schoolId)],
            'section_id' => ['required', Rule::exists('sections', 'id')->where('school_id', $schoolId)],
            'class_teacher_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
