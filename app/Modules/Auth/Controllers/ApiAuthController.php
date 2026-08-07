<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Api\V1\ApiBaseController;
use App\Http\Middleware\SetSchoolContext;
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

        app(SchoolContext::class)->set($schoolId);
        app(PermissionRegistrar::class)->setPermissionsTeamId($schoolId);

        // ───────────────────────────────────────────────────────────────
        // 2. Role & permission resolution (must come AFTER team scope)
        // ───────────────────────────────────────────────────────────────
        $user->load('roles');
        $roleNames = $user->getRoleNames();
        $hasParentRole = $roleNames->contains('Parent');

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
        }

        if ($roleNames->contains('Student')) {
            $response['student'] = $this->studentContext($user);
        }

        return $this->success($response, 'Logged in successfully.');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        // Use the centralized school context resolver (same as SetSchoolContext middleware)
        $schoolId = SetSchoolContext::resolveFromUser($user, $request);

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

        if ($user->hasRole('Student')) {
            $response['student'] = $this->studentContext($user);
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

    /**
     * Update the authenticated user's profile (mobile apps).
     *
     * Fields accepted: phone, email, address, profile_photo.
     * For Student users, address/phone also persist onto the linked Student record.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'phone' => ['sometimes', 'string', 'max:20'],
            'email' => ['sometimes', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($user->id)],
            'address' => ['sometimes', 'nullable', 'string', 'max:500'],
            'profile_photo' => ['sometimes', 'nullable', 'string', 'max:500'],
        ]);

        $updates = [];
        if (array_key_exists('phone', $validated)) {
            $updates['phone'] = $validated['phone'];
        }
        if (array_key_exists('email', $validated)) {
            $updates['email'] = $validated['email'];
        }
        if (array_key_exists('address', $validated)) {
            $updates['address'] = $validated['address'];
        }
        if (array_key_exists('profile_photo', $validated)) {
            $updates['avatar_path'] = $validated['profile_photo'];
        }

        if (! empty($updates)) {
            $user->update($updates);
        }

        // Mirror address onto the linked student profile (student users)
        $student = $user->student;
        if ($student && (array_key_exists('address', $validated) || array_key_exists('phone', $validated))) {
            $studentUpdates = [];
            if (array_key_exists('address', $validated)) {
                $studentUpdates['current_address'] = $validated['address'];
            }
            if (array_key_exists('phone', $validated)) {
                $studentUpdates['phone'] = $validated['phone'];
            }
            if (! empty($studentUpdates)) {
                $student->update($studentUpdates);
            }
        }

        return $this->success([
            'user' => new UserResource($user->fresh()),
            'student' => $student ? $this->studentContext($user->loadMissing('student')) : null,
        ], 'Profile updated successfully.');
    }

    /**
     * Change the authenticated user's password (mobile apps).
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'different:current_password'],
            'confirm_password' => ['required', 'string', 'same:new_password'],
        ]);

        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return $this->error('Current password is incorrect.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->update(['password' => Hash::make($validated['new_password'])]);

        return $this->success(message: 'Password changed successfully.');
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
            $requested = (int) $request->input('school_id');

            if ($user->isSuperAdmin() || $user->schools()->whereKey($requested)->exists()) {
                return $requested;
            }
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
                // Auto-link for future queries
                $guardianByEmail->user_id = $user->id;
                $guardianByEmail->save();

                return $guardianByEmail;
            }
        }

        return null;
    }

    /**
     * Load a compact student context for a student user.
     */
    private function studentContext(User $user): ?array
    {
        $student = $user->student;

        if (! $student) {
            return null;
        }

        $student->load(['sessions.classSection.schoolClass', 'sessions.classSection.section', 'sessions.academicYear']);

        $session = $student->sessions->where('status', 'active')->first();

        return [
            'uuid' => $student->uuid,
            'name' => $student->full_name,
            'admission_no' => $student->admission_no,
            'class' => $session?->classSection?->schoolClass?->name ?? '',
            'section' => $session?->classSection?->section?->name ?? '',
            'roll_number' => $session?->roll_no ?? '',
            'academic_year' => $session?->academicYear?->name ?? '',
            'photo' => $student->photo_path ? url('storage/' . $student->photo_path) : null,
        ];
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
