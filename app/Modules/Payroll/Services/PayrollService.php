<?php

namespace App\Modules\Payroll\Services;

use App\Core\Tenant\SchoolContext;
use App\Modules\Payroll\Models\PayrollDepartment;
use App\Modules\Payroll\Models\PayrollDesignation;
use App\Modules\Payroll\Models\SalaryComponent;
use App\Modules\Payroll\Models\PayGrade;
use App\Modules\Payroll\Models\EmployeeSalaryStructure;
use App\Modules\Payroll\Models\PayrollRun;
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

    // ─── Payroll Processing ───────────────────────────────────────────────

    public function generatePayroll(int $month, int $year, ?string $notes = null): PayrollRun
    {
        $schoolId = app(SchoolContext::class)->id();

        $structures = EmployeeSalaryStructure::query()
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->get();

        $components = SalaryComponent::query()
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->get();

        $run = $this->payroll->createPayrollRun([
            'school_id' => $schoolId,
            'month' => $month,
            'year' => $year,
            'status' => 'draft',
            'generated_by' => auth()->id(),
            'generated_at' => now(),
            'notes' => $notes,
        ]);

        $items = [];
        foreach ($structures as $structure) {
            $monthlyCtc = $structure->total_ctc / 12;

            $gross = 0;
            $deductions = 0;

            foreach ($components as $component) {
                $amount = match ($component->calculation_type) {
                    'fixed' => (float) $component->value,
                    'percentage' => ((float) $component->value / 100) * $monthlyCtc,
                    default => 0,
                };

                if ($component->component_type === 'earning') {
                    $gross += $amount;
                } else {
                    $deductions += $amount;
                }
            }

            $net = $gross - $deductions;

            $items[] = [
                'school_id' => $schoolId,
                'payroll_run_id' => $run->id,
                'employee_type' => $structure->employee_type,
                'employee_id' => $structure->employee_id,
                'gross_salary' => round($gross, 2),
                'total_deductions' => round($deductions, 2),
                'net_salary' => round(max($net, 0), 2),
                'status' => 'active',
            ];
        }

        $this->payroll->createPayrollItems($items);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($run)
            ->event('generated')
            ->log("Payroll generated for {$month}/{$year}");

        return $run->loadCount('items');
    }

    public function lockRun(PayrollRun $run, ?string $notes = null): PayrollRun
    {
        if ($run->isLocked()) {
            throw new \RuntimeException('Payroll run is already locked.');
        }

        $run = $this->payroll->updatePayrollRun($run, [
            'status' => 'locked',
            'notes' => $notes ?? $run->notes,
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($run)
            ->event('locked')
            ->log("Payroll run for {$run->month}/{$run->year} locked");

        return $run;
    }
}
