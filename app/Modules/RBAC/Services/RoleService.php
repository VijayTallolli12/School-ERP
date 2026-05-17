<?php

namespace App\Modules\RBAC\Services;

use App\Core\Tenant\SchoolContext;
use App\Modules\RBAC\Repositories\RoleRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class RoleService
{
    public function __construct(private readonly RoleRepositoryInterface $roles) {}

    public function create(array $data): Role
    {
        return DB::transaction(function () use ($data): Role {
            $permissions = $data['permissions'] ?? [];
            unset($data['permissions']);

            $data['guard_name'] = 'web';
            $data['school_id'] = app(SchoolContext::class)->id();

            $role = $this->roles->create($data);
            $role->syncPermissions($permissions);

            return $role->load('permissions');
        });
    }

    public function update(Role $role, array $data): Role
    {
        return DB::transaction(function () use ($role, $data): Role {
            $permissions = $data['permissions'] ?? [];
            unset($data['permissions']);

            $role = $this->roles->update($role, $data);
            $role->syncPermissions($permissions);

            return $role->load('permissions');
        });
    }

    public function delete(Role $role): void
    {
        if (in_array($role->name, ['Super Admin', 'School Admin'], true)) {
            abort(422, 'System roles cannot be deleted.');
        }

        $this->roles->delete($role);
    }
}
