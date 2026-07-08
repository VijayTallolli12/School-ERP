# Fix Implementation Report

## File Changed

`app/Http/Middleware/SetSchoolContext.php:13-24` — `handle()` method.

## Before

```php
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
```

## After

```php
public function handle(Request $request, Closure $next): Response
{
    $schoolId = $this->resolveSchoolId($request);

    if ($schoolId) {
        app(SchoolContext::class)->set($schoolId);
        app(PermissionRegistrar::class)->setPermissionsTeamId($schoolId);
        session(['school_id' => $schoolId]);
    }

    return $next($request);
}
```

## What Changed

Moved `SchoolContext::set()` and `PermissionRegistrar::setPermissionsTeamId()` inside the existing `if ($schoolId)` guard. These two calls were previously unconditional (lines 17-18), executing even when `$schoolId` was null.

## Why This Fixes the 403

Before the fix:
1. Login sets `SchoolContext` = 1, team ID = 1, session school_id = 1
2. Dashboard GET request runs `SetSchoolContext::handle()`
3. `resolveSchoolId()` returns null (all 7 steps fail)
4. `SchoolContext::set(null)` overwrites the valid context with null
5. `PermissionRegistrar::setPermissionsTeamId(null)` clears the Spatie team ID
6. `DashboardService::build()` calls `SchoolContext::id()` → null → `abort(403)`

After the fix:
1–3. Same
4. `if ($schoolId)` is false → `set()` calls are skipped
5. Existing `SchoolContext` and team ID are preserved from login
6. `DashboardService::build()` sees valid context → proceeds normally

## Safety

This matches the exact guard pattern already used by `applySchoolContext()` (`SetSchoolContext.php:132-136`), which has been working correctly in production. On subsequent page loads where the user navigates between schools, `resolveSchoolId()` will resolve the correct school from the session (step 2) or from `current_school_id` (step 3), and `handle()` will update the context normally.

## What Happens When No School Is Resolved

If a user truly has no school association (e.g., new unassigned user), the middleware preserves whatever context was set before (or leaves it null if none was set). The `DashboardService` will still abort with 403 for truly unassignable users — which is correct behavior. The difference is that users who have a valid school context from login are no longer penalized by a null overwrite on the next request.
