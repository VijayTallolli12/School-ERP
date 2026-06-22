<?php

namespace App\Modules\Payroll\Requests;

use App\Modules\Payroll\Models\PayrollRun;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class BulkGeneratePayslipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('payroll.payslip.generate');
    }

    public function rules(): array
    {
        return [
            'payroll_run_id' => 'required|exists:payroll_runs,id',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $runId = $this->input('payroll_run_id');

            $run = PayrollRun::query()->find($runId);
            if (! $run || ! $run->isLocked()) {
                $validator->errors()->add('payroll_run_id', 'Payslips can only be generated from locked payroll runs.');
            }
        });
    }
}
