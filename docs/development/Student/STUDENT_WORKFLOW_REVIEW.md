# STUDENT WORKFLOW REVIEW

## Scope
Review limited to Workflow 5: Student Portal.
Modules covered:
- Student Login
- Dashboard
- Attendance
- Homework
- Assignments
- Exams
- Results
- Timetable
- Calendar
- Leave
- Documents
- Notifications
- Fees
- Profile
- Change Password

This review is based on the student-app API routes, `StudentAppController`, `StudentDashboardBuilder`, student portal-related test coverage, and supporting docs.

---

## 1. Existing Features

### Critical / High
- Student Login
  - `POST /api/v1/student/login` exists and is covered by tests.
  - Login returns user, student profile, token, and school context.
- Profile
  - `GET /api/v1/student/profile` and `PUT /api/v1/student/profile` exist.
  - Student profile data is loaded with sessions, class/section, academic year, and guardians.
- Change Password
  - `PUT /api/v1/student/change-password` exists and validates current password.
- Dashboard
  - `GET /api/v1/student/dashboard` exists.
  - Student dashboard returns attendance %, pending homework count, upcoming exams, issued books, notifications unread count, and current session info.
- Attendance
  - `GET /api/v1/student/attendance` returns monthly attendance records.
  - `GET /api/v1/student/attendance/monthly` returns monthly status breakdown and percentage.
  - `GET /api/v1/student/attendance/summary` returns academic-year summary.
- Homework
  - `GET /api/v1/student/homework` returns active class-section homework.
  - `GET /api/v1/student/homework/{id}` returns homework detail.
- Timetable
  - `GET /api/v1/student/timetable` returns timetable grouped by day.
- Exams / Results
  - `GET /api/v1/student/exams` returns scheduled exams.
  - `GET /api/v1/student/results` returns exam results grouped by academic year.
  - `GET /api/v1/student/report-card` returns report card grouped by exam type.
- Notifications
  - `GET /api/v1/student/notifications` returns bell data.
  - `POST /api/v1/student/notifications/read` marks notifications read.

### Medium / Low
- Library support is present in student app (`library/books`, `library/history`, `library/fines`), though it is outside the requested module list.
- Student dashboard builder exists (`app/Modules/Dashboard/Services/Builders/StudentDashboardBuilder.php`) and is mapped in `DashboardFactory`.
- Student app test coverage is solid for currently implemented endpoints.

---

## 2. Missing Features

### Critical
- Assignments
  - No dedicated Student App endpoint for assignments.
  - The current portal implements homework, but a separate assignments workflow is not exposed.
- Fees
  - No dedicated student-app fee endpoint under `routes/modules/api/student-app.php`.
  - Fees are missing from the student dashboard and portal API surface.
- Calendar
  - No dedicated student-app calendar endpoint.
  - Student portal cannot currently retrieve academic calendar or event data.
- Leave
  - No dedicated student-app leave endpoint.
  - Leave request creation, history, and status are absent from student portal APIs.
- Documents
  - No dedicated student-app document endpoint.
  - Student portal cannot retrieve student documents or attachments.

### High / Medium
- UI coverage for the missing modules is not evident.
  - The student portal route list includes API endpoints only.
  - There is no clear student UI page for assignments, fees, calendar, leave, or documents in the reviewed sources.
- Fees-related dashboard metrics are absent despite `fees.view` being part of the student role permission seed.
- The Student Dashboard currently returns only attendance, homework, exams, issued books, and notifications; it omits fees, calendar events, leave status, and documents.

---

## 3. Broken Features

### None identified from review
- Existing student-app endpoints have passing feature tests in `tests/Feature/StudentAppApiTest.php`.
- No explicit failing student portal endpoint was found in the reviewed sources.

### Potential concerns
- `homeworkIndex()` returns `status = active` homework without explicit due-date scoping; this may surface homework after its due date.
- No student-side fee or document endpoints means these workflows are effectively broken/missing for portal users.

---

## 4. Security Issues

### High
- Student login endpoint does not show explicit rate limiting in `StudentAppController`.
  - If API login is not protected by global throttle middleware, this is a risk.
