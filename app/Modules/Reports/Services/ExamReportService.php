<?php

namespace App\Modules\Reports\Services;

use App\Modules\Reports\Repositories\ExamReportRepositoryInterface;

class ExamReportService
{
    public function __construct(private readonly ExamReportRepositoryInterface $repository) {}

    public function dashboardStats(): array
    {
        return $this->repository->dashboardStats();
    }

    public function examResults(?int $academicYearId, ?int $examId, ?int $classSectionId, ?int $subjectId): array
    {
        return $this->repository->examResults($academicYearId, $examId, $classSectionId, $subjectId);
    }

    public function classPerformance(?int $academicYearId, ?int $examId): array
    {
        return $this->repository->classPerformance($academicYearId, $examId);
    }

    public function subjectPerformance(?int $academicYearId, ?int $examId, ?int $classSectionId): array
    {
        return $this->repository->subjectPerformance($academicYearId, $examId, $classSectionId);
    }

    public function studentSummary(?int $studentId, ?int $academicYearId): array
    {
        return $this->repository->studentSummary($studentId, $academicYearId);
    }

    public function topPerformers(?int $academicYearId, ?int $examId, ?int $classSectionId, ?int $subjectId, int $topN = 10): array
    {
        return $this->repository->topPerformers($academicYearId, $examId, $classSectionId, $subjectId, $topN);
    }

    public function passFailAnalysis(?int $academicYearId, ?int $examId, ?int $classSectionId, ?int $subjectId, ?string $fromDate, ?string $toDate): array
    {
        return $this->repository->passFailAnalysis($academicYearId, $examId, $classSectionId, $subjectId, $fromDate, $toDate);
    }
}