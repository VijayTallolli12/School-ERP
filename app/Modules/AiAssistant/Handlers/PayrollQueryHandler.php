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

    public function lockedRuns(): string
    {
        $schoolId = $this->schoolContext->id();

        $count = PayrollRun::query()
            ->where('school_id', $schoolId)
            ->where('status', 'locked')
            ->count();

        return "Locked payroll runs: {$count}";
    }

    public function highestSalaryEmployees(): string
    {
        $schoolId = $this->schoolContext->id();

        $items = PayrollItem::query()
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->with('payrollRun')
            ->orderByDesc('net_salary')
            ->limit(10)
            ->get();

        if ($items->isEmpty()) {
            return 'No payroll data found.';
        }

        $lines = [];
        foreach ($items as $i => $item) {
            $employee = $item->employee;
            $name = $employee ? (trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')) ?: 'Unknown') : 'Unknown';
            $run = $item->payrollRun;
            $period = $run ? "{$run->monthName} {$run->year}" : 'N/A';
            $lines[] = ($i + 1) . ". {$name} - \u{20B9}" . number_format($item->net_salary, 2) . " ({$period})";
        }

        return "Highest salary employees:\n" . implode("\n", $lines);
    }

    public function generatedThisMonth(): string
    {
        $schoolId = $this->schoolContext->id();
        $now = Carbon::now();

        $count = PayrollRun::query()
            ->where('school_id', $schoolId)
            ->where('month', $now->month)
            ->where('year', $now->year)
            ->count();

        return "Payroll runs generated this month ({$now->format('F Y')}): {$count}";
    }
}
