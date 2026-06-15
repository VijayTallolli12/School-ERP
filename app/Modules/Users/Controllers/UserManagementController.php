<?php

namespace App\Modules\Users\Controllers;

use App\Core\Tenant\SchoolContext;
use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Modules\Users\Requests\ResetPasswordRequest;
use App\Modules\Users\Requests\StoreUserRequest;
use App\Modules\Users\Requests\UpdateUserRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class UserManagementController extends Controller
{
    use AuthorizesRequests;

    public function index(): View
    {
        $schoolId = app(SchoolContext::class)->id();
        $roles = Role::query()->when($schoolId, fn($q, $id) => $q->where('school_id', $id))->get();
        $schools = $schoolId ? collect([app(SchoolContext::class)->school()]) : School::query()->get();
        $statuses = ['active' => 'Active', 'inactive' => 'Inactive'];

        return view('modules.users.index', compact('roles', 'schools', 'statuses'));
    }

    public function data(Request $request): JsonResponse
    {
        $query = User::query()->with(['roles', 'currentSchool']);

        if ($request->filled('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $request->role));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('school_id')) {
            $query->whereHas('schools', fn ($q) => $q->where('schools.id', $request->school_id));
        }

        return DataTables::of($query)
            ->addColumn('role', fn (User $user) => $user->roles->pluck('name')->implode(', '))
            ->addColumn('school', fn (User $user) => $user->currentSchool?->name ?? '-')
            ->editColumn('is_active', fn (User $user) => $user->is_active ? 'Active' : 'Inactive')
            ->addColumn('actions', fn (User $user) => view('modules.users._actions', compact('user'))->render())
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function create(): View
    {
        $schoolId = app(SchoolContext::class)->id();
        $roles = Role::query()->when($schoolId, fn($q, $id) => $q->where('school_id', $id))->get();
        $schools = $schoolId ? collect([app(SchoolContext::class)->school()]) : School::query()->get();

        return view('modules.users.create', compact('roles', 'schools'));
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::query()->create($data);

        if (!empty($data['role'])) {
            $user->assignRole($data['role']);
        }

        if (!empty($data['school_id'])) {
            $user->schools()->sync([$data['school_id']]);
        }

        return response()->json(['success' => true, 'message' => 'User created successfully.']);
    }

    public function show(User $user): View
    {
        $user->load(['roles', 'currentSchool']);

        return view('modules.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $schoolId = app(SchoolContext::class)->id();
        $roles = Role::query()->when($schoolId, fn($q, $id) => $q->where('school_id', $id))->get();
        $schools = $schoolId ? collect([app(SchoolContext::class)->school()]) : School::query()->get();
        $user->load(['roles', 'currentSchool']);

        return view('modules.users.edit', compact('user', 'roles', 'schools'));
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        if (!empty($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        if (isset($data['school_id'])) {
            $user->schools()->sync([$data['school_id']]);
        }

        return response()->json(['success' => true, 'message' => 'User updated successfully.']);
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'You cannot delete yourself.'], 422);
        }

        $user->delete();

        return response()->json(['success' => true, 'message' => 'User deleted successfully.']);
    }

    public function resetPassword(ResetPasswordRequest $request, User $user): JsonResponse
    {
        $user->update(['password' => Hash::make($request->password)]);

        return response()->json(['success' => true, 'message' => 'Password reset successfully.']);
    }

    public function toggleStatus(User $user): JsonResponse
    {
        $user->update(['is_active' => !$user->is_active]);

        return response()->json([
            'success' => true,
            'message' => $user->is_active ? 'User activated.' : 'User deactivated.',
        ]);
    }

    public function assignRole(Request $request, User $user): JsonResponse
    {
        $request->validate(['role' => 'required|string|exists:roles,name']);

        $user->syncRoles([$request->role]);

        return response()->json([
            'success' => true,
            'message' => 'Role assigned successfully.',
        ]);
    }
}
