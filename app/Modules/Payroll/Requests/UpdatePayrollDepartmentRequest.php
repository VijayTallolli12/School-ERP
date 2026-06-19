<?php

namespace App\Modules\Payroll\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePayrollDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('payroll.update');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();
        $department = $this->route('department');

        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('payroll_departments')->where('school_id', $schoolId)->ignore($department?->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
