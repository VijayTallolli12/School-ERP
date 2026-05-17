<?php

namespace App\Modules\Reports\Repositories;

interface ParentReportRepositoryInterface
{
    public function dashboardStats(): array;
    public function parentList(array $filters = []): array;
    public function mapping(array $filters = []): array;
    public function activitySummary(array $filters = []): array;
}