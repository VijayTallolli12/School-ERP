# Route Report — Principal Experience (Phase 03)

## Summary
**No routes were added or modified** in Phase 03. The Principal gains access to existing routes via newly assigned permissions.

---

## Routes Now Accessible to Principal

| Route Name | URL | HTTP Method | Permission Required | Previously Accessible To |
|------------|-----|-------------|---------------------|--------------------------|
| `admin.dashboard` | `/admin/dashboard` | GET | `dashboard.view` | Admin, Teacher |
| `admin.attendance.index` | `/admin/attendance` | GET | `attendance.view` | Admin, Teacher |
| `admin.timetable.index` | `/admin/timetable` | GET | `timetable.view` | Admin, Teacher |
| `admin.exams.index` | `/admin/exams` | GET | `exams.view` | Admin, Teacher |
| `admin.students.index` | `/admin/students` | GET | `students.view` | Admin, Teacher |
| `admin.teachers.index` | `/admin/teachers` | GET | `teachers.view` | Admin |
| `admin.homework.index` | `/admin/homework` | GET | `homework.view` | Admin, Teacher |
| `admin.calendar.index` | `/admin/calendar` | GET | `academic_calendar.view` | Admin, Teacher |
| `admin.fees.index` | `/admin/fees` | GET | `fees.view` | Admin |
| `reports.attendance.index` | `/reports/attendance` | GET | `reports.view` | Admin |
| `admin.leave-requests.index` | `/admin/leave-requests` | GET | `leave_management.view` | Admin, Teacher |
| `admin.leave-requests.approve` | `/admin/leave-requests/{id}/approve` | POST/PATCH | `leave_management.approve` | Admin |
| `admin.leave-requests.reject` | `/admin/leave-requests/{id}/reject` | POST/PATCH | `leave_management.approve` | Admin |
| `admin.notifications.index` | `/admin/notifications` | GET | `notifications.view` | Admin, Teacher |

## Routes Not Exposed to Principal

| Route Name | Reason |
|------------|--------|
| `admin.users.index` | Principal does not have `users.view` |
| `admin.settings.index` | Principal does not have `settings.view` |
| `admin.roles.*` / `admin.permissions.*` | Principal does not have `roles.view` / `permissions.view` |
| `admin.payroll.*` | Principal does not have `payroll.view` |
| `admin.leave-types.*` | Principal does not have `leave_management.update`/`delete` |
| `admin.documents.index` | Principal does not have `student_documents.view` (has `.verify` but not `.view`) |

## Route Middleware

No new middleware was created. The existing route middleware stack applies:

```
web → auth → verified → SetSchoolContext → permission:{permission_name}
```

The permission middleware checks the user's ability via Spatie's `can()` method, which reads from the cached permissions table — no route changes needed.
