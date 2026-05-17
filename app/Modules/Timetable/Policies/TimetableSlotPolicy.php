<?php

namespace App\Modules\Timetable\Policies;

use App\Models\User;
use App\Modules\Timetable\Models\TimetableSlot;

class TimetableSlotPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('timetable.view');
    }

    public function view(User $user, TimetableSlot $slot): bool
    {
        return $user->can('timetable.view');
    }

    public function create(User $user): bool
    {
        return $user->can('timetable.create');
    }

    public function update(User $user, TimetableSlot $slot): bool
    {
        return $user->can('timetable.update');
    }

    public function delete(User $user, TimetableSlot $slot): bool
    {
        return $user->can('timetable.delete');
    }

    public function print(User $user): bool
    {
        return $user->can('timetable.reports');
    }
}
