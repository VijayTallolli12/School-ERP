<?php

namespace App\Modules\Dashboard\Services\DataCollectors;

use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\EmployeeContract;
use Illuminate\Support\Facades\Cache;

class HRCollector
{
    public function totalEmployeeCount(int $schoolId): int
    {
        return Cache::remember("dashboard.hr.total.{$schoolId}", 300, fn () =>
            (int) Employee::query()->count()
        );
    }

    public function activeEmployeeCount(int $schoolId): int
    {
        return Cache::remember("dashboard.hr.active.{$schoolId}", 300, fn () =>
            (int) Employee::query()->where('employment_status', 'active')->count()
        );
    }

    public function newHiresThisMonth(int $schoolId): int
    {
        return Cache::remember("dashboard.hr.new_hires.{$schoolId}", 300, fn () =>
            (int) Employee::query()
                ->where('employment_status', 'active')
                ->whereMonth('date_of_joining', now()->month)
                ->whereYear('date_of_joining', now()->year)
                ->count()
        );
    }

    public function contractsExpiringSoon(int $schoolId): int
    {
        return Cache::remember("dashboard.hr.expiring_contracts.{$schoolId}", 300, fn () =>
            (int) EmployeeContract::query()
                ->where('status', 'active')
                ->whereNotNull('end_date')
                ->whereBetween('end_date', [now(), now()->addDays(30)])
                ->count()
        );
    }

    public function summary(int $schoolId): array
    {
        return Cache::remember("dashboard.hr.summary.{$schoolId}", 300, fn () => [
            'total' => $this->totalEmployeeCount($schoolId),
            'active' => $this->activeEmployeeCount($schoolId),
            'new_hires' => $this->newHiresThisMonth($schoolId),
            'expiring_contracts' => $this->contractsExpiringSoon($schoolId),
        ]);
    }
}
