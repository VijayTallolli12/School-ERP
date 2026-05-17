<?php

namespace App\Modules\Teachers\Policies;

use App\Models\User;
use App\Modules\Teachers\Models\Teacher;

class TeacherPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('teachers.view');
    }

    public function view(User $user, Teacher $teacher): bool
    {
        return $user->can('teachers.view');
    }

    public function create(User $user): bool
    {
        return $user->can('teachers.create');
    }

    public function update(User $user, Teacher $teacher): bool
    {
        return $user->can('teachers.update');
    }

    public function delete(User $user, Teacher $teacher): bool
    {
        return $user->can('teachers.delete');
    }
}
