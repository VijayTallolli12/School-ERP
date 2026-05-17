<?php

namespace App\Modules\Academics\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can($this->route('subject') ? 'academics.update' : 'academics.create');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();

        return [
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:40', Rule::unique('subjects')->ignore($this->route('subject')?->id)->where('school_id', $schoolId)],
            'type' => ['required', Rule::in(['core', 'elective', 'optional', 'co_scholastic'])],
            'credit_hours' => ['nullable', 'integer', 'min:0', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
