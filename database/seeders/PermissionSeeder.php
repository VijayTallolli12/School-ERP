<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = $this->permissions();

        foreach ($permissions as $permission) {
            Permission::query()->firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        School::query()->each(function (School $school) use ($permissions): void {
            app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);

            foreach ($this->rolePermissions() as $roleName => $allowed) {
                $role = Role::query()->firstOrCreate([
                    'name' => $roleName,
                    'guard_name' => 'web',
                    'school_id' => $school->id,
                ]);

                $role->syncPermissions(in_array($roleName, ['Super Admin', 'School Admin'], true) ? $permissions : $allowed);
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function permissions(): array
    {
        $modules = [
            'dashboard' => ['view'],
            'roles' => ['view', 'create', 'update', 'delete'],
            'permissions' => ['view', 'create', 'update', 'delete'],
            'users' => ['view', 'create', 'update', 'delete'],
            'students' => ['view', 'create', 'update', 'delete', 'export'],
            'teachers' => ['view', 'create', 'update', 'delete', 'reports'],
            'parents' => ['view', 'create', 'update', 'delete', 'reports'],
            'academics' => ['view', 'create', 'update', 'delete'],
            'attendance' => ['view', 'create', 'update', 'delete', 'reports'],
            'fees' => ['view', 'create', 'collect', 'update', 'delete', 'reports'],
            'exams' => ['view', 'create', 'update', 'delete', 'publish', 'reports'],
            'homework' => ['view', 'create', 'update', 'delete'],
            'leave_management' => ['view', 'create', 'update', 'delete', 'approve'],
            'timetable' => ['view', 'create', 'update', 'delete', 'reports'],
            'academic_calendar' => ['view', 'create', 'update', 'delete', 'publish'],
            'student_documents' => ['view', 'create', 'update', 'delete', 'verify'],
            'transport' => ['view', 'create', 'update', 'delete', 'export'],
            'library' => ['view', 'create', 'update', 'delete', 'export'],
            'payroll' => ['view', 'create', 'update', 'delete', 'export', 'process', 'lock'],
            'payroll.payslip' => ['view', 'generate', 'export'],
            'notifications' => ['view', 'create', 'update', 'delete', 'send'],
            'reports' => ['view', 'export'],
            'settings' => ['view', 'update'],
        ];

        $permissions = [];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $permissions[] = $module.'.'.$action;
            }
        }

        return $permissions;
    }

    private function rolePermissions(): array
    {
        return [
            'Super Admin' => [],
            'School Admin' => [],
            'Principal' => [
                'dashboard.view', 'students.view', 'teachers.view', 'teachers.reports', 'parents.view', 'parents.reports',
                'academics.view', 'attendance.view', 'attendance.create', 'attendance.update',
                'attendance.reports', 'fees.view',
                'fees.reports', 'exams.view', 'exams.publish', 'exams.reports', 'timetable.view', 'timetable.create', 'timetable.update', 'timetable.delete', 'timetable.reports',
                'notifications.view', 'notifications.create', 'notifications.update', 'notifications.delete', 'notifications.send',
                'academic_calendar.view', 'academic_calendar.create', 'academic_calendar.update', 'academic_calendar.publish',
                'student_documents.view', 'student_documents.create', 'student_documents.update', 'student_documents.verify',
                'homework.view', 'homework.create', 'homework.update', 'homework.delete',
                'transport.view', 'transport.create', 'transport.update', 'transport.delete',
                'reports.view',
            ],
            'Teacher' => [
                'dashboard.view', 'students.view', 'academics.view', 'attendance.view',
                'attendance.create', 'attendance.update', 'attendance.reports', 'exams.view', 'exams.reports', 'timetable.view', 'timetable.reports',
                'academic_calendar.view', 'student_documents.view',
                'homework.view', 'homework.create', 'homework.update', 'homework.delete',
            ],
            'Student' => ['dashboard.view', 'attendance.view', 'fees.view', 'exams.view'],
            'Parent' => [
                'dashboard.view', 'students.view', 'attendance.view', 'fees.view', 'exams.view',
                'timetable.view', 'homework.view', 'academic_calendar.view', 'student_documents.view',
                'notifications.view', 'leave_management.view', 'leave_management.create',
                'parents.view',
            ],
            'Accountant' => ['dashboard.view', 'fees.view', 'fees.create', 'fees.collect', 'fees.update', 'fees.reports', 'transport.view', 'reports.view'],
            'Librarian' => ['dashboard.view', 'library.view', 'library.create', 'library.update', 'library.delete', 'library.export', 'reports.view'],
            'Payroll Manager' => ['dashboard.view', 'payroll.view', 'payroll.create', 'payroll.update', 'payroll.delete', 'payroll.export', 'payroll.process', 'payroll.lock', 'reports.view'],
            'Receptionist' => ['dashboard.view', 'students.view', 'students.create', 'parents.view', 'parents.create'],
            'HR' => ['dashboard.view', 'teachers.view', 'teachers.create', 'teachers.update', 'teachers.reports', 'reports.view'],
            'Staff' => ['dashboard.view'],
        ];
    }
}
