<?php

namespace App\Modules\Reports\Repositories;

interface ExamReportRepositoryInterface
{
    public function dashboardStats(): array;
    public function examResults(?int $academicYearId, ?int $examId, ?int $classSectionId, ?int $subjectId): array;
    public function classPerformance(?int $academicYearId, ?int $examId): array;
    public function subjectPerformance(?int $academicYearId, ?int $examId, ?int $classSectionId): array;
    public function studentSummary(?int $studentId, ?int $academicYearId): array;
    public function topPerformers(?int $academicYearId, ?int $examId, ?int $classSectionId, ?int $subjectId, int $topN = 10): array;
    public function passFailAnalysis(?int $academicYearId, ?int $examId, ?int $classSectionId, ?int $subjectId, ?string $fromDate, ?string $toDate): array;
}