- `student-app` routes are authenticated, but not guarded by explicit student-specific permission middleware.
  - Access control relies on `resolveStudent()` scoping and the authenticated user.

### Medium
- Token abilities are generated from `User::getAllPermissions()` at login.
  - If a student user is granted excessive permissions, the token may inherit too much access.
- There is no evidence of a dedicated student API scope or guard separate from generic school API usage.

### Low
- `notifications/read` is a bulk mark-all-read endpoint and does not support item-level acknowledgement, which may be a usability and audit limitation rather than a direct vulnerability.

---

## 5. Performance Issues

### Medium
- Dashboard query patterns are simple but not optimized for scale.
  - `dashboard()` uses separate attendance, homework, and exam queries.
  - `results()` fetches all exam results before grouping in memory.
- Homework list is paginated, but `homeworkIndex()` may still load extra relations without a stronger filter.

### Low
- `timetable()` groups full day slots in memory; acceptable for normal student schedules, but may need pagination or caching for large schedule sets.

---

## 6. Missing APIs

### Critical
- `GET /api/v1/student/fees` or equivalent fee summary endpoint.
- `GET /api/v1/student/calendar` for academic calendar/events.
- `GET /api/v1/student/leave` and `POST /api/v1/student/leave` for leave request workflows.
- `GET /api/v1/student/documents` for student document retrieval.
- `GET /api/v1/student/assignments` for assignment-specific workflows.

### High
- `GET /api/v1/student/notifications/unread` or itemized notification listing if bell data is not sufficient.
- `DELETE`/`PATCH` or individual `notifications/{id}` actions for finer notification control.

### Medium
- `GET /api/v1/student/fees/history` or payment receipt endpoints.
- `GET /api/v1/student/calendar/events` with event filtering.

---

## 7. Missing UI

### Critical
- Student-facing UI pages for:
  - Assignments
  - Fees
  - Calendar
  - Leave
  - Documents
- These modules are not exposed in the student-app route list and are absent from the reviewed student portal feature docs.

### High
- Student portal navigation appears limited to dashboard, attendance, timetable, exams, and notifications.
- There is no clear portal UI for viewing or interacting with fees, leave requests, or document attachments.

### Medium
- The student web portal layout currently uses the existing dashboard builder, but richer student portal navigation and module pages are still needed.

---

## 8. Missing Permissions

### High
- No dedicated `student.*` permission middleware is applied to the student-app route definitions in the reviewed route file.
- Student portal access control currently depends on authenticated user context rather than explicit student role permission checks.

### Medium
- Student role permissions are seeded with `dashboard.view`, `attendance.view`, `fees.view`, `exams.view`, but student app routes do not enforce these permissions.
- If permission seeding or role assignment is inconsistent, student-app tokens may still authenticate but not correctly restrict access.

### Low
- There is no evidence of distinct mobile vs web student permission separation for the student portal.

---

## 9. Estimated Implementation Order

### Critical
1. Implement student-app APIs for Fees summary and fee detail.
2. Implement student-app APIs for Calendar events and academic calendar.
3. Implement student-app APIs for Leave request submission, history, and status.
4. Implement student-app APIs for Documents access/download.
5. Implement student-app APIs for Assignments (or clarify assignment vs homework workflow).

### High
6. Add student-facing UI/pages for Fees, Calendar, Leave, Documents, and Assignments.
7. Harden student login with rate limiting and dedicated student API guard.
8. Introduce explicit student permission middleware or scoped student API permissions.

### Medium
9. Add pending fees and leave status to the student dashboard.
10. Add item-level notification controls and unread list support.
11. Add tests for newly added student-app endpoints.

### Low
12. Improve dashboard query efficiency and caching for student portal data.
13. Align StudentDashboardBuilder output with all supported student modules.
14. Add richer student portal UX documentation and guided student navigation.

---

## Notes
- The review is intentionally scoped only to Workflow 5 / Student Portal.
- No code changes were made; this summary is based on route, controller, test, and documentation evidence.
