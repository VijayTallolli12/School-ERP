<?php

namespace App\Modules\RBAC\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;

class PermissionRepository implements PermissionRepositoryInterface
{
    public function query(): Builder
    {
        return Permission::query()->withCount('roles');
    }

    public function create(array $data): Permission
    {
        return Permission::query()->create($data);
    }

    public function update(Permission $permission, array $data): Permission
    {
        $permission->fill($data)->save();

        return $permission->refresh();
    }

    public function delete(Permission $permission): void
    {
        $permission->delete();
    }
}
