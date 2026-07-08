<?php

namespace App\Modules\Exams\Policies;

use App\Models\User;
use App\Modules\Exams\Models\Exam;
use App\Modules\Teachers\Models\Teacher;

class ExamPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('exams.view');
    }

    public function view(User $user, Exam $exam): bool
    {
        if (! $user->can('exams.view')) {
            return false;
        }
        if ($user->hasRole('Teacher')) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            return $teacher && $teacher->classSections->pluck('id')->contains($exam->class_section_id);
        }
        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('exams.create');
    }

    public function update(User $user, Exam $exam): bool
    {
        if (! $user->can('exams.update')) {
            return false;
        }
        if ($user->hasRole('Teacher')) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            return $teacher && $teacher->classSections->pluck('id')->contains($exam->class_section_id);
        }
        return true;
    }

    public function delete(User $user, Exam $exam): bool
    {
        if (! $user->can('exams.delete')) {
            return false;
        }
        if ($user->hasRole('Teacher')) {
            return false;
        }
        return true;
    }

    public function publish(User $user, Exam $exam): bool
    {
        if ($user->hasRole('Teacher')) {
            return false;
        }
        return $user->can('exams.publish');
    }
}
