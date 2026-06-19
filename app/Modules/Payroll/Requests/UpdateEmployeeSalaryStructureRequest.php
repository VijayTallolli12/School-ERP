<?php

namespace App\Modules\Payroll\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeSalaryStructureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('payroll.update');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();

        return [
            'employee_id' => ['required', 'string', 'max:40'],
            'employee_type' => ['required', 'string', 'max:30'],
            'pay_grade_id' => ['nullable', 'integer', 'exists:pay_grades,id'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'total_ctc' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
