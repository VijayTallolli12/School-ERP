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

        app(SchoolContext::class)->set($schoolId);
        app(PermissionRegistrar::class)->setPermissionsTeamId($schoolId);

        if ($schoolId) {
            session(['school_id' => $schoolId]);
        }

        return $next($request);
    }

    private function resolveSchoolId(Request $request): ?int
    {
        $user = $request->user();

        if (! $user) {
            return null;
        }

        $requested = $request->integer('school_id') ?: (int) $request->header('X-School-Id');

        if ($requested && ($user->isSuperAdmin() || $user->schools()->whereKey($requested)->exists())) {
            return $requested;
        }

        if (session('school_id') && ($user->isSuperAdmin() || $user->schools()->whereKey(session('school_id'))->exists())) {
            return (int) session('school_id');
        }

        return $user->current_school_id ?: $user->schools()->wherePivot('status', 'active')->value('schools.id');
    }
}
