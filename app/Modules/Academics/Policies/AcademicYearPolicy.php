<?php

namespace App\Modules\Academics\Policies;

use App\Models\AcademicYear;
use App\Models\User;

class AcademicYearPolicy
{
    public function viewAny(User $user): bool { return $user->can('academics.view'); }

    public function create(User $user): bool { return $user->can('academics.create'); }

    public function update(User $user, AcademicYear $academicYear): bool { return $user->can('academics.update'); }

    public function delete(User $user, AcademicYear $academicYear): bool { return $user->can('academics.delete') && ! $academicYear->is_active; }
}
