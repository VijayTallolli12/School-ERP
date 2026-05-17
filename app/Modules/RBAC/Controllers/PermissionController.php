<?php

namespace App\Modules\RBAC\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\RBAC\Repositories\PermissionRepositoryInterface;
use App\Modules\RBAC\Requests\StorePermissionRequest;
use App\Modules\RBAC\Requests\UpdatePermissionRequest;
use App\Modules\RBAC\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

class PermissionController extends Controller
{
    public function __construct(
        private readonly PermissionRepositoryInterface $permissions,
        private readonly PermissionService $service,
    ) {}

    public function index()
    {
        return view('modules.rbac.permissions.index');
    }

    public function data(): JsonResponse
    {
        return DataTables::of($this->permissions->query())
            ->addColumn('module', fn (Permission $permission) => str($permission->name)->before('.')->headline())
            ->addColumn('actions', fn (Permission $permission) => view('modules.rbac.permissions._actions', compact('permission'))->render())
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function store(StorePermissionRequest $request): JsonResponse
    {
        $permission = $this->service->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Permission created successfully.',
            'data' => $permission,
        ]);
    }

    public function show(Permission $permission): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $permission,
        ]);
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): JsonResponse
    {
        $permission = $this->service->update($permission, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Permission updated successfully.',
            'data' => $permission,
        ]);
    }

    public function destroy(Permission $permission): JsonResponse
    {
        $this->authorize('delete', $permission);
        $this->service->delete($permission);

        return response()->json([
            'success' => true,
            'message' => 'Permission deleted successfully.',
        ]);
    }
}
