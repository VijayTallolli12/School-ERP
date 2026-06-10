<?php

namespace App\Modules\Parents\Policies;

use App\Models\User;
use App\Modules\Parents\Models\Guardian;
use Illuminate\Auth\Access\HandlesAuthorization;

class ParentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('parents.view');
    }

    public function view(User $user, Guardian $parent): bool
    {
        return $user->can('parents.view') && $user->current_school_id === $parent->school_id;
    }

    public function create(User $user): bool
    {
        return $user->can('parents.create');
    }

    public function update(User $user, Guardian $parent): bool
    {
        return $user->can('parents.update') && $user->current_school_id === $parent->school_id;
    }

    public function delete(User $user, Guardian $parent): bool
    {
        return $user->can('parents.delete') && $user->current_school_id === $parent->school_id;
    }

    public function restore(User $user, Guardian $parent): bool
    {
        return $user->can('parents.delete') && $user->current_school_id === $parent->school_id;
    }

    public function forceDelete(User $user, Guardian $parent): bool
    {
        return $user->can('parents.delete') && $user->current_school_id === $parent->school_id;
    }
}