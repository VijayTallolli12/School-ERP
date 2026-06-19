<?php

namespace App\Modules\Payroll\Requests;

use App\Core\Tenant\SchoolContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePayGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('payroll.create');
    }

    public function rules(): array
    {
        $schoolId = app(SchoolContext::class)->id();

        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('pay_grades')->where('school_id', $schoolId)],
            'description' => ['nullable', 'string', 'max:2000'],
            'min_salary' => ['nullable', 'numeric', 'min:0'],
            'max_salary' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
