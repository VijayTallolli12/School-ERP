<?php

namespace App\Modules\Academics\Policies;

use App\Models\User;
use App\Modules\Academics\Models\ClassSection;

class ClassSectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('academics.view');
    }

    public function view(User $user, ClassSection $classSection): bool
    {
        return $user->can('academics.view');
    }

    /**
     * Used by attendance flows (student lists, monthly summary) so users with
     * attendance permissions are not blocked when they lack academics.view.
     */
    public function viewForAttendance(User $user, ClassSection $classSection): bool
    {
        return $user->can('attendance.view')
            || $user->can('attendance.create')
            || $user->can('academics.view');
    }

    public function create(User $user): bool
    {
        return $user->can('academics.create');
    }

    public function update(User $user, ClassSection $classSection): bool
    {
        return $user->can('academics.update');
    }

    public function delete(User $user, ClassSection $classSection): bool
    {
        return $user->can('academics.delete') && ! $classSection->studentSessions()->exists();
    }
}
