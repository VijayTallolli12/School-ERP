<?php

namespace App\Modules\Attendance\Policies;

use App\Models\User;
use App\Modules\Attendance\Models\Attendance;

class AttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('attendance.view');
    }

    public function view(User $user, Attendance $attendance): bool
    {
        return $user->can('attendance.view');
    }

    public function create(User $user): bool
    {
        return $user->can('attendance.create');
    }

    public function update(User $user, Attendance $attendance): bool
    {
        return $user->can('attendance.update');
    }

    public function delete(User $user, Attendance $attendance): bool
    {
        return $user->can('attendance.delete');
    }
}
