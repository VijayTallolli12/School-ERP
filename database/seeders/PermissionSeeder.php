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
            'timetable' => ['view', 'create', 'update', 'delete', 'reports'],
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
                'reports.view',
            ],
            'Teacher' => [
                'dashboard.view', 'students.view', 'academics.view', 'attendance.view',
                'attendance.create', 'attendance.update', 'attendance.reports', 'exams.view', 'exams.reports', 'timetable.view', 'timetable.reports',
            ],
            'Student' => ['dashboard.view', 'attendance.view', 'fees.view', 'exams.view'],
            'Parent' => ['dashboard.view', 'students.view', 'attendance.view', 'fees.view', 'exams.view'],
            'Accountant' => ['dashboard.view', 'fees.view', 'fees.create', 'fees.collect', 'fees.update', 'fees.reports', 'reports.view'],
            'Librarian' => ['dashboard.view', 'reports.view'],
            'Receptionist' => ['dashboard.view', 'students.view', 'students.create', 'parents.view', 'parents.create'],
            'HR' => ['dashboard.view', 'teachers.view', 'teachers.create', 'teachers.update', 'teachers.reports', 'reports.view'],
            'Staff' => ['dashboard.view'],
        ];
    }
}
