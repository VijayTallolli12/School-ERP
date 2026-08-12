<?php

namespace App\Modules\AiAssistant\Handlers;

use App\Core\Tenant\SchoolContext;
use App\Modules\Payroll\Models\PayrollItem;
use App\Modules\Payroll\Models\PayrollRun;
use Illuminate\Support\Carbon;

class PayrollQueryHandler
{
    public function __construct(
        private readonly SchoolContext $schoolContext
    ) {}

    public function latestRun(): string
    {
        $schoolId = $this->schoolContext->id();

        $run = PayrollRun::query()
            ->where('school_id', $schoolId)
            ->latest('generated_at')
            ->first();

        if (!$run) {
            return 'No payroll runs found.';
        }

        $employeeCount = PayrollItem::query()
            ->where('payroll_run_id', $run->id)
            ->count();

        $totalNet = (float) PayrollItem::query()
            ->where('payroll_run_id', $run->id)
            ->sum('net_salary');

        $status = $run->isLocked() ? 'Locked' : ($run->isDraft() ? 'Draft' : ucfirst($run->status));

        return "Latest payroll run: {$run->monthName} {$run->year} (Status: {$status}) - {$employeeCount} employees, Total net: \u{20B9}" . number_format($totalNet, 2);
    }

    public function latestRunStructured(array $parameters): array
    {
        $schoolId = $this->schoolContext->id();

        $run = PayrollRun::query()
            ->where('school_id', $schoolId)
            ->latest('generated_at')
            ->first();

        if (!$run) {
            return [
                'count' => 0,
                'record' => null,
                'records' => [],
                'summary' => null,
            ];
        }

        $employeeCount = PayrollItem::query()
            ->where('payroll_run_id', $run->id)
            ->count();

        $totalNet = (float) PayrollItem::query()
            ->where('payroll_run_id', $run->id)
            ->sum('net_salary');

        $record = [
            'id' => $run->id,
            'month' => $run->monthName,
            'year' => $run->year,
            'status' => $run->status,
            'employees' => $employeeCount,
            'total_net' => round($totalNet, 2),
            'generated_at' => $run->generated_at?->toDateTimeString(),
        ];

        return [
            'count' => 1,
            'record' => $record,
            'records' => [$record],
            'summary' => $record,
        ];
    }

    public function summary(array $parameters): array
    {
        $schoolId = $this->schoolContext->id();

        $run = PayrollRun::query()
            ->where('school_id', $schoolId)
            ->latest('generated_at')
            ->first();

        if (!$run) {
            return [
                'count' => 0,
                'records' => [],
                'summary' => null,
            ];
        }

        $employeeCount = PayrollItem::query()
            ->where('payroll_run_id', $run->id)
            ->count();

        $totalNet = (float) PayrollItem::query()
            ->where('payroll_run_id', $run->id)
            ->sum('net_salary');

        $summary = [
            'period' => "{$run->monthName} {$run->year}",
            'status' => ucfirst($run->status),
            'total_employees' => $employeeCount,
            'net_payroll' => round($totalNet, 2),
        ];

        return [
            'count' => 1,
            'records' => [],
            'summary' => $summary,
        ];
    }

    public function lockedRuns(array $parameters): array
    {
        $schoolId = $this->schoolContext->id();

        $count = PayrollRun::query()
            ->where('school_id', $schoolId)
            ->where('status', 'locked')
            ->count();

        return [
            'count' => $count,
            'records' => [],
            'summary' => null,
        ];
    }

    public function highestSalary(array $parameters): array
    {
        $schoolId = $this->schoolContext->id();
        $limit = max(1, min((int) ($parameters['limit'] ?? 10), 50));

        $items = PayrollItem::query()
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->with(['payrollRun', 'employee'])
            ->orderByDesc('net_salary')
            ->limit($limit)
            ->get();

        return [
            'count' => $items->count(),
            'records' => $items->map(function (PayrollItem $item) {
                $employee = $item->employee;
                $name = $employee
                    ? (trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')) ?: 'Unknown')
                    : 'Unknown';
                $run = $item->payrollRun;
                $period = $run ? "{$run->monthName} {$run->year}" : 'N/A';

                return [
                    'employee' => $name,
                    'net_salary' => round((float) $item->net_salary, 2),
                    'period' => $period,
                ];
            })->all(),
            'summary' => null,
        ];
    }

    public function generatedThisMonth(array $parameters): array
    {
        $schoolId = $this->schoolContext->id();
        $now = Carbon::now();

        $count = PayrollRun::query()
            ->where('school_id', $schoolId)
            ->where('month', $now->month)
            ->where('year', $now->year)
            ->count();

        return [
            'count' => $count,
            'records' => [],
            'summary' => null,
        ];
    }
}
