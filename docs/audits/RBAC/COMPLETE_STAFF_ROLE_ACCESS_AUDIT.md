# COMPLETE STAFF ROLE ACCESS AUDIT & IMPLEMENTATION

Date: 2026-08-13
Scope: School ERP Web Application — internal staff / administration only.

---

## 1. PRODUCT DECISION (CONFIRMED)

The School ERP Web Application is an **internal staff / administration**
application.

- Every internal school staff role MUST be able to log in to the ERP.
- Parents and Students MUST NOT access the administrative School ERP Web
  Application. They use the separate mobile app / portal experiences.
- Backend enforcement is mandatory — hiding a login button or sidebar link is
  not sufficient. Every entry point (login, direct URL, bookmarked dashboard,
  manually entered admin route, admin API, manipulated role/school parameters)
  must be blocked server-side.

---

## 2. CURRENT ROLES (from the live database)

Guard: `web`. All roles are school-scoped via Spatie teams (`school_id`).

| Role | Role ID | Users | Permissions | Classification | ERP Login |
|---|---|---|---|---|---|
| Super Admin | 1 | 1 | 121 (all) | Internal | ✅ |
| School Admin | 2 | 1 | 121 (all) | Internal | ✅ |
| Principal | 3 | 0 | 58 | Internal | ✅ |
| Teacher | 4 | 3 | 19 | Internal | ✅ |
| Student | 5 | 3 | 5 | **External** | ❌ |
| Parent | 6 | 2 | 12 | **External** | ❌ |
| Driver | 7 | 4 | 5 | Internal (transport staff) | ✅ |
| Accountant | 8 | 0 | 8 | Internal | ✅ |
| Librarian | 9 | 0 | 7 | Internal | ✅ |
| Payroll Manager | 10 | 0 | 12 | Internal | ✅ |
| Receptionist | 11 | 0 | 9 | Internal | ✅ |
| HR | 12 | 0 | 11 | Internal | ✅ |
| Staff | 13 | 0 | 1 | Internal | ✅ |

**13 roles total, no duplicates.** No `Transport Manager` role exists — the
`Driver` role is the existing transport staff role and is used as-is (no
duplicate created, per the do-not-duplicate rule).

---

## 3. CLASSIFICATION

### INTERNAL ERP USERS (can log into School ERP)

`Super Admin`, `School Admin`, `Principal`, `Teacher`, `Accountant`,
`Librarian`, `Payroll Manager`, `Receptionist`, `HR`, `Staff`, `Driver`.

### EXTERNAL USERS (MUST NOT log into administrative ERP)

`Parent`, `Student`. No other external roles exist in the application.

---

## 4. PARENT / STUDENT RESTRICTION — IMPLEMENTATION

### 4.1 Login flow (web)

`app/Modules/Auth/Controllers/LoginController.php:48`

After authentication, school context is applied and then:

```php
foreach (config('access.external_roles', ['Parent', 'Student']) as $externalRole) {
    if ($user->hasRole($externalRole)) {
        // logout + invalidate session + regenerate token
        return redirect()->route('login')->withErrors([...]);
    }
}
```

A Parent/Student who supplies valid credentials is **logged out and returned
to the login page** with a staff-only message. The session is fully
invalidated. The account is never authenticated into the ERP session.

### 4.2 Middleware — every /admin route

`app/Http/Middleware/EnsureStaffUser.php` (registered as `staff`):

- Registered in `bootstrap/app.php` as the `staff` middleware alias.
- `routes/web.php:14` wraps the entire `admin.*` route group in
  `['auth', 'school', 'staff']`.
- `EnsureStaffUser` allows Super Admin, then blocks any user holding an
  `external_roles` role (`Parent`/`Student`) with `403`. Any role NOT listed
  as external is treated as internal staff and allowed through (the 
  permission middleware further gates module access).

This means a Parent/Student cannot reach `/admin/dashboard` or any other
`/admin/*` route by:

