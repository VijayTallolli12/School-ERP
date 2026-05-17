<?php

namespace App\Modules\RBAC\Repositories;

use App\Core\Tenant\SchoolContext;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class RoleRepository implements RoleRepositoryInterface
{
    public function query(): Builder
    {
        $schoolId = app(SchoolContext::class)->id();

        return Role::query()
            ->with('permissions')
            ->when($schoolId, fn (Builder $query) => $query->where('school_id', $schoolId))
            ->when(! $schoolId, fn (Builder $query) => $query->whereNull('school_id'));
    }

    public function create(array $data): Role
    {
        return Role::query()->create($data);
    }

    public function update(Role $role, array $data): Role
    {
        $role->fill($data)->save();

        return $role->refresh();
    }

    public function delete(Role $role): void
    {
        $role->delete();
    }
}
