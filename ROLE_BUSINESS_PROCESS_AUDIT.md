# Role-Based Business Process Audit — School ERP

**Date:** 2026-07-07
**Scope:** Full authorization landscape: role definitions, route middleware, controller gates, view directives, policies, and dashboard-builder mapping.

---

## 1. Role Inventory

Defined in `database/seeders/PermissionSeeder.php:82-118` (12 roles).

| Role | Dashboard Builder | System-Protected | Permission Scope |
|------|-------------------|------------------|-----------------|
| Super Admin | AdminDashboardBuilder | Yes (delete blocked) | ALL permissions |
| School Admin | AdminDashboardBuilder | Yes (delete blocked) | ALL permissions |
| Principal | PrincipalDashboardBuilder | No | Management oversight |
| Teacher | TeacherDashboardBuilder | No | Class-level operations |
| Student | — (no builder mapped) | No | Self-view only |
| Parent | ParentDashboardBuilder | No | Child-related view |
| Accountant | — (no builder mapped) | No | Fees & finance |
| Librarian | — (no builder mapped) | No | Library only |
| Payroll Manager | — (no builder mapped) | No | Payroll only |
| Receptionist | — (no builder mapped) | No | Front-desk basic |
| HR | — (no builder mapped) | No | Teacher management |
| Staff | StaffDashboardBuilder | No | Dashboard-only |

### Role–Builder Mapping Gaps

`DashboardFactory.php:16-23` maps only 6 of 12 roles:

```
Super Admin  → AdminDashboardBuilder   ✓
School Admin → AdminDashboardBuilder   ✓
Principal    → PrincipalDashboardBuilder ✓
Teacher      → TeacherDashboardBuilder ✓
Staff        → StaffDashboardBuilder   ✓
Parent       → ParentDashboardBuilder  ✓
```

