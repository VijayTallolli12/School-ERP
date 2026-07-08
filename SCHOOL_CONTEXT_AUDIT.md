# School Context Middleware Audit

## File: `app/Http/Middleware/SetSchoolContext.php`

## Resolution Chain

`resolveSchoolId()` (private, instance) → `resolveFromUser()` (public, static). The static method is shared by both `handle()` (middleware) and `applySchoolContext()` (called from `LoginController`).

### `resolveFromUser()` — 7-Step Fallback Chain

| Step | Source | Guard | Requires |
|------|--------|-------|----------|
| 1 | `$request->integer('school_id')` or `X-School-Id` header | `$request` non-null + user owns school | Explicit param |
| 2 | `session('school_id')` | `$request` non-null + user owns school (`school_user` pivot) | Session persisted |
| 3 | `$user->current_school_id` | truthy | DB column populated |
| 4 | `$user->guardian->school_id` | `method_exists($user, 'guardian')` | Guardian role only |
| 5 | `school_user` pivot where `status=active` | truthy | `school_user` record exists |
| 6 | `model_has_roles.school_id` | `whereNotNull('school_id')` | Role assignment has team ID |
| 7 | `return null` | — | All above fail |

**All 7 steps can return `null`** if:
- No request param/header sent
- Session `school_id` missing OR user doesn't belong to that school
- `current_school_id` is null
- Not a guardian
- No `school_user` pivot
- `model_has_roles.school_id` is null

## Critical Inconsistency: `handle()` vs `applySchoolContext()`

### `applySchoolContext()` (called from `LoginController::store()`) — SAFE

```php
$schoolId = static::resolveFromUser($user, $request);
if ($schoolId) {
    app(SchoolContext::class)->set($schoolId);
    app(PermissionRegistrar::class)->setPermissionsTeamId($schoolId);
    session(['school_id' => $schoolId]);
}
return $schoolId;
```

- Guards all three mutations behind `if ($schoolId)`
- If resolution fails, existing context is preserved

### `handle()` (middleware, runs on EVERY request) — UNSAFE

```php
$schoolId = $this->resolveSchoolId($request);
app(SchoolContext::class)->set($schoolId);                           // Unconditional!
app(PermissionRegistrar::class)->setPermissionsTeamId($schoolId);   // Unconditional!
if ($schoolId) {
    session(['school_id' => $schoolId]);
}
```

- Calls `SchoolContext::set(null)` when resolution fails — **overwrites valid context**
- Calls `PermissionRegistrar::setPermissionsTeamId(null)` — **clears team ID for Spatie roles**
- Only `session()` write is guarded (but the damage is already done)

## Impact

When `handle()` runs on the redirect-following GET request:
1. `SchoolContext` singleton is overwritten with `null`
2. `PermissionRegistrar` team ID is cleared to `null`
3. Spatie `hasRole()` calls on subsequent render produce false negatives
4. `DashboardService::build()` at line 22 sees `SchoolContext::id()` = null → `abort(403)`
