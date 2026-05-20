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

        // Temporary debug logging — remove after confirming the fix
        Log::debug('API Login attempt', [
            'email'           => $email,
            'user_found'      => $user !== null,
            'stored_hash'     => $user?->password,
            'hash_algo_info'  => $user ? password_get_info($user->password) : null,
            'hash_check_pass' => $user ? Hash::check($password, $user->password) : false,
            'hash_driver'     => config('hashing.driver'),
        ]);

        if (! $user || ! Hash::check($password, $user->password)) {
            Log::warning('API Login failed — credentials mismatch', [
                'email'  => $email,
                'reason' => $user ? 'password mismatch' : 'user not found',
            ]);

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

        Log::debug('API Login success', [
            'email'    => $email,
            'token_id' => $token->accessToken->id,
        ]);

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
