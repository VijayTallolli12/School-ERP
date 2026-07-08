# Dashboard Pipeline Diagnostic

**User:** Aisha Khan (aisha.khan@example.com)
**User ID:** 3
**Date:** 2026-07-07 06:23:21

---

## 1. AFTER LOGIN

- **User ID:** 3
## 1. AFTER LOGIN

- **Email:** aisha.khan@example.com
## 1. AFTER LOGIN

- **Current School ID (DB column):** 1
## 1. AFTER LOGIN

- **Session school_id:** NULL
## 1. AFTER LOGIN

- **SchoolContext::id():** NULL
## 1. AFTER LOGIN

- **getPermissionsTeamId() before apply:** NULL
## 1. AFTER LOGIN

- **Roles (before context):** Teacher
## 1. AFTER LOGIN

- **Role Names (before context):** Teacher
## 1. AFTER LOGIN

- **Permissions (before context):** dashboard.view, students.view, academics.view, attendance.view, attendance.create, attendance.update, attendance.reports, exams.view, exams.reports, homework.view, homework.create, homework.update, homework.delete, timetable.view, timetable.reports, academic_calendar.view, student_documents.view
## 1. AFTER LOGIN

- **hasRole(Teacher) before context:** true
## 1. AFTER LOGIN

- **applySchoolContext returned:** 1
## 1. AFTER LOGIN

- **getPermissionsTeamId() after apply:** 1
## 1. AFTER LOGIN

- **Roles (after context):** Teacher
## 1. AFTER LOGIN

- **Role Names (after context):** Teacher
## 1. AFTER LOGIN

- **Permissions (after context):** dashboard.view, students.view, academics.view, attendance.view, attendance.create, attendance.update, attendance.reports, exams.view, exams.reports, homework.view, homework.create, homework.update, homework.delete, timetable.view, timetable.reports, academic_calendar.view, student_documents.view
## 1. AFTER LOGIN

- **hasRole(Teacher) after context:** true
## 2. BEFORE DashboardFactory::make()

- **SchoolContext ID:** 1
## 2. BEFORE DashboardFactory::make()

- **getPermissionsTeamId():** 1
## 2. BEFORE DashboardFactory::make()

- **Roles:** Teacher
## 2. BEFORE DashboardFactory::make()

- **Role Names:** Teacher
## 2. BEFORE DashboardFactory::make()

- **Permissions:** dashboard.view, students.view, academics.view, attendance.view, attendance.create, attendance.update, attendance.reports, exams.view, exams.reports, homework.view, homework.create, homework.update, homework.delete, timetable.view, timetable.reports, academic_calendar.view, student_documents.view
## 2. BEFORE DashboardFactory::make()

- **ROLE_PRIORITY:** {"Super Admin":"App\\Modules\\Dashboard\\Services\\Builders\\AdminDashboardBuilder","School Admin":"App\\Modules\\Dashboard\\Services\\Builders\\AdminDashboardBuilder","Principal":"App\\Modules\\Dashboard\\Services\\Builders\\PrincipalDashboardBuilder","Teacher":"App\\Modules\\Dashboard\\Services\\Builders\\TeacherDashboardBuilder","Staff":"App\\Modules\\Dashboard\\Services\\Builders\\StaffDashboardBuilder","Parent":"App\\Modules\\Dashboard\\Services\\Builders\\ParentDashboardBuilder"}
## 3. INSIDE DashboardFactory::make()

- **ROLE_PRIORITY iteration:** see above
## 3. INSIDE DashboardFactory::make()

- **hasRole(Super Admin):** false
## 3. INSIDE DashboardFactory::make()

- **Reason Super Admin failed:** User is NOT assigned role 'Super Admin' in model_has_roles table.
## 3. INSIDE DashboardFactory::make()

- **hasRole(School Admin):** false
## 3. INSIDE DashboardFactory::make()

- **Reason School Admin failed:** User is NOT assigned role 'School Admin' in model_has_roles table.
## 3. INSIDE DashboardFactory::make()

- **hasRole(Principal):** false
## 3. INSIDE DashboardFactory::make()

- **Reason Principal failed:** User is NOT assigned role 'Principal' in model_has_roles table.
## 3. INSIDE DashboardFactory::make()

- **hasRole(Teacher):** true
## 3. INSIDE DashboardFactory::make()

- **hasRole(Staff):** false
## 3. INSIDE DashboardFactory::make()

- **Reason Staff failed:** User is NOT assigned role 'Staff' in model_has_roles table.
## 3. INSIDE DashboardFactory::make()

- **hasRole(Parent):** false
## 3. INSIDE DashboardFactory::make()

- **Reason Parent failed:** User is NOT assigned role 'Parent' in model_has_roles table.
## 3. INSIDE DashboardFactory::make()

