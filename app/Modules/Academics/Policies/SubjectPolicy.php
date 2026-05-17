<?php

namespace App\Modules\Academics\Policies;

use App\Models\User;
use App\Modules\Academics\Models\Subject;

class SubjectPolicy
{
    public function viewAny(User $user): bool { return $user->can('academics.view'); }

    public function create(User $user): bool { return $user->can('academics.create'); }

    public function update(User $user, Subject $subject): bool { return $user->can('academics.update'); }

    public function delete(User $user, Subject $subject): bool { return $user->can('academics.delete') && ! $subject->classSubjects()->exists(); }
}
