<?php

namespace App\Modules\AiAgents\Agents;

use App\Core\Tenant\SchoolContext;
use App\Modules\Payroll\Models\EmployeeSalaryStructure;
use App\Modules\Payroll\Models\PayrollRun;
use App\Modules\Payroll\Models\SalaryComponent;
use App\Modules\Payroll\Services\PayrollService;
use Illuminate\Support\Facades\DB;

class PayrollAgent implements AgentInterface
{
    public function __construct(
        private readonly SchoolContext $schoolContext,
        private readonly PayrollService $payrollService,
    ) {}

    public function name(): string
    {
        return 'payroll';
    }

    public function description(): string
    {
        return 'Validates payroll readiness, generates payroll, locks run, creates payslips, and produces a summary report.';
    }

    public function permissions(): array
    {
        return ['payroll.view', 'payroll.process'];
    }

    public function config(): array
    {
        $months = [];
        foreach (range(1, 12) as $m) {
            $label = \Carbon\Carbon::createFromDate(null, $m, 1)->format('F');
            $months[] = ['value' => $m, 'label' => $label];
        }

        $currentYear = (int) now()->format('Y');
        $years = [];
        foreach (range($currentYear - 1, $currentYear + 1) as $y) {
            $years[] = ['value' => $y, 'label' => (string) $y];
        }

        return [
            'label' => 'Payroll Agent',
            'icon' => 'cash-banknote',
            'color' => 'success',
            'tags' => ['Payroll', 'Payslips', 'Processing'],
            'params' => [
                'month' => [
                    'label' => 'Payroll Month',
                    'type' => 'select',
                    'options' => $months,
                    'default' => (int) now()->format('n'),
                ],
                'year' => [
                    'label' => 'Payroll Year',
                    'type' => 'select',
                    'options' => $years,
                    'default' => $currentYear,
                ],
            ],
        ];
    }

    public function validateParams(array $params): array
    {
        return [
            'month' => (int) ($params['month'] ?? now()->format('n')),
            'year' => (int) ($params['year'] ?? now()->format('Y')),
        ];
    }

    public function preview(array $params): array
    {
        $month = $params['month'];
        $year = $params['year'];
        $schoolId = $this->schoolContext->id();

        $validation = $this->validateReadiness($schoolId, $month, $year);

        if (!$validation['ready']) {
            return [
                'month' => $month,
                'year' => $year,
                'ready' => false,
                'errors' => $validation['errors'],
                'warnings' => $validation['warnings'],
                'total_employees' => 0,
                'estimated_gross' => 0,
                'estimated_deductions' => 0,
                'estimated_net' => 0,
            ];
        }

        $estimates = $this->calculateEstimates($schoolId);

        return [
            'month' => $month,
            'year' => $year,
            'ready' => true,
            'total_employees' => $estimates['employee_count'],
            'estimated_gross' => $estimates['gross'],
            'estimated_deductions' => $estimates['deductions'],
            'estimated_net' => $estimates['net'],
            'active_structures' => $estimates['employee_count'],
            'active_components' => $estimates['component_count'],
        ];
    }

    public function execute(array $params): array
    {
        $month = $params['month'];
        $year = $params['year'];
        $schoolId = $this->schoolContext->id();

        $validation = $this->validateReadiness($schoolId, $month, $year);

        if (!$validation['ready']) {
            return [
                'month' => $month,
                'year' => $year,
                'success' => false,
                'errors' => $validation['errors'],
                'records_processed' => 0,
            ];
        }

        DB::beginTransaction();
        try {
            $payrollRun = $this->payrollService->generatePayroll($month, $year, 'Generated via Payroll Agent');

            $lockedRun = $this->payrollService->lockRun($payrollRun, 'Locked via Payroll Agent');

            $payslips = $this->payrollService->bulkGeneratePayslips($lockedRun->id);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $itemCount = $lockedRun->items()->count();
        $summary = $lockedRun->items()->selectRaw(
            'COALESCE(SUM(gross_salary), 0) as total_gross, COALESCE(SUM(total_deductions), 0) as total_deductions, COALESCE(SUM(net_salary), 0) as total_net'
        )->first();

        return [
            'month' => $month,
            'year' => $year,
            'success' => true,
            'payroll_run_id' => $lockedRun->id,
            'total_employees' => $itemCount,
            'total_gross' => round((float) ($summary?->total_gross ?? 0), 2),
            'total_deductions' => round((float) ($summary?->total_deductions ?? 0), 2),
            'total_net' => round((float) ($summary?->total_net ?? 0), 2),
            'payslips_generated' => count($payslips),
            'records_processed' => $itemCount,
        ];
    }

    private function validateReadiness(int $schoolId, int $month, int $year): array
    {
        $errors = [];
        $warnings = [];

        $structureCount = EmployeeSalaryStructure::query()
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->count();

        if ($structureCount === 0) {
            $errors[] = 'No active salary structures found.';
        }

        $componentCount = SalaryComponent::query()
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->count();

        if ($componentCount === 0) {
            $errors[] = 'No active salary components found.';
        }

        $existingRun = PayrollRun::query()
            ->where('school_id', $schoolId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if ($existingRun) {
            $errors[] = "A payroll run already exists for {$month}/{$year} (Status: {$existingRun->status}).";
        }

        return [
            'ready' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'structure_count' => $structureCount,
            'component_count' => $componentCount,
        ];
    }

    private function calculateEstimates(int $schoolId): array
    {
        $structures = EmployeeSalaryStructure::query()
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->get();

        $components = SalaryComponent::query()
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->get();

        $totalGross = 0;
        $totalDeductions = 0;

        foreach ($structures as $structure) {
            $monthlyCtc = $structure->total_ctc / 12;

            foreach ($components as $component) {
                $amount = match ($component->calculation_type) {
                    'fixed' => (float) $component->value,
                    'percentage' => ((float) $component->value / 100) * $monthlyCtc,
                    default => 0,
                };

                if ($component->component_type === 'earning') {
                    $totalGross += $amount;
                } else {
                    $totalDeductions += $amount;
                }
            }
        }

        return [
            'employee_count' => $structures->count(),
            'component_count' => $components->count(),
            'gross' => round($totalGross, 2),
            'deductions' => round($totalDeductions, 2),
            'net' => round(max($totalGross - $totalDeductions, 0), 2),
        ];
    }
}
