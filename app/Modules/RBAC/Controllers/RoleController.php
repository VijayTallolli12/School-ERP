<?php

namespace App\Modules\RBAC\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\RBAC\Repositories\RoleRepositoryInterface;
use App\Modules\RBAC\Requests\StoreRoleRequest;
use App\Modules\RBAC\Requests\UpdateRoleRequest;
use App\Modules\RBAC\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    public function __construct(
        private readonly RoleRepositoryInterface $roles,
        private readonly RoleService $service,
    ) {}

    public function index()
    {
        return view('modules.rbac.roles.index', [
            'permissions' => Permission::query()->orderBy('name')->get()->groupBy(fn (Permission $permission) => str($permission->name)->before('.')->toString()),
        ]);
    }

    public function data(): JsonResponse
    {
        return DataTables::of($this->roles->query())
            ->addColumn('permissions_count', fn (Role $role) => $role->permissions->count())
            ->addColumn('permissions_preview', fn (Role $role) => $role->permissions->pluck('name')->take(6)->implode(', '))
            ->addColumn('actions', fn (Role $role) => view('modules.rbac.roles._actions', compact('role'))->render())
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->service->create($request->validated());

        activity()->causedBy($request->user())->performedOn($role)->event('created')->log('Role created');

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully.',
            'data' => $role,
        ]);
    }

    public function show(Role $role): JsonResponse
    {
        $role->load('permissions');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
            ],
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $role = $this->service->update($role, $request->validated());

        activity()->causedBy($request->user())->performedOn($role)->event('updated')->log('Role updated');

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully.',
            'data' => $role,
        ]);
    }

    public function destroy(Role $role): JsonResponse
    {
        $this->authorize('delete', $role);
        $this->service->delete($role);

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully.',
        ]);
    }
}
