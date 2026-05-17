<?php

namespace App\Modules\Reports\Repositories;

interface ExamReportRepositoryInterface
{
    public function dashboardStats(): array;
    public function examResults(?int $academicYearId, ?int $examId, ?int $classSectionId, ?int $subjectId): array;
    public function classPerformance(?int $academicYearId, ?int $examId): array;
    public function subjectPerformance(?int $academicYearId, ?int $examId, ?int $classSectionId): array;
    public function studentSummary(?int $studentId, ?int $academicYearId): array;
}