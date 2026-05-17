<?php

namespace App\Modules\Academics\Policies;

use App\Models\User;
use App\Modules\Academics\Models\SchoolClass;

class SchoolClassPolicy
{
    public function viewAny(User $user): bool { return $user->can('academics.view'); }

    public function create(User $user): bool { return $user->can('academics.create'); }

    public function update(User $user, SchoolClass $class): bool { return $user->can('academics.update'); }

    public function delete(User $user, SchoolClass $class): bool { return $user->can('academics.delete') && ! $class->classSections()->exists(); }
}
