<?php

namespace App\Modules\Attendance\Policies;

use App\Models\User;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Teachers\Models\Teacher;

class AttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('attendance.view');
    }

    public function view(User $user, Attendance $attendance): bool
    {
        if (! $user->can('attendance.view')) {
            return false;
        }
        if ($user->hasRole('Teacher')) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            return $teacher && $teacher->classSections->pluck('id')->contains($attendance->class_section_id);
        }
        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('attendance.create');
    }

    public function update(User $user, Attendance $attendance): bool
    {
        if (! $user->can('attendance.update')) {
            return false;
        }
        if ($user->hasRole('Teacher')) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            return $teacher && $teacher->classSections->pluck('id')->contains($attendance->class_section_id);
        }
        return true;
    }

    public function delete(User $user, Attendance $attendance): bool
    {
        if (! $user->can('attendance.delete')) {
            return false;
        }
        if ($user->hasRole('Teacher')) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            return $teacher && $teacher->classSections->pluck('id')->contains($attendance->class_section_id);
        }
        return true;
    }
}
