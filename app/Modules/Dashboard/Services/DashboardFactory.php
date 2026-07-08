<?php

namespace App\Modules\Dashboard\Services;

use App\Models\User;
use App\Modules\Dashboard\Contracts\RoleDashboardBuilderInterface;
use App\Modules\Dashboard\Services\Builders\AccountantDashboardBuilder;
use App\Modules\Dashboard\Services\Builders\AdminDashboardBuilder;
use App\Modules\Dashboard\Services\Builders\HRDashboardBuilder;
use App\Modules\Dashboard\Services\Builders\LibrarianDashboardBuilder;
use App\Modules\Dashboard\Services\Builders\ParentDashboardBuilder;
use App\Modules\Dashboard\Services\Builders\PrincipalDashboardBuilder;
use App\Modules\Dashboard\Services\Builders\ReceptionistDashboardBuilder;
use App\Modules\Dashboard\Services\Builders\StaffDashboardBuilder;
use App\Modules\Dashboard\Services\Builders\StudentDashboardBuilder;
use App\Modules\Dashboard\Services\Builders\TeacherDashboardBuilder;
use Illuminate\Contracts\Auth\Authenticatable;

class DashboardFactory
{
    private const ROLE_PRIORITY = [
        'Super Admin' => AdminDashboardBuilder::class,
        'School Admin' => AdminDashboardBuilder::class,
        'Principal' => PrincipalDashboardBuilder::class,
        'HR' => HRDashboardBuilder::class,
        'Teacher' => TeacherDashboardBuilder::class,
        'Accountant' => AccountantDashboardBuilder::class,
        'Librarian' => LibrarianDashboardBuilder::class,
        'Receptionist' => ReceptionistDashboardBuilder::class,
        'Staff' => StaffDashboardBuilder::class,
        'Parent' => ParentDashboardBuilder::class,
        'Student' => StudentDashboardBuilder::class,
    ];

    public function make(Authenticatable|User $user): RoleDashboardBuilderInterface
    {
        foreach (self::ROLE_PRIORITY as $role => $builderClass) {
            if ($user->hasRole($role)) {
                return app($builderClass);
            }
        }

        abort(403, 'Your role does not have access to any dashboard.');
    }
}
