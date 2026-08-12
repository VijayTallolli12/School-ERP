<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures only internal ERP staff users can access the administrative
 * School ERP web application.
 *
 * Parents and Students (external roles) are blocked from every /admin/*
 * route regardless of how they reached it: normal login redirect, a
 * bookmarked dashboard URL, a manually entered admin route, or a forged
 * request. The backend enforces this — it is not a frontend-only restriction.
 */
class EnsureStaffUser
{
    public function handle(Request $request, Closure $next): Response
    {
        // Resolve the authenticated user regardless of guard (web session or
        // Sanctum token). Some internal staff routes authenticate via
        // `auth:sanctum` (e.g. the Reports module), so we must not assume the
        // default `web` guard.
        $user = $request->user('sanctum') ?? $request->user();

        if (! $user) {
            abort(403, 'Unauthenticated staff access denied.');
        }

        // Super Admin always has access (system-level role).
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        foreach (config('access.external_roles', ['Parent', 'Student']) as $role) {
            if ($user->hasRole($role)) {
                abort(403, 'This portal is for school staff only. Parents and students use the mobile app.');
            }
        }

        return $next($request);
    }
}
