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

interface PayrollRepositoryInterface
{
    public function departments(): Builder;
    public function designations(): Builder;
    public function salaryComponents(): Builder;
    public function payGrades(): Builder;
    public function salaryStructures(): Builder;
    public function payrollRuns(): Builder;
    public function payrollItems(int $runId): Builder;

    public function createDepartment(array $data): PayrollDepartment;
    public function updateDepartment(PayrollDepartment $department, array $data): PayrollDepartment;

    public function createDesignation(array $data): PayrollDesignation;
    public function updateDesignation(PayrollDesignation $designation, array $data): PayrollDesignation;

    public function createSalaryComponent(array $data): SalaryComponent;
    public function updateSalaryComponent(SalaryComponent $component, array $data): SalaryComponent;

    public function createPayGrade(array $data): PayGrade;
    public function updatePayGrade(PayGrade $payGrade, array $data): PayGrade;

    public function createSalaryStructure(array $data): EmployeeSalaryStructure;
    public function updateSalaryStructure(EmployeeSalaryStructure $salaryStructure, array $data): EmployeeSalaryStructure;

    public function createPayrollRun(array $data): PayrollRun;
    public function updatePayrollRun(PayrollRun $run, array $data): PayrollRun;
    public function createPayrollItems(iterable $items): void;
    public function deletePayrollItems(int $runId): void;
}
