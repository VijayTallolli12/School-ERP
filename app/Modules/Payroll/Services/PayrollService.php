<?php

namespace App\Modules\Payroll\Services;

use App\Core\Tenant\SchoolContext;
use App\Modules\Payroll\Models\PayrollDepartment;
use App\Modules\Payroll\Models\PayrollDesignation;
use App\Modules\Payroll\Models\SalaryComponent;
use App\Modules\Payroll\Models\PayGrade;
use App\Modules\Payroll\Models\EmployeeSalaryStructure;
use App\Modules\Payroll\Repositories\PayrollRepositoryInterface;

class PayrollService
{
    public function __construct(private readonly PayrollRepositoryInterface $payroll)
    {
    }

    public function createDepartment(array $data): PayrollDepartment
    {
        $data['school_id'] = app(SchoolContext::class)->id();
        $department = $this->payroll->createDepartment($data);
        activity()->causedBy(auth()->user())->performedOn($department)->event('created')->log('Payroll department created');

        return $department;
    }

    public function updateDepartment(PayrollDepartment $department, array $data): PayrollDepartment
    {
        $department = $this->payroll->updateDepartment($department, $data);
        activity()->causedBy(auth()->user())->performedOn($department)->event('updated')->log('Payroll department updated');

        return $department;
    }

    public function createDesignation(array $data): PayrollDesignation
    {
        $data['school_id'] = app(SchoolContext::class)->id();
        $designation = $this->payroll->createDesignation($data);
        activity()->causedBy(auth()->user())->performedOn($designation)->event('created')->log('Payroll designation created');

        return $designation;
    }

    public function updateDesignation(PayrollDesignation $designation, array $data): PayrollDesignation
    {
        $designation = $this->payroll->updateDesignation($designation, $data);
        activity()->causedBy(auth()->user())->performedOn($designation)->event('updated')->log('Payroll designation updated');

        return $designation;
    }

    public function createSalaryComponent(array $data): SalaryComponent
    {
        $data['school_id'] = app(SchoolContext::class)->id();
        $component = $this->payroll->createSalaryComponent($data);
        activity()->causedBy(auth()->user())->performedOn($component)->event('created')->log('Salary component created');

        return $component;
    }

    public function updateSalaryComponent(SalaryComponent $component, array $data): SalaryComponent
    {
        $component = $this->payroll->updateSalaryComponent($component, $data);
        activity()->causedBy(auth()->user())->performedOn($component)->event('updated')->log('Salary component updated');

        return $component;
    }

    public function createPayGrade(array $data): PayGrade
    {
        $data['school_id'] = app(SchoolContext::class)->id();
        $payGrade = $this->payroll->createPayGrade($data);
        activity()->causedBy(auth()->user())->performedOn($payGrade)->event('created')->log('Pay grade created');

        return $payGrade;
    }

    public function updatePayGrade(PayGrade $payGrade, array $data): PayGrade
    {
        $payGrade = $this->payroll->updatePayGrade($payGrade, $data);
        activity()->causedBy(auth()->user())->performedOn($payGrade)->event('updated')->log('Pay grade updated');

        return $payGrade;
    }

    public function createSalaryStructure(array $data): EmployeeSalaryStructure
    {
        $data['school_id'] = app(SchoolContext::class)->id();
        $structure = $this->payroll->createSalaryStructure($data);
        activity()->causedBy(auth()->user())->performedOn($structure)->event('created')->log('Employee salary structure created');

        return $structure;
    }

    public function updateSalaryStructure(EmployeeSalaryStructure $salaryStructure, array $data): EmployeeSalaryStructure
    {
        $salaryStructure = $this->payroll->updateSalaryStructure($salaryStructure, $data);
        activity()->causedBy(auth()->user())->performedOn($salaryStructure)->event('updated')->log('Employee salary structure updated');

        return $salaryStructure;
    }
}
