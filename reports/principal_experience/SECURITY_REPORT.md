# Security Report — Principal Experience (Phase 03)

## Data Isolation

| Concern | Assessment |
|---------|------------|
| Can Principal see other users' leave requests? | **Yes, by design.** The Principal has school-wide visibility (`leave_management.view`). This is a deliberate business requirement for the oversight role. |
| Can Principal see other schools' data? | **No.** All queries are scoped to the current school via `SchoolContext` and `school_id` columns. The `SetSchoolContext` middleware ensures tenant isolation. |
| Can Principal access Admin-only routes? | **No.** Principal does not have `users.view`, `settings.view`, `payroll.view`, or `roles.view` permissions. These routes remain Admin-only. |

## Tenant Isolation

Multi-school isolation is enforced at the **infrastructure layer** via `App\Core\Tenant\SchoolContext`:
- All Eloquent models use `school_id` column
- Global scopes or explicit `where('school_id', $this->schoolContext->id())` are applied
- The `SetSchoolContext` middleware sets the school context per-request
- This pattern is **inherited**, not changed, by Phase 03

## Role-Based Access

| Mechanism | Detail |
|-----------|--------|
| **Sidebar dispatch** | `SidebarBuilder::build()` checks `hasRole('Principal')` at line 15 **before** the Admin fallthrough at line 19. This ensures Principal never accidentally inherits Admin sidebar items |
| **Blade view** | `@elseif(auth()->user()->hasRole('Principal'))` at line 121 — Principal is matched before `@else` |
| **Permission gating** | Every sidebar item and widget is wrapped in `@can('permission.name')` or `$this->can('permission.name')` |

## Permission Enforcement

| Layer | Enforcement Point | Permission Checked |
|-------|------------------|--------------------|
| Route | `permission:leave_management.approve` middleware | `leave_management.approve` |
| Route | `permission:leave_management.view` middleware | `leave_management.view` |
| Policy | `LeaveRequestPolicy::approve()` | `leave_management.approve` |
| Policy | `LeaveRequestPolicy::viewAny()` | `leave_management.view` |
| UI (sidebar) | `@can('leave_management.view')` in Blade | `leave_management.view` |
| UI (widget) | `$this->can('leave_management.approve')` in DashboardBuilder | `leave_management.approve` |
| UI (quick action) | `'permission' => 'leave_management.view'` in DashboardBuilder | `leave_management.view` |

## Notification Target Type

A new target type `'principals'` was added to `NotificationService::resolveTargetUserIds()`:

```php
'principals' => $query->whereHas('roles', fn ($q) => $q->where('name', 'Principal'))->pluck('id')->all(),
```

**Security consideration:** This query is scoped by `$schoolId` (via `User::whereHas('schools', ...)`), ensuring that notifications intended for principals of School A are **not** delivered to principals of School B.

## Threat Analysis

| Threat | Mitigation | Residual Risk |
|--------|-----------|---------------|
| Principal views data from another school | `SchoolContext` scoping on all queries | None |
| Principal accesses Admin-only pages | Missing permissions block route access via middleware | None |
| Principal impersonates another role | Role check is server-side (Spatie); no client-side trust | None |
| Leave notification leaks across schools | School ID scoping in notification dispatch | None |
| Unauthorized approve/reject via API | Route + Policy double-gating — both must pass | None |
| Sidebar shows items user cannot access | Items gated by `@can` / `$user->can()` at render time | None |

## Conclusion

Phase 03 introduces **no new security vulnerabilities**. The Principal role is treated as a high-trust oversight role with school-wide data access, which is appropriate for the business domain. All access is gated at multiple layers (route middleware, policy, and UI rendering). Tenant isolation is preserved via the existing `SchoolContext` infrastructure.
