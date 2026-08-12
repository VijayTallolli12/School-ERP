<?php

namespace App\Modules\Dashboard\Services\Builders;

use App\Modules\Payroll\Models\EmployeePayslip;
use App\Modules\Payroll\Models\PayrollRun;
use Illuminate\Support\Facades\Cache;

class PayrollManagerDashboardBuilder extends BaseDashboardBuilder
{
    public function getRoleName(): string
    {
        return 'Payroll Manager';
    }

    public function getLayout(): string
    {
        return 'admin';
    }

    protected function buildStatCards(): array
    {
        $latestRun = Cache::remember("dashboard.payroll.latest_run.{$this->schoolId}", 300, fn () => PayrollRun::query()->latest('generated_at')->first());
        $runsThisMonth = Cache::remember("dashboard.payroll.month.{$this->schoolId}", 300, fn () => PayrollRun::query()
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->count());

        $payslipCount = Cache::remember("dashboard.payroll.payslips.{$this->schoolId}", 300, fn () => EmployeePayslip::query()->count());
        $lockedRuns = Cache::remember("dashboard.payroll.locked.{$this->schoolId}", 300, fn () => PayrollRun::query()->where('status', 'locked')->count());

        return [
            $this->statCard('Latest Payroll Run', $latestRun?->month_name.' '.$latestRun?->year ?? 'None', 'calendar-clock', 'primary', null, null, route('admin.payroll.index')),
            $this->statCard('Runs This Month', $runsThisMonth, 'calendar-event', 'info'),
            $this->statCard('Payslips Generated', $payslipCount, 'receipt-2', 'success', null, null, route('admin.payroll.index')),
            $this->statCard('Locked Runs', $lockedRuns, 'lock', 'warning', null, null, route('admin.payroll.index')),
        ];
    }

    protected function buildWidgets(): array
    {
        return [];
    }

    protected function buildQuickActions(): array
    {
        return [
            $this->quickAction('Payroll Dashboard', route('admin.payroll.index'), 'cash', 'primary', 'payroll.view'),
            $this->quickAction('Payroll Reports', route('admin.payroll.reports.index'), 'chart-bar', 'info', 'payroll.view'),
        ];
    }

    protected function buildCharts(): array
    {
        return [];
    }
}
