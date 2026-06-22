<?php

namespace App\Modules\Payroll\Requests;

use App\Modules\Payroll\Models\PayrollRun;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class GeneratePayslipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('payroll.payslip.generate');
    }

    public function rules(): array
    {
        return [
            'payroll_run_id' => 'required|exists:payroll_runs,id',
            'payroll_item_id' => 'required|exists:payroll_items,id',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $runId = $this->input('payroll_run_id');
            $itemId = $this->input('payroll_item_id');

            $run = PayrollRun::query()->find($runId);
            if (! $run || ! $run->isLocked()) {
                $validator->errors()->add('payroll_run_id', 'Payslips can only be generated from locked payroll runs.');
                return;
            }

            $exists = \App\Modules\Payroll\Models\EmployeePayslip::query()
                ->where('payroll_run_id', $runId)
                ->where('payroll_item_id', $itemId)
                ->exists();

            if ($exists) {
                $validator->errors()->add('payroll_item_id', 'A payslip has already been generated for this employee in this run.');
            }
        });
    }
}
