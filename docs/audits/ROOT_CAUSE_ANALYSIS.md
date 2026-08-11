# Root Cause Analysis: 403 on Teacher Dashboard

## Symptom

Teacher receives HTTP 403 `"No school context available."` after login redirect to `/admin/dashboard`.

## Call Chain

```
POST /login
  LoginController::store()
    $user->hasRole('Teacher')                    # OK — team ID set
    redirect()->route('admin.dashboard')

  ── redirect follows ──▶

GET /admin/dashboard
  auth middleware                                 # OK — session authenticates user
  SetSchoolContext::handle()
    resolveSchoolId($request)                     # Returns null (all 7 steps fail)
    app(SchoolContext)->set(null)                 # OVERWRITES valid context!
    app(PermissionRegistrar)->setPermissionsTeamId(null)  # Clears team ID!
  DashboardController
    DashboardService::build()
      SchoolContext::id()                         # null
      abort(403, 'No school context available.')  # ❌
```

## Root Cause: `SetSchoolContext::handle()` line 17-18

```php
$schoolId = $this->resolveSchoolId($request);
app(SchoolContext::class)->set($schoolId);                         // Line 17
app(PermissionRegistrar::class)->setPermissionsTeamId($schoolId); // Line 18
```

`SchoolContext::set(null)` and `PermissionRegistrar::setPermissionsTeamId(null)` are called **unconditionally** — even when `$schoolId` is null.

Compare with `applySchoolContext()` (`SetSchoolContext.php:132-136`) which correctly guards all three mutations behind `if ($schoolId)`.

## Why Resolution Fails (null)

The 7-step chain returns null when a teacher user:

| Step | Condition | Fails When |
|------|-----------|------------|
| 1 | Request param/header | No explicit `school_id` in GET request |
| 2 | Session school_id | Session not persisted, OR user not in `school_user` pivot for that school |
| 3 | `current_school_id` | DB column is null |
| 4 | Guardian school_id | Not a guardian |
| 5 | `school_user` pivot | No record, or status != active |
| 6 | `model_has_roles.school_id` | `school_id` is null in role assignment (no team ID) |
| 7 | Fallback | — |

**Likely scenario:** User has a role with `model_has_roles.school_id = NULL` (Spatie created the record without a team context) and no other school linkage.

## Why `applySchoolContext()` Works but `handle()` Fails

- `applySchoolContext()` is called inside `LoginController::store()` before the redirect
- It correctly sets `session(['school_id' => 1])` because `resolveFromUser()` succeeds at step 3 (`current_school_id`) or step 5/6
- On the **next HTTP request** (GET /admin/dashboard), `SetSchoolContext::handle()` runs fresh
- If the session hasn't persisted the `school_id`, or if the user's `school_user` check in step 2 fails, all 7 steps return null
- **The existing valid context is unconditionally overwritten with null**

## Fix Strategy

Guard all mutations in `handle()` the same way `applySchoolContext()` does:

```php
if ($schoolId) {
    app(SchoolContext::class)->set($schoolId);
    app(PermissionRegistrar::class)->setPermissionsTeamId($schoolId);
    session(['school_id' => $schoolId]);
}
// If null — preserve existing context, don't overwrite
```

This is a one-line structural change (move the two set() calls inside the existing `if ($schoolId)` block) and matches the proven-safe pattern in `applySchoolContext()`.
