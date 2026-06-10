<?php

namespace App\Modules\Reports\Repositories;

interface TeacherReportRepositoryInterface
{
    public function dashboardStats(): array;
    public function teacherList(array $filters = []): array;
    public function attendance(array $filters = []): array;
    public function subjectAllocation(array $filters = []): array;
    public function classTeacherMapping(array $filters = []): array;
    public function workload(array $filters = []): array;
}