<?php

namespace App\Modules\Exams\Policies;

use App\Models\User;
use App\Modules\Exams\Models\ExamMark;

class ExamMarkPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('exams.view');
    }

    public function view(User $user, ExamMark $mark): bool
    {
        return $user->can('exams.view');
    }

    public function create(User $user): bool
    {
        return $user->can('exams.create');
    }

    public function update(User $user, ExamMark $mark): bool
    {
        return $user->can('exams.update');
    }

    public function delete(User $user, ExamMark $mark): bool
    {
        return $user->can('exams.delete');
    }
}
