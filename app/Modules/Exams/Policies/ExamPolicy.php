<?php

namespace App\Modules\Exams\Policies;

use App\Models\User;
use App\Modules\Exams\Models\Exam;

class ExamPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('exams.view');
    }

    public function view(User $user, Exam $exam): bool
    {
        return $user->can('exams.view');
    }

    public function create(User $user): bool
    {
        return $user->can('exams.create');
    }

    public function update(User $user, Exam $exam): bool
    {
        return $user->can('exams.update');
    }

    public function delete(User $user, Exam $exam): bool
    {
        return $user->can('exams.delete');
    }

    public function publish(User $user, Exam $exam): bool
    {
        return $user->can('exams.publish');
    }
}
