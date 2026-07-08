<?php

namespace App\Modules\Exams\Policies;

use App\Models\User;

class ExamSchedulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('exams.view');
    }

    public function view(User $user): bool
    {
        return $user->can('exams.view');
    }

    public function create(User $user): bool
    {
        return $user->can('exams.create');
    }

    public function update(User $user): bool
    {
        return $user->can('exams.update');
    }

    public function delete(User $user): bool
    {
        return $user->can('exams.delete');
    }
}
