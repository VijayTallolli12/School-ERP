<?php

namespace App\Modules\RBAC\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;

interface PermissionRepositoryInterface
{
    public function query(): Builder;

    public function create(array $data): Permission;

    public function update(Permission $permission, array $data): Permission;

    public function delete(Permission $permission): void;
}
