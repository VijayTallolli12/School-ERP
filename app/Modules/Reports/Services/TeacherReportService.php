<?php

namespace App\Modules\Reports\Services;

use App\Modules\Reports\Repositories\TeacherReportRepositoryInterface;

class TeacherReportService
{
    public function __construct(private readonly TeacherReportRepositoryInterface $repository) {}

    public function dashboardStats(): array
    {
        return $this->repository->dashboardStats();
    }

    public function teacherList(array $filters = []): array
    {
        return $this->repository->teacherList($filters);
    }

    public function attendance(array $filters = []): array
    {
        return $this->repository->attendance($filters);
    }

    public function subjectAllocation(array $filters = []): array
    {
        return $this->repository->subjectAllocation($filters);
    }

    public function classTeacherMapping(array $filters = []): array
    {
        return $this->repository->classTeacherMapping($filters);
    }
}