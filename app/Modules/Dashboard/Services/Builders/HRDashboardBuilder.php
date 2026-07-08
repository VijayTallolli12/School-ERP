<?php

namespace App\Modules\Dashboard\Services\Builders;

use App\Modules\Dashboard\Services\DataCollectors\HRCollector;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\EmployeeDocument;

class HRDashboardBuilder extends BaseDashboardBuilder
{
    public function getRoleName(): string
    {
        return 'HR';
    }

    public function getLayout(): string
    {
        return 'admin';
    }

    protected function buildStatCards(): array
    {
        $collector = app(HRCollector::class);

        return [
            $this->statCard('Total Employees', $collector->totalEmployeeCount($this->schoolId), 'users', 'primary', null, null, route('admin.hr.index')),
            $this->statCard('Active Employees', $collector->activeEmployeeCount($this->schoolId), 'user-check', 'success'),
            $this->statCard('New Hires (This Month)', $collector->newHiresThisMonth($this->schoolId), 'user-plus', 'info'),
            $this->statCard('Contracts Expiring (30 Days)', $collector->contractsExpiringSoon($this->schoolId), 'clock', 'warning'),
        ];
    }

    protected function buildWidgets(): array
    {
        $widgets = [];

        $employeesByDept = Employee::query()
            ->selectRaw('department, COUNT(*) as count')
            ->groupBy('department')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn ($e) => ['label' => $e->department ?? 'Unassigned', 'value' => $e->count])
            ->toArray();

        $widgets[] = $this->widget(
            'employees_by_department',
            'Employees by Department',
            'list',
            $employeesByDept,
            'building',
            'primary',
            4, 2,
            route('admin.hr.index'),
            'No departments configured',
        );

        $pendingDocs = EmployeeDocument::query()
            ->where('status', 'pending')
            ->whereHas('employee', fn ($q) => $q->where('school_id', $this->schoolId))
            ->with('employee')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($d) => ['label' => $d->employee?->full_name ?? 'Unknown', 'value' => $d->document_type])
            ->toArray();

        $widgets[] = $this->widget(
            'pending_verifications',
            'Pending Document Verifications',
            'list',
            $pendingDocs,
            'file-check',
            'warning',
            4, 2,
            route('admin.hr.documents.index'),
            'No pending verifications',
        );

        return $widgets;
    }

    protected function buildQuickActions(): array
    {
        return [
            $this->quickAction('Add Employee', route('admin.hr.index'), 'user-plus', 'primary', 'hr.create'),
            $this->quickAction('View Employees', route('admin.hr.index'), 'users', 'info', 'hr.view'),
            $this->quickAction('Verify Documents', route('admin.hr.documents.index'), 'file-check', 'warning', 'hr.verify'),
        ];
    }
}
