<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Api\V1\ApiBaseController;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Modules\Auth\Requests\ApiLoginRequest;
use App\Modules\Auth\Services\LoginActivityService;
use App\Core\Tenant\SchoolContext;
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
        $user = User::query()->where('email', $request->string('email'))->first();

        if (! $user || ! Hash::check($request->string('password')->toString(), $user->password)) {
            $this->loginActivityService->recordFailure($request, 'Invalid API credentials');

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        if ($user->status !== 'active') {
            $this->loginActivityService->recordFailure($request, 'Inactive user');

            return $this->error('This account is not active.', Response::HTTP_FORBIDDEN);
        }

        $schoolId = $request->integer('school_id') ?: $user->current_school_id;
        app(SchoolContext::class)->set($schoolId);
        app(PermissionRegistrar::class)->setPermissionsTeamId($schoolId);

        $abilities = $user->getAllPermissions()->pluck('name')->values()->all();
        $token = $user->createToken(
            $request->input('device_name', 'school-erp-api'),
            $abilities ?: ['dashboard.view']
        );

        $this->loginActivityService->recordSuccess($request, $user);

        return $this->success([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'user' => new UserResource($user->load('roles')),
            'school_id' => $schoolId,
        ], 'Logged in successfully.');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('roles');

        return $this->success([
            'user' => new UserResource($user),
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name')->values(),
        ]);
    }

    public function refreshToken(Request $request): JsonResponse
    {
        $user = $request->user();

        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        // Issue new token
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
}
