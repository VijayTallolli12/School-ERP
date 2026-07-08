<?php

namespace App\Modules\Homework\Policies;

use App\Models\User;
use App\Modules\Homework\Models\Homework;
use App\Modules\Teachers\Models\Teacher;

class HomeworkPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('homework.view');
    }

    public function view(User $user, Homework $homework): bool
    {
        if (! $user->can('homework.view')) {
            return false;
        }
        if ($user->hasRole('Teacher')) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if (! $teacher) {
                return false;
            }
            return $teacher->classSections->pluck('id')->contains($homework->class_section_id);
        }
        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('homework.create');
    }

    public function update(User $user, Homework $homework): bool
    {
        if ($user->can('homework.update') && $user->hasRole('Teacher')) {
            return $homework->created_by === $user->id;
        }
        return $user->can('homework.update');
    }

    public function delete(User $user, Homework $homework): bool
    {
        if ($user->can('homework.delete') && $user->hasRole('Teacher')) {
            return $homework->created_by === $user->id;
        }
        return $user->can('homework.delete');
    }
}
