<?php

namespace App\Modules\Users\Controllers;

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
        $roles = Role::all();
        $schools = School::all();
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
            $query->where('status', $request->status);
        }

        if ($request->filled('school_id')) {
            $query->where('current_school_id', $request->school_id);
        }

        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->created_from);
        }

        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->created_to);
        }

        return DataTables::of($query)
            ->addColumn('role_label', function (User $user) {
                $role = $user->roles->first();
                if (! $role) {
                    return '<span class="badge bg-secondary">No Role</span>';
                }

                $badgeClass = match ($role->name) {
                    'Super Admin' => 'bg-danger',
                    'School Admin' => 'bg-warning text-dark',
                    'Principal' => 'bg-info',
                    'Accountant' => 'bg-primary',
                    'Teacher' => 'bg-success',
                    'Parent' => 'bg-purple',
                    'Student' => 'bg-teal',
                    default => 'bg-secondary',
                };

                return '<span class="badge ' . $badgeClass . '">' . e($role->name) . '</span>';
            })
            ->addColumn('school_name', fn (User $user) => $user->currentSchool?->name ?? '<span class="text-muted">—</span>')
            ->addColumn('status_label', function (User $user) {
                $badgeClass = $user->status === 'active' ? 'bg-success' : 'bg-danger';

                return '<span class="badge ' . $badgeClass . '">' . ucfirst($user->status) . '</span>';
            })
            ->addColumn('actions', function (User $user) {
                return view('modules.users._actions', compact('user'))->render();
            })
            ->rawColumns(['role_label', 'school_name', 'status_label', 'actions'])
            ->make(true);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();
        $roleName = $request->role;

        // Only Super Admin can create admins/principals
        if (in_array($roleName, ['Super Admin', 'School Admin', 'Principal'], true) && ! $currentUser->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only Super Admin can create admin or principal accounts.',
            ], 403);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'status' => $request->status,
            'current_school_id' => $request->school_id,
            'force_password_change' => false,
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->update(['avatar_path' => $path]);
        }

        // Assign role
        $user->syncRoles([$roleName]);

        // Attach school to pivot if provided
        if ($request->filled('school_id')) {
            $user->schools()->syncWithoutDetaching([$request->school_id => [
                'status' => 'active',
                'is_primary' => true,
            ]]);
        }

        return response()->json([
            'success' => true,
            'message' => 'User created successfully.',
        ]);
    }

    public function show(User $user): JsonResponse
    {
        $user->load(['roles', 'schools']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'status' => $user->status,
                'role' => $user->roles->first()?->name,
                'school_id' => $user->current_school_id,
                'avatar_url' => $user->avatar_path ? asset('storage/' . $user->avatar_path) : null,
            ],
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'status' => $request->status,
        ];

        if ($request->filled('school_id')) {
            $data['current_school_id'] = $request->school_id;
        }

        $user->update($data);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->update(['avatar_path' => $path]);
        }

        // Update role if provided
        if ($request->filled('role')) {
            $roleName = $request->role;

            if (in_array($roleName, ['Super Admin', 'School Admin', 'Principal'], true) && ! $currentUser->isSuperAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only Super Admin can assign admin or principal roles.',
                ], 403);
            }

            $user->syncRoles([$roleName]);
        }

        // Sync school pivot
        if ($request->filled('school_id')) {
            $user->schools()->syncWithoutDetaching([$request->school_id => [
                'status' => 'active',
                'is_primary' => true,
            ]]);
        }

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.',
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        // Prevent deleting self
        if ($user->is(auth()->user())) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account.',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.',
        ]);
    }

    public function toggleStatus(User $user): JsonResponse
    {
        // Prevent deactivating self
        if ($user->is(auth()->user())) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot change your own status.',
            ], 403);
        }

        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => 'User ' . $newStatus . ' successfully.',
            'data' => ['status' => $newStatus],
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request, User $user): JsonResponse
    {
        $user->update([
            'password' => Hash::make($request->password),
            'force_password_change' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully for ' . $user->name . '.',
        ]);
    }

    public function assignRole(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'role' => ['required', 'exists:roles,name'],
        ]);

        /** @var User $currentUser */
        $currentUser = $request->user();
        $roleName = $request->role;

        if (in_array($roleName, ['Super Admin', 'School Admin', 'Principal'], true) && ! $currentUser->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only Super Admin can assign admin or principal roles.',
            ], 403);
        }

        $user->syncRoles([$roleName]);

        return response()->json([
            'success' => true,
            'message' => 'Role assigned successfully.',
        ]);
    }
}