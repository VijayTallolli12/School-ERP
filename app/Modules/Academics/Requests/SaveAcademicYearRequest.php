<?php

namespace App\Modules\Academics\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can($this->route('academicYear') ? 'academics.update' : 'academics.create');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();

        return [
            'name' => ['required', 'string', 'max:60', Rule::unique('academic_years')->ignore($this->route('academicYear')?->id)->where('school_id', $schoolId)],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after:starts_on'],
            'is_active' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['active', 'inactive', 'archived'])],
        ];
    }
}