- **Builder selected:** Teacher
## 5. VERIFY DATABASE

- **model_has_roles rows:** [
    {
        "role_id": 4,
        "model_type": "App\\Models\\User",
        "model_id": 3,
        "school_id": 1
    }
]
## 5. VERIFY DATABASE

- **roles table:** [
    {
        "id": 1,
        "school_id": 1,
        "name": "Super Admin",
        "guard_name": "web",
        "created_at": "2026-06-24 06:06:17",
        "updated_at": "2026-06-24 06:06:17"
    },
    {
        "id": 2,
        "school_id": 1,
        "name": "School Admin",
        "guard_name": "web",
        "created_at": "2026-06-24 06:06:18",
        "updated_at": "2026-06-24 06:06:18"
    },
    {
        "id": 3,
        "school_id": 1,
        "name": "Principal",
        "guard_name": "web",
        "created_at": "2026-06-24 06:06:19",
        "updated_at": "2026-06-24 06:06:19"
    },
    {
        "id": 4,
        "school_id": 1,
        "name": "Teacher",
        "guard_name": "web",
        "created_at": "2026-06-24 06:06:20",
        "updated_at": "2026-06-24 06:06:20"
    },
    {
        "id": 5,
        "school_id": 1,
        "name": "Student",
        "guard_name": "web",
        "created_at": "2026-06-24 06:06:21",
        "updated_at": "2026-06-24 06:06:21"
    },
    {
        "id": 6,
        "school_id": 1,
        "name": "Parent",
        "guard_name": "web",
        "created_at": "2026-06-24 06:06:22",
        "updated_at": "2026-06-24 06:06:22"
    },
    {
        "id": 7,
        "school_id": 1,
        "name": "Accountant",
        "guard_name": "web",
        "created_at": "2026-06-24 06:06:22",
        "updated_at": "2026-06-24 06:06:22"
    },
    {
        "id": 8,
        "school_id": 1,
        "name": "Librarian",
        "guard_name": "web",
        "created_at": "2026-06-24 06:06:23",
        "updated_at": "2026-06-24 06:06:23"
    },
    {
        "id": 9,
        "school_id": 1,
        "name": "Payroll Manager",
        "guard_name": "web",
        "created_at": "2026-06-24 06:06:24",
        "updated_at": "2026-06-24 06:06:24"
    },
    {
        "id": 10,
        "school_id": 1,
        "name": "Receptionist",
        "guard_name": "web",
        "created_at": "2026-06-24 06:06:25",
        "updated_at": "2026-06-24 06:06:25"
    },
    {
        "id": 11,
        "school_id": 1,
        "name": "HR",
        "guard_name": "web",
        "created_at": "2026-06-24 06:06:26",
        "updated_at": "2026-06-24 06:06:26"
    },
    {
        "id": 12,
        "school_id": 1,
        "name": "Staff",
        "guard_name": "web",
        "created_at": "2026-06-24 06:06:27",
        "updated_at": "2026-06-24 06:06:27"
    }
]
## 5. VERIFY DATABASE

- **school_user rows:** [
    {
        "id": 3,
        "school_id": 1,
        "user_id": 3,
        "designation": "Teacher",
        "employee_code": "T-1001",
        "joined_at": "2021-06-24",
        "status": "active",
        "is_primary": 0,
        "created_at": "2026-06-24 06:32:45",
        "updated_at": "2026-06-24 06:32:45"
    }
]
## 5. VERIFY DATABASE

- **user row:** {
    "id": 3,
    "name": "Aisha Khan",
    "email": "aisha.khan@example.com",
    "current_school_id": 1,
    "is_super_admin": 0
}
## 6. VERIFY MIDDLEWARE

- **Middleware execution order:** auth -> school -> DashboardController
## 6. VERIFY MIDDLEWARE

- **SetSchoolContext.handle operations:** SchoolContext::set() + PermissionRegistrar::setPermissionsTeamId() + session()
## 6. VERIFY MIDDLEWARE

- **DashboardService.build operations:** SchoolContext::id() -> setPermissionsTeamId() -> DashboardFactory::make() -> builder->build()
## 7. VERIFY CACHING

- **Cache cleared:** permission + config + application + route
## 7. VERIFY CACHING

- **getPermissionsTeamId() after cache clear:** 1
## 7. VERIFY CACHING

- **Roles after cache clear:** Teacher
## 7. VERIFY CACHING

- **Role Names after cache clear:** Teacher
## 7. VERIFY CACHING

- **hasRole(Teacher) after cache clear:** true

---

## Root Cause Analysis

No definitive root cause identified from the diagnostic data. Review the full output above.

---

## Evidence

See above sections for complete runtime state at each pipeline stage.