- a bookmarked dashboard URL,
- a manually entered admin route,
- a forged request,
- modifying `school_id` in the URL/header/session (school resolution is
  validated against the user's own school memberships — see §8).

### 4.3 Reports routes (defense-in-depth)

`app/Modules/Reports/routes.php:6`

The `/reports/*` module was registered **outside** the `/admin` group (loaded
from `routes/web.php` before the group) with its own middleware. It was
missing the `staff` gate. Fixed:

```php
Route::middleware(['auth:sanctum', 'verified', 'school', 'staff', 'permission:reports.view'])
```

`EnsureStaffUser` was also hardened to resolve the user from the `sanctum`
guard first (`$request->user('sanctum') ?? $request->user()`), since the
reports module authenticates via `auth:sanctum` for API-token users.

### 4.4 Frontend

- Parent/Student branches were removed from `SidebarBuilder` and
  `resources/views/layouts/partials/sidebar.blade.php`.
- Parent/Student dashboard builders (`ParentDashboardBuilder`,
  `StudentDashboardBuilder`) were removed from `DashboardFactory`.
- The separate `layouts/parent.blade.php` layout and all parent web portal
  views/routes were removed. Parents use the mobile API (`/api/v1/parents/*`).

### 4.5 API (mobile app) remains available

The mobile APIs (`/api/v1/auth/login`, `/api/v1/parents/*`,
`/api/v1/student/*`, `/api/v1/teacher/*`, `/api/v1/driver/*`) remain
accessible to their respective external roles. This is correct — the mobile
apps are the parent/student experience. The web ERP `/admin/*` and `/reports/*`
are blocked.

---

## 5. STAFF LOGIN AUDIT

All internal staff roles were tested with real login flows (web login +
dashboard GET). Verified for every role: authentication succeeds, role
resolves, school context resolves, dashboard builder resolves, and the
dashboard renders 200.

Test: `tests/Feature/StaffRoleAccessTest.php`
`test_every_internal_staff_role_can_login_and_reach_dashboard`.

### Gaps found & fixed

1. **`Payroll Manager` and `Driver` had no dashboard builder.** A
   Payroll Manager / Driver who logged in would hit
   `DashboardFactory::make()` → `abort(403, 'Your role does not have access
   to any dashboard.')`. This was a real broken-login bug for approved roles.
   - Added `PayrollManagerDashboardBuilder` and `DriverDashboardBuilder`.
   - Registered both in `DashboardFactory::ROLE_PRIORITY`.
   - Added sidebar branches for both roles in `sidebar.blade.php`.

2. **HR dashboard crashed.** `HRDashboardBuilder` ran
   `selectRaw('department, COUNT(*) ...')` but the `employees` table has no
   `department` column (it uses `department_id` → `payroll_departments`).
   Any HR user logging in hit a 500 on the dashboard. Fixed with a
   `leftJoin('payroll_departments', ...)` and `department_name` grouping.

### Verified access boundaries (permissions from PermissionSeeder)

| Role | Can access | Cannot access |
|---|---|---|
| Teacher | dashboard, students, academics, attendance, exams, timetable, calendar, homework, documents, own payslips, own leave | payroll mgmt, fees, access control, settings, users |
| Accountant | dashboard, fees, transport, financial reports | payroll, exams mgmt, access control, settings |
| Librarian | dashboard, library, reports | fees, payroll, access control, settings |
| Receptionist | dashboard, students, parents, admissions | payroll, fees, access control, settings |
| HR | dashboard, employees, HR docs | fees, payroll, access control, settings |
| Driver | dashboard, transport | students, payroll, fees, access control |
| Principal | dashboard, students, teachers, attendance, exams, homework, calendar, fees (view), reports, leave approve | settings, users, access control |
| School Admin | full school administration | cross-school / system-level |
| Super Admin | everything | — |

All negative cases are enforced by the existing `permission:*` route
middleware (Spatie) — a direct URL request returns 403. Verified in
`StaffRoleAccessTest` (§7).

---

## 6. BACKEND ROUTE PROTECTION (real security)

### 6.1 Every `/admin/*` route

Protected by `auth` + `school` + `staff` group middleware, plus module-level
`permission:module.action` middleware. A route-level audit (`route:list --json`)
confirmed **all admin modules carry permission middleware**:

Modules with permission middleware: academics, admissions, attendance,
calendar, documents, exams, exam-schedules, fees, grade-scales, homework, hr,
leave-requests, leave-types, library, lifecycle, mobile-apps, notifications,
parents, payroll, permissions, roles, settings, students, teachers, timetable,
transport, users.

### 6.2 Intentionally permission-light self-service routes

The following routes are deliberately accessible to any authenticated staff
user because they only surface **the authenticated user's own data** and are
controller-scoped by `auth()->id()`:

- `admin/notifications/bell`, `mark-all-read`, `{notification}/mark-read`
  (NotificationService keys everything on the current user id).
- `admin/my-leaves*` (scoped to `leave_requests.user_id = auth()->id()`).
- `admin/payroll/payslips/my*` (scoped to the authenticated teacher's payslips).
- `admin/teacher-documents*` (teacher's own documents).
- `admin/dashboard` (all staff have `dashboard.view`).

### 6.3 AI Agents routes — tightened

`routes/modules/ai_agents.php` previously left `GET /admin/agents`,
`/history`, `/history/data` and `/executions/{id}` **unprotected** — any
authenticated staff user could view agent execution history, while only
`preview`/`execute` required a role. Fixed by applying
`role:Super Admin|School Admin|Principal|HR` to the entire agents group
(matching the sidebar gating and the preview/execute role gate).

---

## 7. TESTS — StaffRoleAccessTest

`tests/Feature/StaffRoleAccessTest.php` (16 tests / 84 assertions):

- every internal staff role can login + reach dashboard
- parent cannot login to web ERP
- student cannot login to web ERP
- parent blocked from admin routes via middleware
- student blocked from admin routes via middleware
- parent blocked from reports routes
- accountant has no payroll / roles / settings access
- librarian has no fees / payroll / roles access
- teacher has no payroll / roles / settings access
- HR has no fees / payroll access
- receptionist has no payroll / fees access
- driver only has transport and dashboard
- principal has no settings / roles / users access
- school admin has full school administration
- super admin retains full access
- AI agents limited to senior roles

---

## 8. SCHOOL / TENANT ISOLATION

`app/Http/Middleware/SetSchoolContext.php` resolves the school ID from the
authenticated user only:

1. explicit `school_id` param/header — **only accepted if the user belongs to
   that school** or is Super Admin,
2. session `school_id` — only if the user belongs to that school,
3. user `current_school_id`,
4. guardian record (legacy),
5. active school pivot,
6. `model_has_roles.school_id`.

`BelongsToSchool` adds a global scope that filters every query by the resolved
`SchoolContext::id()`, so School A staff can never read School B rows even via
a forged URL/`school_id`. Existing AI security tests cover cross-school
isolation for exams/students (`AiSecurityTest`).

---

## 9. TEACHER-SPECIFIC SCOPING

Server-side, per existing business rules:

- **Students** (`StudentController`): a Teacher's listing and DataTable are
  filtered to `teacher->classSections` ids.
- **Attendance** (`AttendanceController`): class-section lists, data tables,
  store, and reports are filtered + `verifyTeacherClassAccess()` guard.
- **Homework** (`HomeworkController`): filtered to assigned class sections;
  create validates the target section is in the teacher's scope.
- **Exams / Marks** (`ExamController`, `ExamMarkController`): filtered to
  assigned class sections; marks entry and exam access check teacher scope.
- **Timetable**: teachers view the shared timetable (read `timetable.view`);
  editing is `timetable.create/update/delete` (not granted to Teacher).

Teacher scope is enforced server-side (controllers + AI `RoleDataScoper`),
never frontend-only.

---

## 10. AI ACCESS FOLLOWS ERP ROLE PERMISSIONS

Already hardened in the prior AI phase (see `AI_HARDENING_ACCEPTANCE_REPORT.md`):

- `config/ai.php` `role_permissions` maps every ERP role to permitted tool
  patterns; `RoleDataScoper` authorizes every intent before execution.
- Teacher scope filters apply to exam/attendance/homework/student/fee queries.
- Accountant blocked from payroll; Receptionist blocked from fees; Librarian
  blocked from students — verified by `AiSecurityTest`.
- `parent`/`student` roles cannot reach the web AI endpoint
  (`/admin/ai/ask` role middleware excludes them), so AI cannot become a
  privilege-escalation path for external roles.
- This audit added `Payroll Manager` to the valid-roles list in
  `AiRegistryConsistencyTest` (config already had it; the test's whitelist was
  stale). All AI suites pass.

---

## 11. USER CREATION / ROLE ASSIGNMENT

- Users are created via `admin/users` with `users.create` (Spatie permission),
  role assignment via `users.update` (`/admin/users/{user}/assign-role`).
- `AdminUserSeeder` creates Super Admin / School Admin with role assignment
  scoped to the school via `PermissionRegistrar::setPermissionsTeamId()`.
- `TransportService` creates Driver users with the `Driver` role at the correct
  school (`DriverLoginEnableTest` covers create/update/reset-password and
  school scoping).
- No duplicate roles exist; no new roles were created by this audit.

---

## 12. REMAINING NOTES / RECOMMENDATIONS

1. **Reports module middleware** now includes `staff`; the module also has
   `permission:reports.view` + per-report abilities (`attendance.view`,
   `fees.reports`, `exams.reports`, `teachers.reports`, `parents.reports`,
   `students.view`) so report-type access follows ERP permissions.
2. **Self-service route coverage** (my-leaves / my-payslips / teacher
   documents) is controller-scoped to the current user; if future routes are
   added, they must follow the same pattern.
3. **No cross-school dashboard test yet** — `SetSchoolContext` + global scope
   already prevent it; a dedicated test in `StaffRoleAccessTest` can be added
   when a second school's data fixture is needed.
4. **Sidebar PHP service (`SidebarBuilder`)** is registered as a singleton but
   the rendered sidebar is the Blade partial; the PHP class is kept for
   tooling/compatibility. Both were updated to remove Parent/Student branches.

---

## 13. FILES CHANGED / ADDED (this audit)

### Modified
| File | Change |
|---|---|
| `app/Http/Middleware/EnsureStaffUser.php` | Guard-aware user resolution (`sanctum` first) |
| `app/Modules/Reports/routes.php` | Added `staff` middleware |
| `routes/modules/ai_agents.php` | Role-gated entire agents group |
| `app/Modules/Dashboard/Services/DashboardFactory.php` | Registered Payroll Manager + Driver builders |
| `app/Modules/Dashboard/Services/Builders/HRDashboardBuilder.php` | Fixed `department` → `payroll_departments` join |
| `resources/views/layouts/partials/sidebar.blade.php` | Added Payroll Manager + Driver branches |
| `tests/Feature/AiRegistryConsistencyTest.php` | Added Payroll Manager / Driver to valid roles |

### Added
| File | Reason |
|---|---|
| `app/Modules/Dashboard/Services/Builders/PayrollManagerDashboardBuilder.php` | Payroll Manager dashboard |
| `app/Modules/Dashboard/Services/Builders/DriverDashboardBuilder.php` | Driver dashboard |
| `tests/Feature/StaffRoleAccessTest.php` | 16-test role access regression suite |
| `docs/audits/RBAC/COMPLETE_STAFF_ROLE_ACCESS_AUDIT.md` | This report |

---

## 14. VERDICT

- Parent/Student are excluded from the administrative ERP at **login, every
  `/admin/*` route, and `/reports/*`** — enforced server-side.
- Every approved internal staff role can authenticate, resolve school context,
  and reach a working dashboard.
- Module access follows the existing Spatie permission model; direct-URL
  access to restricted modules returns 403.
- Teacher scope and tenant isolation are enforced server-side.
- AI access mirrors ERP permissions; external roles have no AI entry point.
- Full application test suite passes with no regressions.
