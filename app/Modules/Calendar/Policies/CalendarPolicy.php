<?php

namespace App\Modules\Calendar\Policies;

use App\Models\User;
use App\Modules\Calendar\Models\AcademicCalendar;
use Illuminate\Auth\Access\HandlesAuthorization;

class CalendarPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('academic_calendar.view');
    }

    public function view(User $user, AcademicCalendar $calendar): bool
    {
        return $user->can('academic_calendar.view') && $calendar->school_id === $user->current_school_id;
    }

    public function create(User $user): bool
    {
        return $user->can('academic_calendar.create');
    }

    public function update(User $user, AcademicCalendar $calendar): bool
    {
        return $user->can('academic_calendar.update') && $calendar->school_id === $user->current_school_id;
    }

    public function delete(User $user, AcademicCalendar $calendar): bool
    {
        return $user->can('academic_calendar.delete') && $calendar->school_id === $user->current_school_id;
    }

    public function publish(User $user, AcademicCalendar $calendar): bool
    {
        return $user->can('academic_calendar.publish') && $calendar->school_id === $user->current_school_id;
    }
}
