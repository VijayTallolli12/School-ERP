<?php

namespace App\Modules\Payroll\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePayGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('payroll.update');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();
        $payGrade = $this->route('pay_grade');

        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('pay_grades')->where('school_id', $schoolId)->ignore($payGrade?->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'min_salary' => ['nullable', 'numeric', 'min:0'],
            'max_salary' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
