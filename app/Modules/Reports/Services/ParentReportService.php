<?php

namespace App\Modules\Reports\Services;

use App\Modules\Reports\Repositories\ParentReportRepositoryInterface;

class ParentReportService
{
    public function __construct(private readonly ParentReportRepositoryInterface $repository) {}

    public function dashboardStats(): array
    {
        return $this->repository->dashboardStats();
    }

    public function parentList(array $filters = []): array
    {
        return $this->repository->parentList($filters);
    }

    public function mapping(array $filters = []): array
    {
        return $this->repository->mapping($filters);
    }

    public function activitySummary(array $filters = []): array
    {
        return $this->repository->activitySummary($filters);
    }
}