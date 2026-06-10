<?php

namespace App\Modules\Homework\Policies;

use App\Models\User;
use App\Modules\Homework\Models\Homework;

class HomeworkPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('homework.view');
    }

    public function view(User $user, Homework $homework): bool
    {
        return $user->can('homework.view');
    }

    public function create(User $user): bool
    {
        return $user->can('homework.create');
    }

    public function update(User $user, Homework $homework): bool
    {
        return $user->can('homework.update');
    }

    public function delete(User $user, Homework $homework): bool
    {
        return $user->can('homework.delete');
    }
}
