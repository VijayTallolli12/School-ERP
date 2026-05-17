<?php

namespace App\Modules\RBAC\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('roles.view');
    }

    public function create(User $user): bool
    {
        return $user->can('roles.create');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->can('roles.update') && $role->name !== 'Super Admin';
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->can('roles.delete') && ! in_array($role->name, ['Super Admin', 'School Admin'], true);
    }
}
