<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Api\V1\ApiBaseController;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Modules\Auth\Requests\ApiLoginRequest;
use App\Modules\Auth\Services\LoginActivityService;
use App\Modules\Parents\Models\Guardian;
use App\Core\Tenant\SchoolContext;
use App\Http\Resources\Api\V1\StudentListResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthController extends ApiBaseController
{
    public function __construct(private readonly LoginActivityService $loginActivityService) {}

    public function login(ApiLoginRequest $request): JsonResponse
    {
        $email = $request->string('email')->toString();
        $password = $request->string('password')->toString();

        $user = User::query()->where('email', $email)->first();

        Log::debug('[API LOGIN] Attempt', [
            'email'            => $email,
            'user_found'       => $user !== null,
            'user_id'          => $user?->id,
            'current_school_id'=> $user?->current_school_id,
            'user_status'      => $user?->status,
            'hash_check_pass'  => $user ? Hash::check($password, $user->password) : false,
        ]);

        if (! $user || ! Hash::check($password, $user->password)) {
            $this->loginActivityService->recordFailure($request, 'Invalid API credentials');
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        if ($user->status !== 'active') {
            $this->loginActivityService->recordFailure($request, 'Inactive user');
            return $this->error('This account is not active.', Response::HTTP_FORBIDDEN);
        }

        // ───────────────────────────────────────────────────────────────
        // 1. Resolve school_id with a hard fallback chain
        // ───────────────────────────────────────────────────────────────
        $schoolId = $this->resolveSchoolId($request, $user);

        Log::debug('[API LOGIN] School context', [
            'resolved_school_id' => $schoolId,
            'permissions_team_id' => $schoolId,
        ]);

        app(SchoolContext::class)->set($schoolId);
        app(PermissionRegistrar::class)->setPermissionsTeamId($schoolId);

        // ───────────────────────────────────────────────────────────────
        // 2. Role & permission resolution (must come AFTER team scope)
        // ───────────────────────────────────────────────────────────────
        $user->load('roles');
        $roleNames = $user->getRoleNames();
        $hasParentRole = $roleNames->contains('Parent');

        Log::debug('[API LOGIN] Role resolution', [
            'user_id'               => $user->id,
            'role_names'            => $roleNames->toArray(),
            'has_parent_role'       => $hasParentRole,
            'permissions_team_id'   => app(PermissionRegistrar::class)->getPermissionsTeamId(),
        ]);

        $abilities = $user->getAllPermissions()->pluck('name')->values()->all();
        $token = $user->createToken(
            $request->input('device_name', 'school-erp-api'),
            $abilities ?: ['dashboard.view']
        );

        $this->loginActivityService->recordSuccess($request, $user);

        // ───────────────────────────────────────────────────────────────
        // 3. Build response
        // ───────────────────────────────────────────────────────────────
        $response = [
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
            'school_id' => $schoolId,
        ];

        if ($hasParentRole) {
            $guardian = $this->resolveGuardian($user);
            $response['students'] = $guardian
                ? $this->loadLinkedStudents($guardian)
                : [];
            $response['parent_uuid'] = $guardian?->uuid;

            Log::debug('[API LOGIN] Parent data', [
                'guardian_found'  => $guardian !== null,
                'guardian_id'     => $guardian?->id,
                'guardian_uuid'   => $guardian?->uuid,
                'students_count'  => count($response['students'] ?? []),
            ]);
        } else {
            Log::debug('[API LOGIN] Non-parent user — skipping students', [
                'user_id'    => $user->id,
                'role_names' => $roleNames->toArray(),
            ]);
        }

        Log::debug('[API LOGIN] Response keys', ['keys' => array_keys($response)]);

        return $this->success($response, 'Logged in successfully.');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        // Resolve school context from authenticated user
        $schoolId = $user->current_school_id
            ?: $user->schools()->wherePivot('status', 'active')->value('schools.id');

        if ($schoolId) {
            app(SchoolContext::class)->set($schoolId);
            app(PermissionRegistrar::class)->setPermissionsTeamId($schoolId);
        }

        $user->load('roles');

        $response = [
            'user' => new UserResource($user),
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name')->values(),
        ];

        if ($user->hasRole('Parent')) {
            $guardian = $this->resolveGuardian($user);
            $response['students'] = $guardian
                ? $this->loadLinkedStudents($guardian)
                : [];
            $response['parent_uuid'] = $guardian?->uuid;
        }

        return $this->success($response);
    }

    public function refreshToken(Request $request): JsonResponse
    {
        $user = $request->user();
        $request->user()->currentAccessToken()->delete();

        $abilities = $user->getAllPermissions()->pluck('name')->values()->all();
        $token = $user->createToken(
            $request->input('device_name', 'school-erp-api'),
            $abilities ?: ['dashboard.view']
        );

        return $this->success([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
        ], 'Token refreshed successfully.');
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $request->user()?->currentAccessToken()?->delete();

        if ($user) {
            $this->loginActivityService->recordLogout($request, $user);
        }

        return $this->success(message: 'Logged out successfully.');
    }

    // ─────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────

    /**
     * Resolve school_id via fallback chain:
     *  1. Request param 'school_id'
     *  2. $user->current_school_id
     *  3. Guardian record's school_id
     *  4. First linked student's school_id
     *  5. User's first active school relationship
     */
    private function resolveSchoolId(Request $request, User $user): ?int
    {
        // 1. Explicit request parameter
        if ($request->filled('school_id')) {
            return (int) $request->input('school_id');
        }

        // 2. User's current_school_id
        if ($user->current_school_id) {
            return $user->current_school_id;
        }

        // 3. Guardian record's school_id
        $guardian = $user->guardian;
        if ($guardian && $guardian->school_id) {
            return $guardian->school_id;
        }

        // 4. Try finding guardian by email fallback, then get school_id
        if ($user->email && !$guardian) {
            $guardianByEmail = Guardian::query()->withoutGlobalScopes()->where('email', $user->email)->first();
            if ($guardianByEmail && $guardianByEmail->school_id) {
                return $guardianByEmail->school_id;
            }
        }

        // 5. If we had a guardian, check their students
        if ($guardian) {
            $student = $guardian->students()->first();
            if ($student && $student->school_id) {
                return $student->school_id;
            }
        }

        // 6. User's school relationships
        $schoolId = $user->schools()->wherePivot('status', 'active')->value('schools.id');
        if ($schoolId) {
            return $schoolId;
        }

        return null;
    }

    /**
     * Resolve the Guardian record for a parent user.
     *
     * 1. Direct $user->guardian HasOne relationship
     * 2. Fallback: find Guardian by matching email
     *    (handles cases where user_id was not set on guardian)
     */
    private function resolveGuardian(User $user): ?Guardian
    {
        $guardian = $user->guardian;

        if ($guardian) {
            return $guardian;
        }

        // Fallback: try to find guardian by email
        if ($user->email) {
            $guardianByEmail = Guardian::query()->withoutGlobalScopes()
                ->where('email', $user->email)
                ->first();

            if ($guardianByEmail) {
                Log::debug('[API LOGIN] Guardian found by email fallback', [
                    'user_email'         => $user->email,
                    'guardian_id'        => $guardianByEmail->id,
                    'guardian_user_id'   => $guardianByEmail->user_id,
                ]);

                // Auto-link for future queries
                $guardianByEmail->user_id = $user->id;
                $guardianByEmail->save();

                return $guardianByEmail;
            }
        }

        return null;
    }

    /**
     * Load linked students from a guardian record.
     */
    private function loadLinkedStudents(Guardian $guardian): array
    {
        return $guardian->students()
            ->with(['currentSession.classSection.schoolClass', 'currentSession.classSection.section'])
            ->get()
            ->map(fn ($student) => [
                'id' => $student->id,
                'uuid' => $student->uuid,
                'name' => $student->full_name,
                'class' => $student->currentSession->first()?->classSection?->schoolClass?->name ?? '',
                'section' => $student->currentSession->first()?->classSection?->section?->name ?? '',
                'roll_number' => $student->currentSession->first()?->roll_no ?? '',
                'admission_no' => $student->admission_no,
                'photo' => $student->photo_path ? url('storage/' . $student->photo_path) : null,
            ])
            ->values()
            ->all();
    }
}
