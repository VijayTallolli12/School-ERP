<?php

namespace App\Modules\Dashboard\Services\Builders;

use App\Modules\Dashboard\Services\DataCollectors\FeeCollector;
use App\Modules\Fees\Models\FeePayment;
use App\Modules\Fees\Models\StudentFee;

class AccountantDashboardBuilder extends BaseDashboardBuilder
{
    public function getRoleName(): string
    {
        return 'Accountant';
    }

    public function getLayout(): string
    {
        return 'admin';
    }

    protected function buildStatCards(): array
    {
        $feeCollector = app(FeeCollector::class);
        $stats = $feeCollector->dashboardStats($this->schoolId);

        $todayCollection = FeePayment::query()->whereDate('payment_date', today())->sum('amount');
        $pendingCount = StudentFee::query()->where('status', 'pending')->count();
        $overdueCount = StudentFee::query()->where('status', 'overdue')->count();

        return [
            $this->statCard('Today Collection', '₹'.number_format($todayCollection), 'money-bill-wave', 'success'),
            $this->statCard('Total Collected', '₹'.number_format($stats['total_collected'] ?? 0), 'wallet', 'primary', null, null, route('admin.fees.index')),
            $this->statCard('Pending Fees', $pendingCount, 'clock', 'warning', null, null, route('admin.fees.index')),
            $this->statCard('Overdue Fees', $overdueCount, 'exclamation-triangle', 'danger'),
        ];
    }

    protected function buildWidgets(): array
    {
        return [];
    }

    protected function buildQuickActions(): array
    {
        return [
            $this->quickAction('Collect Fee', route('admin.fees.index'), 'plus-circle', 'success', 'fees.collect'),
            $this->quickAction('Fee Reports', route('reports.fees.index'), 'chart-bar', 'primary', 'fees.reports'),
        ];
    }

    protected function buildCharts(): array
    {
        return [];
    }
}