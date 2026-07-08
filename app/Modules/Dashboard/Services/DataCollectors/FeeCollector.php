<?php

namespace App\Modules\Dashboard\Services\DataCollectors;

use App\Modules\Fees\Services\FeeService;
use Illuminate\Support\Facades\Cache;

class FeeCollector
{
    public function dashboardStats(int $schoolId): ?array
    {
        return Cache::remember("dashboard.fee.stats.{$schoolId}", 300, fn () =>
            app(FeeService::class)->dashboardFeeStats()
        );
    }

    public function totalCollected(int $schoolId): float
    {
        return (float) ($this->dashboardStats($schoolId)['total_collected'] ?? 0);
    }

    public function pendingFees(int $schoolId): float
    {
        return (float) ($this->dashboardStats($schoolId)['pending_fees'] ?? 0);
    }
}
