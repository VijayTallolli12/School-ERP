<?php

namespace App\Modules\Payroll\Repositories;

use App\Modules\Payroll\Models\PayrollDepartment;
use App\Modules\Payroll\Models\PayrollDesignation;
use App\Modules\Payroll\Models\SalaryComponent;
use App\Modules\Payroll\Models\PayGrade;
use App\Modules\Payroll\Models\EmployeeSalaryStructure;
use App\Modules\Payroll\Models\PayrollRun;
use App\Modules\Payroll\Models\PayrollItem;
use Illuminate\Database\Eloquent\Builder;

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
}
