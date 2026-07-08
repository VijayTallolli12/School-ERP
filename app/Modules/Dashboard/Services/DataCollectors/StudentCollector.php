<?php

namespace App\Modules\Dashboard\Services\DataCollectors;

use App\Models\User;
use App\Modules\Students\Models\Student;
use Illuminate\Support\Facades\Cache;

class StudentCollector
{
    public function totalCount(int $schoolId): int
    {
        return Cache::remember("dashboard.student.total.{$schoolId}", 300, fn () =>
            Student::query()->count()
        );
    }

    public function genderDistribution(int $schoolId): array
    {
        return Cache::remember("dashboard.student.gender.{$schoolId}", 600, fn () =>
            Student::query()
                ->selectRaw("gender, COUNT(*) as count")
                ->groupBy('gender')
                ->pluck('count', 'gender')
                ->toArray()
        );
    }

    public function newAdmissions(int $schoolId, int $days = 30): int
    {
        return Cache::remember("dashboard.student.new.{$schoolId}.{$days}", 300, fn () =>
            Student::query()
                ->where('created_at', '>=', now()->subDays($days))
                ->count()
        );
    }

    public function totalUsers(int $schoolId): int
    {
        return Cache::remember("dashboard.users.total.{$schoolId}", 300, fn () =>
            User::query()
                ->whereHas('schools', fn ($q) => $q->whereKey($schoolId))
                ->count()
        );
    }
}
