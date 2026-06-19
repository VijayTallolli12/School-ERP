<?php

namespace App\Modules\Payroll\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSalaryComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('payroll.update');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();
        $component = $this->route('salary_component');

        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('salary_components')->where('school_id', $schoolId)->ignore($component?->id)],
            'name_display' => ['required', 'string', 'max:120'],
            'component_type' => ['required', Rule::in(['earning', 'deduction'])],
            'calculation_type' => ['required', Rule::in(['fixed', 'percentage'])],
            'value' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
