<?php

namespace App\Modules\Students\Policies;

use App\Models\User;
use App\Modules\Students\Models\Student;
use App\Modules\Teachers\Models\Teacher;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('students.view');
    }

    public function view(User $user, Student $student): bool
    {
        if (! $user->can('students.view')) {
            return false;
        }
        if ($user->hasRole('Teacher')) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if (! $teacher) {
                return false;
            }
            $assignedIds = $teacher->classSections->pluck('id');
            return $student->sessions()->whereIn('class_section_id', $assignedIds)->where('status', 'active')->exists();
        }
        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('students.create');
    }

    public function update(User $user, Student $student): bool
    {
        if (! $user->can('students.update')) {
            return false;
        }
        if ($user->hasRole('Teacher')) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if (! $teacher) {
                return false;
            }
            $assignedIds = $teacher->classSections->pluck('id');
            return $student->sessions()->whereIn('class_section_id', $assignedIds)->where('status', 'active')->exists();
        }
        return true;
    }

    public function delete(User $user, Student $student): bool
    {
        if (! $user->can('students.delete')) {
            return false;
        }
        if ($user->hasRole('Teacher')) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if (! $teacher) {
                return false;
            }
            $assignedIds = $teacher->classSections->pluck('id');
            return $student->sessions()->whereIn('class_section_id', $assignedIds)->where('status', 'active')->exists();
        }
        return true;
    }
}
