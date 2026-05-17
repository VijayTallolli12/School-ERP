<?php

namespace App\Modules\Reports\Services;

use App\Modules\Reports\Repositories\AttendanceReportRepositoryInterface;

class AttendanceReportService
{
    protected AttendanceReportRepositoryInterface $repository;

    public function __construct(AttendanceReportRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function dailyReport(array $filters = []): array
    {
        return [
            'summary' => $this->repository->dailySummary($filters),
            'query' => $this->repository->dailyQuery($filters),
        ];
    }

    public function monthlyReport(int $classSectionId, int $month, int $year): array
    {
        return [
            'summary' => $this->repository->monthlySummary($classSectionId, $month, $year),
            'student_breakdown' => $this->repository->monthlyStudentBreakdown($classSectionId, $month, $year),
        ];
    }

    public function classWiseReport(array $filters = []): array
    {
        return [
            'class_summary' => $this->repository->classWiseSummary($filters),
        ];
    }

    public function todaySummary(): array
    {
        return $this->repository->todaySummary();
    }
}
