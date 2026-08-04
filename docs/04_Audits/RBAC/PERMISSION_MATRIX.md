# PERMISSION MATRIX

**Phase:** 2B — Authentication & Authorization Stabilization  
**Date:** 2026-07-06  

---

## Role Assignments (from seeders)

| User | Email | Role | school_id |
|------|-------|------|-----------|
| Super Admin | superadmin@example.com | Super Admin | 1 (via is_super_admin=1) |
| School Admin | admin@example.com | School Admin | 1 |
| Aisha Khan | aisha.khan@example.com | Teacher | 1 |
| Rahul Sharma | rahul.sharma@example.com | Teacher | 1 |
| John Doe | john.doe@example.com | Parent | 1 |
| Jane Smith | jane.smith@example.com | Parent | 1 |
| Student users (8) | ...@example.com | Student | 1 |

---

## Permission Matrix

| Role | dashboard.view | Has Permission | Required | Status |
|------|---------------|----------------|----------|--------|
| Super Admin | ✅ Gate::before bypass | YES | YES | ✅ |
| School Admin | ✅ | YES | YES | ✅ |
| Principal | ✅ | YES | YES | ✅ |
| Teacher | ✅ | YES | YES | ✅ |
| Staff | ✅ | YES | YES | ✅ |
| Parent | ✅ | YES | YES | ✅ |

**All roles have `dashboard.view`** — confirmed via `debug:auth` Step 7.

---

## Feature Permissions Used in DashboardController

| Permission | Super Admin | School Admin | Principal | Teacher | Staff | Parent |
|-----------|-------------|--------------|-----------|---------|-------|--------|
| dashboard.view | ✅ bypass | ✅ | ✅ | ✅ | ✅ | ✅ |
| fees.view | ✅ bypass | ✅ | ✅ | ❌ | ❌ | ❌ |
| timetable.view | ✅ bypass | ✅ | ✅ | ✅ | ❌ | ❌ |
| academic_calendar.view | ✅ bypass | ✅ | ✅ | ✅ | ✅ | ✅ |
| student_documents.view | ✅ bypass | ✅ | ✅ | ❌ | ❌ | ❌ |
| attendance.view | ✅ bypass | ✅ | ✅ | ✅ | ❌ | ❌ |
| transport.view | ✅ bypass | ✅ | ✅ | ❌ | ❌ | ❌ |

✅ = Assigned via Role in PermissionSeeder  
❌ = Not assigned (feature not applicable to role)

---

## Role Permission Details

### Role: Super Admin
- **ID:** 1
- **Access:** All permissions via Gate::before bypass (`$user->isSuperAdmin() ? true : null`)
- **Note:** No direct permission assignments needed. The Gate::before short-circuits all `can()` checks.

### Role: School Admin
- **ID:** 2
- **Permissions:** All feature permissions (dashboard.view, fees.view, timetable.view, academic_calendar.view, student_documents.view, attendance.view, transport.view)
- **Inherited:** All permissions from role assignment

### Role: Principal
- **ID:** 3
- **Permissions:** All feature permissions (same as School Admin)

### Role: Teacher
- **ID:** 4
- **Permissions:** dashboard.view, timetable.view, academic_calendar.view, attendance.view
- **Missing (correctly):** fees.view, student_documents.view, transport.view

### Role: Staff
- **ID:** 12
- **Permissions:** dashboard.view, academic_calendar.view
- **Missing (correctly):** fees.view, timetable.view, student_documents.view, attendance.view, transport.view

### Role: Parent
- **ID:** 5 (school_id=1)
- **Permissions:** dashboard.view (for parent portal)
- **Special:** Redirected to `admin.parent-portal.dashboard` instead of `admin.dashboard`

---

## Team / School Context

| Setting | Value |
|---------|-------|
| Teams enabled | `true` |
| Team foreign key | `school_id` |
| Team model | `App\Models\School` |
| Team resolver | `DefaultTeamResolver` |
| role_has_permissions.school_id | `NULL` (all rows — Spatie expected behavior) |
| model_has_roles.school_id | `1` (all rows — correctly scoped) |

---

## Permission Cache

| Setting | Value |
|---------|-------|
| Cache driver | `database` (from config) |
| Cache key | `spatie.permission.cache` |
| Expiration | 24 hours |
| Cache store | `default` (database) |

**Cache flushed:** `php artisan permission:cache-reset` — no stale entries.

---

## Summary

| Check | Result |
|-------|--------|
| All roles have dashboard.view | ✅ |
| Role assignments correct | ✅ |
| Team scoping correct | ✅ |
| No duplicate permissions | ✅ |
| No missing permissions | ✅ |
| Super Admin bypass works | ✅ |
| Permission cache flushed | ✅ |
