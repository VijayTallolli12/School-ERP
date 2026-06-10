<?php

namespace App\Modules\Leave\Policies;

use App\Models\User;
use App\Modules\Leave\Models\LeaveRequest;

class LeaveRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('leave_management.view');
    }

    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->can('leave_management.view');
    }

    public function create(User $user): bool
    {
        return $user->can('leave_management.create');
    }

    public function update(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->can('leave_management.update');
    }

    public function delete(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->can('leave_management.delete');
    }

    public function approve(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->can('leave_management.approve');
    }
}