**Unmapped roles** (will receive `abort(403)` on login if they are the user's highest-priority role):
- Student
- Accountant
- Librarian
- Payroll Manager
- Receptionist
- HR

These roles have `dashboard.view` permission but no dashboard builder to render it. See §7.

---

## 2. Route Protection Architecture

### 2.1 Web Routes (`routes/web.php:14`)

All admin routes protected by:
```php
Route::middleware(['auth', 'school'])
    ->prefix('admin')
    ->as('admin.')
    ->group(...)
```

Per-module permission middleware applied at group level:

| Module | Permission Gate | Routes |
|--------|----------------|--------|
| Dashboard | *(none)* | 1 |
| RBAC Roles | `permission:roles.view` | 6 |
| RBAC Permissions | `permission:permissions.view` | 6 |
| Academics | `permission:academics.view` | 38 |
| Students | `permission:students.view` | 7 |
| Parents | `permission:parents.view` | 6 |
| Timetable | `permission:timetable.view` | 13 |
| Attendance | *(sub-groups)* | 15 |
| Teachers | `permission:teachers.view` | 18 |
| Exams | `permission:exams.view` | 14 |
| Homework | `permission:homework.view` | 7 |
| Leave | `permission:leave_management.view` | 12 |
| Fees | `permission:fees.view` | 30 |
| Notifications | `permission:notifications.view` (bell routes unguarded) | 10 |
| Settings | `permission:settings.view` | 2 |
| Users | `permission:users.view` | 10 |
| Calendar | `permission:academic_calendar.view` | 8 |
| Documents | `permission:student_documents.view` | 8 |
| Transport | `permission:transport.view` | 30+ |
| Library | `permission:library.view` | 40 |
| Payroll | `permission:payroll.view` | 30+ |
| AI Agents | **NO permission middleware** | 6 |
| Reports | `permission:reports.view` (+ sub-group checks) | 60+ |

### 2.2 Unguarded Routes

| Route | File | Risk |
|-------|------|------|
| `GET /admin/agents/*` | `routes/modules/ai_agents.php` | All 6 AI Agent routes have **zero** permission middleware. Any authenticated user (all roles) can access. |
| `GET /ai/dashboard`, `POST /ai/ask` | `routes/modules/ai_assistant.php` | Outside admin group entirely. Only `auth` middleware — no permission check. |
| `GET /admin/notifications/bell` | `routes/modules/notifications.php:7` | Intentionally public (bell badge API), but worth documenting. |

### 2.3 Parent Portal Routes

`routes/modules/parents.php:19-29` defines a separate group:
```php
Route::middleware(['auth', 'role:Parent'])
    ->prefix('parent-portal')
    ->as('parent-portal.')
    ->group(...)
```
Uses `role:Parent` middleware — only `Parent` role can access. However, the `Parent` role **also** has access to the main `admin.*` routes (since they pass `auth` + `school` middleware). This means a parent user can access both `/parent-portal/*` and `/admin/*` routes for any module where they have a permission.

---

## 3. Authorization Enforcement Patterns

Three layers, checked in this order:

```
Request → FormRequest::authorize()    (60+ checks, ~27 request classes)
        → Controller::__construct     ($this->middleware('can:...') — 7 report controllers)
        → Controller::method          ($this->authorize() — 91 calls)
        → Policy methods              (32 policy classes, 6+ custom abilities)
```

Backed by:
- **`Gate::before()`** (`AppServiceProvider.php:190-192`): Super Admin bypasses ALL gates.
- **`Gate::policy()`** (38 registrations in `AppServiceProvider.php:194-231`).
- **`@can`/`@canany`** (135+ Blade directives): UI-level show/hide only — **never** a security boundary.

### 3.1 Pattern 1 — Route `permission:` Middleware (primary gate)

Used on every module group. Example from `routes/modules/academics.php:8`:
```php
Route::middleware('permission:academics.view')
```

This is the **first gate** every request hits (after `auth` + `school`). If the user lacks the `.view` permission, they receive an immediate 403 before any controller code runs.

### 3.2 Pattern 2 — FormRequest `authorize()` (secondary gate)

Applied to all mutating requests (create/update). Example:
```php
// app/Modules/Academics/Requests/SaveAcademicYearRequest.php:13
public function authorize(): bool
{
    return $this->user()->can(
        $this->route('academicYear') ? 'academics.update' : 'academics.create'
    );
}
```

**Observations:**
- All FormRequests with `authorize()` return either `true` or `$this->user()->can(...)`.
- **None return `false`** — no request is explicitly denied.
- **Notification FormRequests have no `authorize()` method** — defaults to `true` (all authenticated users allowed to submit create/update requests). The only barrier is `$this->authorize('send', $notification)` in `NotificationController::send()`.

### 3.3 Pattern 3 — Controller `$this->authorize()` (tertiary gate)

91 uses across all module controllers. All map to policies. Examples:
```php
$this->authorize('create', Attendance::class);
$this->authorize('update', $attendance);
$this->authorize('delete', $exam);
```

### 3.4 Pattern 4 — Controller Middleware `$this->middleware('can:...')`

Used only in 7 report controllers:
```php
// AbsentStudentReportController.php:21
$this->middleware('can:attendance.view');
```

### 3.5 Pattern 5 — Hardcoded `hasRole()` in API Controllers

11 checks in `app/Http/Controllers/Api/V1/`:
```php
if (! $user->hasRole('School Admin') && ! $user->hasRole('Accountant')) {
    return $this->forbidden('...');
}
```

**These bypass the permission system entirely.** A user could have `fees.view` permission but still be blocked if they lack the `Accountant` or `School Admin` role.

---

## 4. Dashboard–Role Consistency

### 4.1 Module Access by Role (defined in PermissionSeeder)

| Module | Principal | Teacher | Student | Parent | Accountant | Librarian | Payroll Mgr | Receptionist | HR | Staff |
|--------|-----------|---------|---------|--------|------------|-----------|-------------|--------------|----|-------|
| Dashboard | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Attendance | ✓ | ✓ | ✓ | ✓ | — | — | — | — | — | — |
| Timetable | ✓ | ✓ | — | ✓ | — | — | — | — | — | — |
| Ac. Calendar | ✓ | ✓ | — | ✓ | — | — | — | — | — | — |
| St. Documents | ✓ | ✓ | — | ✓ | — | — | — | — | — | — |
| Transport | ✓ | — | — | — | ✓ | — | — | — | — | — |
| Students | ✓ | ✓ | — | ✓ | — | — | — | ✓ | — | — |
| Parents | ✓ | — | — | ✓ | — | — | — | ✓ | — | — |
| Teachers | ✓ | — | — | — | — | — | — | — | ✓ | — |
| Exams | ✓ | ✓ | ✓ | ✓ | — | — | — | — | — | — |
| Homework | ✓ | ✓ | — | ✓ | — | — | — | — | — | — |
| Academics | ✓ | ✓ | — | — | — | — | — | — | — | — |
| Library | ✓ | — | — | — | — | ✓ | — | — | — | — |
| Fees | ✓ | — | ✓ | ✓ | ✓ | — | — | — | — | — |
| Payroll | ✓ | — | — | — | — | — | ✓ | — | — | — |
| Notifications | ✓ | — | — | ✓ | — | — | — | — | — | — |
| Roles | — | — | — | — | — | — | — | — | — | — |
| Permissions | — | — | — | — | — | — | — | — | — | — |
| Leave Mgmt | ✓ | — | — | ✓ | — | — | — | — | — | — |
| Users | — | — | — | — | — | — | — | — | — | — |
| Settings | — | — | — | — | — | — | — | — | — | — |
| Reports | ✓ | — | — | — | ✓ | ✓ | ✓ | — | ✓ | — |

### 4.2 Anomalies

1. **Student has `dashboard.view` and `fees.view` but no dashboard builder** (§1) — would 403 on login redirect.
2. **Accountant has `fees.*` and `reports.view` but no dashboard builder** — same issue.
3. **Parent has `leave_management.create` but no other leave mgmt permissions** — can create but never approve/update.
4. **Parent has `parents.view`** — self-referential (can view parent listing, which likely shows all parents).
5. **Teacher lacks `teachers.view`** — cannot see teacher list (only has `teachers.reports`).
6. **Staff** has `dashboard.view` only — literally just the dashboard with no modules accessible.

---

## 5. Policy Coverage Analysis

| Module | Policies | Custom Abilities | Tenant-Scoped |
|--------|----------|-----------------|---------------|
| Students | StudentPolicy | — | No |
| Parents | ParentPolicy | — | Yes (`current_school_id`) |
| Teachers | TeacherPolicy | — | No |
| Attendance | AttendancePolicy | — | No |
| Academics | 5 policies | — | No |
| Timetable | TimetableSlotPolicy | `print()` | No |
| Exams | ExamPolicy | `publish()` | No |
| Homework | HomeworkPolicy | — | No |
| Calendar | CalendarPolicy | `publish()` | Yes (`current_school_id`) |
| Documents | DocumentPolicy | `verify()`, `download()` | Yes (`current_school_id`) |
| Fees | 4 policies | — | No |
| RBAC | 2 policies | — | No |
| Transport | 5 policies | — | No |
| Library | 2 policies | — | No |
| Payroll | 2 policies | `process()`, `lock()`, `export()`, `payslipView()`, `payslipGenerate()`, `payslipExport()` | No |
| Leave | 2 policies | `approve()` | No |
| Notifications | NotificationPolicy | `send()` | No |

### Missing Policy Coverage

- **Users** module has NO policy. Authorization relies entirely on route middleware (`permission:users.view`) and FormRequest `authorize()` methods. No `$this->authorize()` calls in the controller.
- **Settings** module has NO policy. Authorization at route group only (`permission:settings.view`).
- **Reports** modules use constructor middleware (`$this->middleware('can:...')`) instead of policies.

---

## 6. Issues & Gaps

### 6.1 Critical

| # | Issue | File(s) | Impact |
|---|-------|---------|--------|
| C1 | **6 roles have no dashboard builder** (Student, Accountant, Librarian, Payroll Manager, Receptionist, HR). After login redirect, `DashboardFactory::make()` will `abort(403)` because it iterates `ROLE_PRIORITY` and won't find a match. | `DashboardFactory.php:16-23` | Login redirect fails for these roles. |
| C2 | **AI Agent routes have zero permission middleware**. All 6 routes (`/admin/agents/*`) are inside the `admin.*` group but have no `permission:` middleware. Any authenticated user (any role) can access. | `routes/modules/ai_agents.php` | Unauthorized access to AI agent execution. |
| C3 | **AI Assistant routes are outside admin group** with no permission check. `/ai/dashboard` and `/ai/ask` have only `auth` middleware. | `routes/modules/ai_assistant.php` | All authenticated users can access AI features. |

### 6.2 High

| # | Issue | File(s) | Impact |
|---|-------|---------|--------|
| H1 | **`isSuperAdmin()` side-effect loads stale roles**. Inside `SetSchoolContext::resolveFromUser()`, `$user->isSuperAdmin()` calls `hasRole('Super Admin')` which lazy-loads `$user->roles` with `team_id=null` (team not yet set). This caches an empty collection on the model. Subsequent `hasRole()` calls use `loadMissing('roles')` and find the stale empty collection, returning false. | `SetSchoolContext.php:85-86`, `User.php:82` | All role/permission checks that happen after middleware could return false if roles were loaded too early. *(Partially fixed — `unsetRelation('roles')` added, but `isSuperAdmin()` still has the side-effect.)* |
| H2 | **Hardcoded `hasRole()` checks in API controllers** bypass the permission system. `ExamApiController`, `FeeApiController`, `TeacherApiController`, `StudentApiController`, and `TeacherAppController` check role names directly instead of permissions. If a role is renamed or a new role is created with the same permissions, these checks will block valid access. | Several files in `app/Http/Controllers/Api/V1/` | Brittle authorization; prevents Role-based access flexibility. |
| H3 | **Lowercase role names in AI ContextBuilder** (`hasRole('admin')`, `hasRole('teacher')`, etc.) don't match seeded Title Case roles (`'Teacher'`, `'Parent'`, `'Student'`). These checks always return false. | `AiAssistant/Services/ContextBuilder.php:133-139` | AI context detection broken for all roles. |

### 6.3 Medium

| # | Issue | File(s) | Impact |
|---|-------|---------|--------|
| M1 | **`teachers.attendance.mark`, `teachers.attendance.view`, `teachers.attendance.update`, `teachers.leave.create`, `teachers.leave.update`** permissions are checked in FormRequests but **not defined in PermissionSeeder`. These permission strings don't exist in the database — `can()` checks will always return false. | Teacher FormRequest classes | Teacher attendance/leave management authorization is effectively broken (always denied). |
| M2 | **`students.export` permission** is defined in seeder but never checked anywhere in the codebase (no `@can`, no `can()`, no policy, no middleware). Dead permission. | `PermissionSeeder.php` (defined), nowhere (used) | None (unused). |
| M3 | **`payroll.payslip.view` and `payroll.payslip.export`** are defined in seeder and checked in `PayrollPolicy` but never used in Blade templates or FormRequests. | `PayrollPolicy.php` | Payslip view/export gates exist but are dead code in the UI. |
| M4 | **Pending Homework stat card counts ALL homework regardless of teacher.** `TeacherDashboardBuilder::buildStatCards()` (line 32) counts `Homework` where `due_date >= now()` but scoped by `created_by` only if the teacher record exists. A teacher with no `Teacher` model record sees ALL unexpired homework. | `TeacherDashboardBuilder.php:31-33` | Potential data leak (teacher sees other teachers' homework counts). |

### 6.4 Low

| # | Issue | File(s) | Impact |
|---|-------|---------|--------|
| L1 | **No `AuthServiceProvider.php`** — all policy registrations in `AppServiceProvider.php`. Works but deviates from Laravel convention. | — | Convention only. |
| L2 | **`reports.export` permission** defined in seeder but never checked in any route, blade, or controller. Reports export/print routes check the same `.view` permission, not `.export`. | `PermissionSeeder.php` | Dead permission. |
| L3 | **Notification FormRequests have no `authorize()`** — default to `true`. Any authenticated user can submit notification create/update requests. Only the controller's `$this->authorize('send', ...)` provides a gate. | `Notifications/Requests/` | Weak create/update gate on notifications. |
| L4 | **Users module has no policy** — relies on route middleware + FormRequest only. No `$this->authorize()` in controller. | `Users/Controllers/` | Thin authorization layer (still protected by middleware). |
| L5 | **Settings module has no policy** — route middleware only. | `Settings/` | Thin authorization (still protected by middleware). |
| L6 | **Parent portal route group** uses `role:Parent` middleware, but the `Parent` role also has access to main `admin.*` routes via permissions. Gate is redundant for parents who also have admin-route access. | `routes/modules/parents.php:19` | Redundant gate; parent users have two parallel access paths. |

---

## 7. Dashboard Builder Access Matrix (Simplified)

For each role, the login redirect after `POST /login`:

| Role | `hasRole()` Match | Builder Selected | Dashboard Renders |
|------|-------------------|------------------|-------------------|
| Super Admin | ✓ (priority 1) | AdminDashboardBuilder | ✓ |
| School Admin | ✓ (priority 2) | AdminDashboardBuilder | ✓ |
| Principal | ✓ (priority 3) | PrincipalDashboardBuilder | ✓ |
| Teacher | ✓ (priority 4) | TeacherDashboardBuilder | ✓ |
| Staff | ✓ (priority 5) | StaffDashboardBuilder | ✓ |
| Parent | ✓ (priority 6) | ParentDashboardBuilder | ✓ |
| **Student** | **✗** | **—** | **403 after login** |
| **Accountant** | **✗** | **—** | **403 after login** |
| **Librarian** | **✗** | **—** | **403 after login** |
| **Payroll Mgr** | **✗** | **—** | **403 after login** |
| **Receptionist** | **✗** | **—** | **403 after login** |
| **HR** | **✗** | **—** | **403 after login** |

Note: If a user has MULTIPLE roles, the first matching role in `ROLE_PRIORITY` order is used. E.g., a user with both `Teacher` and `Parent` roles would get `TeacherDashboardBuilder`.

---

## 8. Recommendations

1. **Add dashboard builders** for unmapped roles (Student, Accountant, Librarian, Payroll Manager, Receptionist, HR) or redirect them to a non-dashboard landing page.
2. **Add `permission:` middleware** to AI Agent routes.
3. **Add `permission:` middleware** to AI Assistant routes or move them inside the admin group.
4. **Define missing permission strings** (`teachers.attendance.*`, `teachers.leave.*`) in `PermissionSeeder`.
5. **Replace hardcoded `hasRole()` checks** in API controllers with permission-based checks.
6. **Fix lowercase role names** in `ContextBuilder.php` to match seeded Title Case.
7. **Refactor `isSuperAdmin()`** to avoid calling `hasRole()` as a side-effect, or document the caching implications.
8. **Add policies** for Users and Settings modules.
9. **Add `authorize()` methods** to Notification FormRequests.
10. **Remove dead permissions** (`students.export`, `reports.export`) or add usage.
11. **Consider scoping** the `Homework` count query in `TeacherDashboardBuilder` to the current user only.
