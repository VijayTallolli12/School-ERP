<?php

namespace App\Http\Middleware;

use App\Core\Tenant\SchoolContext;
use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class SetSchoolContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $schoolId = $this->resolveSchoolId($request);

        if ($schoolId) {
            app(SchoolContext::class)->set($schoolId);
            app(PermissionRegistrar::class)->setPermissionsTeamId($schoolId);
            session(['school_id' => $schoolId]);

            if ($request->user()) {
                $request->user()->unsetRelation('roles');
            }
        }

        return $next($request);
    }

    /**
     * Resolve the school ID for a given user.
     * Public static so it can be reused by LoginController or other classes
     * before the middleware pipeline runs.
     */
    public static function resolveSchoolForUser(?int $userId): ?int
    {
        if (! $userId) {
            return null;
        }

        $user = \App\Models\User::find($userId);

        if (! $user) {
            return null;
        }

        return static::resolveFromUser($user);
    }

    /**
     * Resolve school ID from the request, trying multiple resolution strategies.
     */
    private function resolveSchoolId(Request $request): ?int
    {
        $user = $request->user();

        if (! $user) {
            return null;
        }

        return static::resolveFromUser($user, $request);
    }

    /**
     * Central school resolution logic.
     *
     * Tries (in order):
     * 1. Explicit request parameter or header
     * 2. Session school_id
     * 3. User's current_school_id
     * 4. Guardian record's school_id
     * 5. User's active school relationships (school_user pivot)
     * 6. model_has_roles table (most reliable fallback for role-bearing users)
     * 7. First school assignment from model_has_roles
     */
    public static function resolveFromUser(\App\Models\User $user, ?Request $request = null): ?int
    {
        // 1. Explicit request parameter or header
        if ($request) {
            $requested = $request->integer('school_id') ?: (int) $request->header('X-School-Id');
            if ($requested && ($user->isSuperAdmin() || $user->schools()->whereKey($requested)->exists())) {
                return $requested;
            }

            // 2. Session school_id
            if (session('school_id') && ($user->isSuperAdmin() || $user->schools()->whereKey(session('school_id'))->exists())) {
                return (int) session('school_id');
            }
        }

        // 3. User's current_school_id
        if ($user->current_school_id) {
            return $user->current_school_id;
        }

        // 4. Guardian record's school_id (handles parent users)
        if (method_exists($user, 'guardian')) {
            $guardian = $user->guardian;
            if ($guardian && $guardian->school_id) {
                return $guardian->school_id;
            }
        }

        // 5. User's active school relationships
        $schoolId = $user->schools()->wherePivot('status', 'active')->value('schools.id');
        if ($schoolId) {
            return (int) $schoolId;
        }

        // 6. Fallback: query model_has_roles to find the user's school
        // This is the most reliable when teams are enabled because
        // role assignments always store the correct school_id.
        $schoolIdFromRoles = \Illuminate\Support\Facades\DB::table('model_has_roles')
            ->where('model_id', $user->getKey())
            ->where('model_type', $user->getMorphClass())
            ->whereNotNull('school_id')
            ->value('school_id');

        if ($schoolIdFromRoles) {
            return (int) $schoolIdFromRoles;
        }

        return null;
    }

    /**
     * Apply school context (SchoolContext singleton + PermissionRegistrar team ID + session)
     * for a given user. Used by LoginController before role checks.
     * Also persists school_id to session so subsequent requests can reuse it.
     */
    public static function applySchoolContext(\App\Models\User $user, ?Request $request = null): ?int
    {
        $schoolId = static::resolveFromUser($user, $request);

        if ($schoolId) {
            app(SchoolContext::class)->set($schoolId);
            app(PermissionRegistrar::class)->setPermissionsTeamId($schoolId);
            session(['school_id' => $schoolId]);
        }

        return $schoolId;
    }
}
