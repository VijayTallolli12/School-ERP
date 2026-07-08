<?php

namespace App\Modules\Dashboard\Services\DataCollectors;

use App\Modules\Teachers\Models\Teacher;
use Illuminate\Support\Facades\Cache;

class TeacherCollector
{
    public function totalCount(int $schoolId): int
    {
        return Cache::remember("dashboard.teacher.total.{$schoolId}", 300, fn () =>
            (int) Teacher::query()->count()
        );
    }

    public function activeCount(int $schoolId): int
    {
        return Cache::remember("dashboard.teacher.active.{$schoolId}", 300, fn () =>
            (int) Teacher::query()->where('status', 'active')->count()
        );
    }

    public function summary(int $schoolId): array
    {
        return Cache::remember("dashboard.teacher.summary.{$schoolId}", 300, fn () => [
            'total' => $this->totalCount($schoolId),
            'active' => $this->activeCount($schoolId),
        ]);
    }
}
