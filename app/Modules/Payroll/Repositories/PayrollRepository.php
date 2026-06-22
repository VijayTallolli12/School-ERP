<?php

namespace App\Modules\Payroll\Repositories;

use App\Modules\Payroll\Models\PayrollDepartment;
use App\Modules\Payroll\Models\PayrollDesignation;
use App\Modules\Payroll\Models\SalaryComponent;
use App\Modules\Payroll\Models\PayGrade;
use App\Modules\Payroll\Models\EmployeeSalaryStructure;
use App\Modules\Payroll\Models\PayrollRun;
use App\Modules\Payroll\Models\PayrollItem;
use App\Modules\Payroll\Models\EmployeePayslip;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PayrollRepository implements PayrollRepositoryInterface
{
    public function departments(): Builder
    {
        return PayrollDepartment::query()->withCount('designations')->orderBy('sort_order')->orderBy('name');
    }

    public function designations(): Builder
    {
        return PayrollDesignation::query()->with('department')->orderBy('name');
    }

    public function salaryComponents(): Builder
    {
        return SalaryComponent::query()->orderBy('sort_order')->orderBy('name');
    }

    public function payGrades(): Builder
    {
        return PayGrade::query()->orderBy('name');
    }

    public function salaryStructures(): Builder
    {
        return EmployeeSalaryStructure::query()->with(['payGrade', 'employee'])->latest();
    }

    public function payrollRuns(): Builder
    {
        return PayrollRun::query()->withCount('items')->latest();
    }

    public function payrollItems(int $runId): Builder
    {
        return PayrollItem::query()->where('payroll_run_id', $runId);
    }

    public function createDepartment(array $data): PayrollDepartment
    {
        return PayrollDepartment::query()->create($data);
    }

    public function updateDepartment(PayrollDepartment $department, array $data): PayrollDepartment
    {
        $department->fill($data)->save();
        return $department->refresh();
    }

    public function createDesignation(array $data): PayrollDesignation
    {
        return PayrollDesignation::query()->create($data);
    }

    public function updateDesignation(PayrollDesignation $designation, array $data): PayrollDesignation
    {
        $designation->fill($data)->save();
        return $designation->refresh();
    }

    public function createSalaryComponent(array $data): SalaryComponent
    {
        return SalaryComponent::query()->create($data);
    }

    public function updateSalaryComponent(SalaryComponent $component, array $data): SalaryComponent
    {
        $component->fill($data)->save();
        return $component->refresh();
    }

    public function createPayGrade(array $data): PayGrade
    {
        return PayGrade::query()->create($data);
    }

    public function updatePayGrade(PayGrade $payGrade, array $data): PayGrade
    {
        $payGrade->fill($data)->save();
        return $payGrade->refresh();
    }

    public function createSalaryStructure(array $data): EmployeeSalaryStructure
    {
        return EmployeeSalaryStructure::query()->create($data);
    }

    public function updateSalaryStructure(EmployeeSalaryStructure $salaryStructure, array $data): EmployeeSalaryStructure
    {
        $salaryStructure->fill($data)->save();
        return $salaryStructure->refresh();
    }

    public function createPayrollRun(array $data): PayrollRun
    {
        return PayrollRun::query()->create($data);
    }

    public function updatePayrollRun(PayrollRun $run, array $data): PayrollRun
    {
        $run->fill($data)->save();
        return $run->refresh();
    }

    public function createPayrollItems(iterable $items): void
    {
        foreach ($items as $item) {
            PayrollItem::query()->create($item);
        }
    }

    public function deletePayrollItems(int $runId): void
    {
        PayrollItem::query()->where('payroll_run_id', $runId)->delete();
    }

    // ─── Payslips ────────────────────────────────────────────────────────

    public function employeePayslips(?int $runId = null): Builder
    {
        $query = EmployeePayslip::query()->with(['payrollRun', 'payrollItem'])->latest();
        if ($runId) {
            $query->where('payroll_run_id', $runId);
        }
        return $query;
    }

    public function payslipHistory(): Builder
    {
        return EmployeePayslip::query()->with(['payrollRun', 'payrollItem'])->latest();
    }

    public function createPayslip(array $data): EmployeePayslip
    {
        return EmployeePayslip::query()->create($data);
    }

    public function getNextPayslipNumber(int $year, int $month): string
    {
        $prefix = sprintf('PS-%04d-%02d-', $year, $month);
        $last = EmployeePayslip::query()
            ->where('payslip_number', 'like', "$prefix%")
            ->orderBy('payslip_number', 'desc')
            ->value('payslip_number');

        if ($last) {
            $seq = (int) substr($last, -6) + 1;
        } else {
            $seq = 1;
        }

        return $prefix . str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
    }

    public function findPayslip(int $id): Model|EmployeePayslip|null
    {
        return EmployeePayslip::query()->with(['payrollRun', 'payrollItem'])->find($id);
    }

    public function payslipExists(int $runId, int $itemId): bool
    {
        return EmployeePayslip::query()
            ->where('payroll_run_id', $runId)
            ->where('payroll_item_id', $itemId)
            ->exists();
    }
}
