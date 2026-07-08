# Route Report - Phase 02: Teacher Experience Refactor

## Routes Added

### 1. Teacher Self-Service Documents

**File**: `routes/modules/documents.php` (lines 20-24)

| Method | URI | Name | Controller Method | Middleware |
|--------|-----|------|-------------------|------------|
| GET | `/teacher-documents` | `admin.teacher-documents.index` | `TeacherDocumentController@index` | None (web) |
| GET | `/teacher-documents/data` | `admin.teacher-documents.data` | `TeacherDocumentController@data` | None (web) |
| GET | `/teacher-documents/{document}/download` | `admin.teacher-documents.download` | `TeacherDocumentController@download` | None (web) |

**Purpose**: Allows teachers to view and download their own employment documents (appointment letters, certificates, etc.).

---

### 2. Teacher Self-Service Leave

**File**: `routes/modules/leave.php` (lines 38-43)

| Method | URI | Name | Controller Method | Middleware |
|--------|-----|------|-------------------|------------|
| GET | `/my-leaves` | `admin.my-leaves.index` | `LeaveRequestController@myLeaves` | None (web) |
| GET | `/my-leaves/data` | `admin.my-leaves.data` | `LeaveRequestController@myLeavesData` | None (web) |

**Purpose**: Allows teachers to view their own leave requests in a simplified view, filtered automatically to their user ID.

---

### 3. Teacher Self-Service Payslips

**File**: `routes/modules/payroll.php` (lines 7-8)

| Method | URI | Name | Controller Method | Middleware |
|--------|-----|------|-------------------|------------|
| GET | `/payroll/payslips/my` | `admin.payroll.payslips.my` | `PayrollController@myPayslips` | None (web) |
| GET | `/payroll/my-payslips/data` | `admin.payroll.my-payslips.data` | `PayrollController@myPayslipsData` | None (web) |

**Purpose**: Allows teachers to view their own payslips without needing the `payroll.view` permission (which grants access to all employee payslips).

---

## Routes Modified for Teacher Scoping

### 1. Leave Requests (Admin View)

**File**: `routes/modules/leave.php` (lines 22-35)

| Method | URI | Name | Controller Method | Middleware |
|--------|-----|------|-------------------|------------|
| GET | `/leave-requests` | `admin.leave-requests.index` | `LeaveRequestController@index` | `permission:leave_management.view` |
| GET | `/leave-requests/data` | `admin.leave-requests.data` | `LeaveRequestController@data` | `permission:leave_management.view` |

**Modification**: The `data()` method now includes teacher-scoping logic:
```php
if (auth()->user()->hasRole('Teacher')) {
    $query->where('leave_requests.user_id', auth()->id());
}
```
This ensures that even if a teacher navigates to the admin leave-requests route, they only see their own records.

---

## Middleware Changes

### No new middleware created
Phase 02 did not introduce any new middleware classes. Existing middleware leveraged:

| Middleware | Purpose | Used By |
|-----------|---------|---------|
| `auth` | Authentication | All routes |
| `school` | School context resolution | All routes |
| `permission:xxx` | Permission-based access control | Admin routes (e.g., `attendance.view`, `homework.view`) |
| `role:Teacher` | Role-based checks (via `hasRole()`) | Controller and policy methods |

---

## Permission Gates

### Teacher Self-Service Routes (No Permission Gates)
The following routes intentionally have **no** `permission:` middleware because they auto-scope to the authenticated user:

| Route | Rationale |
|-------|-----------|
| `admin.teacher-documents.*` | Teachers access their own documents; ownership verified in controller |
| `admin.my-leaves.*` | Teachers access their own leaves; auto-filtered by `user_id` |
| `admin.payroll.payslips.my` | Teachers access their own payslips; auto-filtered by `employee_id` |

### Admin Routes (With Permission Gates)
| Route | Permission Gate |
|-------|----------------|
| `admin.leave-requests.*` | `permission:leave_management.view` (with teacher auto-scoping in controller) |
| `admin.payroll.*` | `permission:payroll.view` (teacher self-service routes excluded) |

---

## Sidebar Route Navigation

### Teacher Sidebar Items (sidebar.blade.php lines 14-119)

| Label | Route | Permission Gate |
|-------|-------|-----------------|
| Dashboard | `admin.dashboard` | `@can('dashboard.view')` |
| My Timetable | `admin.timetable.index` | `@can('timetable.view')` |
| Attendance | `admin.attendance.index` | `@can('attendance.view')` |
| Homework | `admin.homework.index` | `@can('homework.view')` |
| My Students | `admin.students.index` | `@can('students.view')` |
| Marks | `admin.exams.index` | `@can('exams.view')` |
| Leave | `admin.leave-requests.index` | None (always visible) |
| My Documents | `admin.teacher-documents.index` | None (always visible) |
| My Payslips | `admin.payroll.payslips.my` | None (always visible) |
| Notifications | `admin.notifications.index` | `@can('notifications.view')` |
| Calendar | `admin.calendar.index` | `@can('academic_calendar.view')` |
| Ask ERP | `#askErpModal` (modal trigger) | None (always visible) |

### Programmatic Sidebar Builder (SidebarBuilder.php lines 60-81)
The `buildForTeacher()` method mirrors the blade template and produces the same navigation structure for the programmatic sidebar rendering approach.

---

## Route Summary

| Category | Routes Added | Routes Modified | Middleware Changes | Permission Gates |
|----------|-------------|-----------------|-------------------|-----------------|
| Documents | 3 | 0 | 0 | 0 |
| Leave | 2 | 1 (scoping) | 0 | 0 |
| Payroll | 2 | 0 | 0 | 0 |
| **Total** | **7** | **1** | **0** | **0** |
