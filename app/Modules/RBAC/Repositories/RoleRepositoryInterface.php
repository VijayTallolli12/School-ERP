<?php

namespace App\Modules\RBAC\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

interface RoleRepositoryInterface
{
    public function query(): Builder;

    public function create(array $data): Role;

    public function update(Role $role, array $data): Role;

    public function delete(Role $role): void;
}
