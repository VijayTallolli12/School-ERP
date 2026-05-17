<?php

namespace App\Modules\RBAC\Services;

use App\Modules\RBAC\Repositories\PermissionRepositoryInterface;
use Spatie\Permission\Models\Permission;

class PermissionService
{
    public function __construct(private readonly PermissionRepositoryInterface $permissions) {}

    public function create(array $data): Permission
    {
        $data['guard_name'] = 'web';

        return $this->permissions->create($data);
    }

    public function update(Permission $permission, array $data): Permission
    {
        return $this->permissions->update($permission, $data);
    }

    public function delete(Permission $permission): void
    {
        if ($permission->roles()->exists()) {
            abort(422, 'Permission is assigned to one or more roles.');
        }

        $this->permissions->delete($permission);
    }
}
