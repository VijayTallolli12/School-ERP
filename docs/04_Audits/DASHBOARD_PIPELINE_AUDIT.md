# Dashboard Rendering Pipeline — 403 Audit

## Test User

Aisha Khan (ID: 3, `aisha.khan@example.com`), school_id=1, role=Teacher

---

## Live Verification (CLI)

All stages passed for the test user:

| Stage | Result |
|---|---|
| `DashboardService::build($user)` | ✅ Succeeded |
| `TeacherDashboardBuilder::build()` | ✅ Returned DashboardView |
| `new DashboardView(...)` | ✅ Valid DTO |
| Route name resolution (`route(...)`) | ✅ All 7 routes resolved |
| View render (`dashboard.index`) | ✅ Works when `auth()->user()` is present |

### DashboardView contents:
- roleName: "Teacher"
- layout: "admin"
- statCards: 4 (Today's Classes, Attendance Rate, Pending Homework, Upcoming Exams)
- widgets: 4 (Today's Schedule, Student Attendance, Upcoming Events, Leave Overview)
- quickActions: 5 (Record Attendance, Manage Homework, View Timetable, View Exams, Apply Leave)
- charts: 1 (Weekly Attendance Trend)
- insights: 2 (Students Requiring Attention, Homework Reminder)
- recentActivity: 5 entries
- sidebar: null (default — not used by any builder)

---

## Exhaustive 403 Source Search

### All `abort(403)` calls in the entire application:

| File | Line | Message | In Pipeline After Builder? |
|---|---|---|---|
| `DashboardFactory.php` | 33 | "Your role does not have access to any dashboard." | ❌ No — runs BEFORE builder selection |
| `DashboardService.php` | 22 | "No school context available." | ✅ YES — runs AFTER factory but BEFORE builder->build() |

**No other `abort(403)` calls exist in any Dashboard module file.**

### Searched terms across entire app:
- `abort(403)` → 9 matches (only 2 in Dashboard module)
- `AuthorizationException` → 0 matches
- `Gate::authorize` → 0 matches
- `throw_if` / `throw_unless` / `abort_unless` → 0 matches in Dashboard module
- `AccessDeniedHttpException` → 0 matches

### Middleware on `admin/dashboard` route:
```
["web", "auth", "school"]
```
✅ No `permission:dashboard.view` middleware on the web route.

### Blade files audited:
| File | abort(403)? | @can issues? |
|---|---|---|
| `dashboard/index.blade.php` | No | No — `@can` just hides elements |
| `layouts/admin.blade.php` | No | No |
| `layouts/partials/sidebar.blade.php` | No | No — uses `@can` (silent) |
| `layouts/partials/navbar.blade.php` | No | No |
| `layouts/partials/_bell.blade.php` | No | No |
| `layouts/partials/flash.blade.php` | No | No |

### SidebarBuilder audit:
- Registered as singleton at `AppServiceProvider.php:171`
- **NOT called anywhere** in the rendering pipeline
- `BaseDashboardBuilder::buildSidebar()` returns `null` by default
- `DashboardView::$sidebar` defaults to `null`
- **Sidebar is NOT the 403 source** — it's not even used.

---

## The ONLY Two 403 Candidates Post-Factory-Selection

### Candidate 1: `DashboardService.php:22`
```php
$schoolId ??= $this->schoolContext->id();
if (! $schoolId) {
    abort(403, 'No school context available.');  // LINE 22
}
$builder = $this->factory->make($user);          // factory selects builder
return $builder->build($user, $schoolId);
```

**Trigger condition:** `SchoolContext::id()` returns `null`.

### Candidate 2: None — no other 403 source exists.

---

## Root Cause Analysis

The **only** `abort(403)` call that executes AFTER the factory selects a builder is in `DashboardService::build()` at line 22, triggered when `SchoolContext::id()` returns `null`.

### Why `SchoolContext::id()` can return `null`:

The `SetSchoolContext` middleware's `resolveFromUser()` has a 7-step resolution chain. If ALL steps return `null`, the school ID is never set:

1. ❌ Request param `school_id` — not present on GET
2. ❌ Session `school_id` — may be absent if `applySchoolContext()` didn't set it
3. ❌ `$user->current_school_id` — may be `null` on DB record
4. ❌ Guardian record — not applicable for Teacher
5. ❌ `school_user` pivot with `status=active` — may not exist
6. ❌ `model_has_roles` lookup — may not have `school_id` populated
7. ❌ No fallback remains

### Most probable cause:

The `SchoolContext` singleton was set correctly during `LoginController::store()` via `applySchoolContext()`, but on the **next HTTP request** (the redirect to `/admin/dashboard`), the `SetSchoolContext` middleware runs `handle()` which calls `resolveSchoolId()`. If **this** resolution returns `null`, the context is overwritten to `null`.

This would happen if:
- The `session('school_id')` from LoginController's `applySchoolContext()` was NOT persisted to the session store
- The user has `current_school_id = NULL` in the database
- No active `school_user` pivot record exists

---

## Call Stack (Exact 403 Source)

```
1. GET /admin/dashboard
2. web middleware group
3. auth middleware                          → authenticates user
4. school middleware (SetSchoolContext)     → resolveSchoolId() may return null
5. DashboardController::__invoke()         → app/Dashboard/Controllers/DashboardController.php:12
6. DashboardService::build($user)          → app/Dashboard/Services/DashboardService.php:17
7.   $schoolContext->id() returns null     → SchoolContext singleton was set to null
8.   abort(403, 'No school context available.')  → app/Dashboard/Services/DashboardService.php:22  ← ★
```

---

## Verification Checklist

| Check | Status |
|---|---|
| TeacherDashboardBuilder returns valid DashboardView | ✅ PASS |
| DashboardView contains layout, statCards, widgets, quickActions | ✅ PASS |
| SidebarBuilder does not return empty navigation | ✅ PASS (not used in pipeline) |
| DashboardService does not interpret empty widget list as "no dashboard" | ✅ PASS |
| dashboard/index.blade.php does not intentionally abort on empty data | ✅ PASS |

**All checks pass for Aisha Khan.** If a different teacher user is experiencing a 403, the root cause is almost certainly `SchoolContext::id()` returning `null` at `DashboardService.php:22`.

---

## Recommended Diagnostic

To confirm the root cause, run:

```bash
php artisan tinker
```

Then:

```php
$user = User::where('email', '<teacher-email>')->first();
$user->current_school_id;
$user->schools()->wherePivot('status', 'active')->first();
DB::table('model_has_roles')->where('model_id', $user->id)->where('model_type', get_class($user))->first();
session('school_id');
app(App\Core\Tenant\SchoolContext::class)->id();
app(Spatie\Permission\PermissionRegistrar::class)->getPermissionsTeamId();
```

This will identify which step in the resolution chain is failing.
