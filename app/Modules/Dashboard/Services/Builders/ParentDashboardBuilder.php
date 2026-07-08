<?php

namespace App\Modules\Dashboard\Services\Builders;

use App\Modules\Parents\Models\Guardian;
use App\Modules\Parents\Services\ParentService;

class ParentDashboardBuilder extends BaseDashboardBuilder
{
    public function getRoleName(): string
    {
        return 'Parent';
    }

    public function getLayout(): string
    {
        return 'parent';
    }

    protected function buildStatCards(): array
    {
        $guardian = Guardian::query()->where('user_id', $this->user->getKey())->first();

        if (!$guardian) {
            return [];
        }

        $data = $this->getParentData($guardian);
        $attendance = $data['attendance_summary'] ?? [];
        $fees = $data['fees_summary'] ?? [];
        $exams = $data['exam_results_summary'] ?? [];
        $homework = $data['homework_summary'] ?? [];

        return [
            $this->statCard('Attendance', ($attendance['percentage'] ?? 0).'%', 'calendar-check', 'info'),
            $this->statCard('Pending Fees', '₹'.number_format($fees['pending'] ?? 0), 'money-bill-wave', 'warning'),
            $this->statCard('Exam Score', ($exams['average'] ?? 0).'%', 'chart-arrows-vertical', 'success'),
            $this->statCard('Homework', $homework['active_count'] ?? 0, 'books', 'primary'),
        ];
    }

    private ?array $parentData = null;

    private function getParentData(Guardian $guardian): array
    {
        if ($this->parentData === null) {
            $this->parentData = app(ParentService::class)->getParentDashboardData($guardian);
        }

        return $this->parentData;
    }

    protected function buildWidgets(): array
    {
        return [];
    }

    protected function buildQuickActions(): array
    {
        return [
            $this->quickAction('View Attendance', route('parent-portal.attendance'), 'calendar-check', 'info'),
            $this->quickAction('View Fees', route('parent-portal.fees'), 'money-bill-wave', 'warning'),
            $this->quickAction('Homework', route('parent-portal.homework'), 'books', 'primary'),
            $this->quickAction('Exam Results', route('parent-portal.exam-results'), 'chart-line', 'success'),
        ];
    }

    protected function buildCharts(): array
    {
        return [];
    }

    protected function buildMeta(): array
    {
        $guardian = Guardian::query()->where('user_id', $this->user->getKey())->first();

        if (!$guardian) {
            return [];
        }

        return $this->getParentData($guardian);
    }
}
