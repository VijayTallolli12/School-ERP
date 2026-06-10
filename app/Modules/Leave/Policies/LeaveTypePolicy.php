<?php

namespace App\Modules\Leave\Policies;

use App\Models\User;
use App\Modules\Leave\Models\LeaveType;

class LeaveTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('leave_management.view');
    }

    public function view(User $user, LeaveType $leaveType): bool
    {
        return $user->can('leave_management.view');
    }

    public function create(User $user): bool
    {
        return $user->can('leave_management.create');
    }

    public function update(User $user, LeaveType $leaveType): bool
    {
        return $user->can('leave_management.update');
    }

    public function delete(User $user, LeaveType $leaveType): bool
    {
        return $user->can('leave_management.delete');
    }
}
