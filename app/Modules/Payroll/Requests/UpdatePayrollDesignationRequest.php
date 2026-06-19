<?php

namespace App\Modules\Payroll\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePayrollDesignationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('payroll.update');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();
        $designation = $this->route('designation');

        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('payroll_designations')->where('school_id', $schoolId)->ignore($designation?->id)],
            'department_id' => ['nullable', 'integer', 'exists:payroll_departments,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
