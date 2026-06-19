<?php

namespace App\Modules\Payroll\Requests;

use App\Core\Tenant\SchoolContext;
use App\Modules\Payroll\Models\PayrollRun;
use Illuminate\Foundation\Http\FormRequest;

class GeneratePayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('payroll.process');
    }

    public function rules(): array
    {
        return [
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:2020', 'max:2099'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator): void {
            $schoolId = app(SchoolContext::class)->id();
            $exists = PayrollRun::query()
                ->where('school_id', $schoolId)
                ->where('month', $this->month)
                ->where('year', $this->year)
                ->exists();

            if ($exists) {
                $validator->errors()->add('month', 'A payroll run already exists for this period.');
            }
        });
    }
}
