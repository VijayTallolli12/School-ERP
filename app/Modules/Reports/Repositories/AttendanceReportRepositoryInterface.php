<?php

namespace App\Modules\Reports\Repositories;

use Illuminate\Database\Eloquent\Builder;

interface AttendanceReportRepositoryInterface
{
    public function dailyQuery(array $filters = []): Builder;

    public function dailySummary(array $filters = []): array;

    public function monthlySummary(int $classSectionId, int $month, int $year): array;

    public function monthlyStudentBreakdown(int $classSectionId, int $month, int $year): array;

    public function classWiseSummary(array $filters = []): array;

    public function todaySummary(): array;
}
