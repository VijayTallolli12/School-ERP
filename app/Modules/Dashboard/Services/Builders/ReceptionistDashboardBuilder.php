<?php

namespace App\Modules\Dashboard\Services\Builders;

use App\Modules\Students\Models\Student;

class ReceptionistDashboardBuilder extends BaseDashboardBuilder
{
    public function getRoleName(): string
    {
        return 'Receptionist';
    }

    public function getLayout(): string
    {
        return 'admin';
    }

    protected function buildStatCards(): array
    {
        $totalStudents = Student::query()->count();
        $newToday = Student::query()->whereDate('created_at', today())->count();

        return [
            $this->statCard('Total Students', $totalStudents, 'users', 'primary', null, null, route('admin.students.index')),
            $this->statCard('New Today', $newToday, 'user-plus', 'success'),
        ];
    }

    protected function buildWidgets(): array
    {
        return [];
    }

    protected function buildQuickActions(): array
    {
        return [
            $this->quickAction('Add Student', route('admin.students.index'), 'user-plus', 'success', 'students.create'),
            $this->quickAction('Add Parent', route('admin.parents.index'), 'users', 'primary', 'parents.create'),
        ];
    }

    protected function buildCharts(): array
    {
        return [];
    }
}