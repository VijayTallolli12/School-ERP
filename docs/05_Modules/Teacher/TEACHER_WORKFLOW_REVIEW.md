# TEACHER WORKFLOW REVIEW — Workflow 3 (Teacher Lifecycle)

Status: **Review only — no code modified.**
Scope: Teacher Login, Dashboard, Attendance, Homework, Timetable, Exams, Results, Leave,
Documents, Reports, Payslips, Profile (web module + mobile API). Unrelated modules not
inspected.

---

## 1. Teacher Login

### Existing Features
- Web login via generic auth (`routes/modules/auth.php`); teacher is a `User` with the `Teacher` role. `TeacherService::createOrUpdateUser()` creates the linked `User` + `Teacher` role when `create_user` is enabled (`TeacherService.php:202-243`).
- Mobile: `POST /api/v1/teacher/login` (`routes/modules/api.php:27-29` → `TeacherAppController::login:68-118`) validates role, active status, issues Sanctum token, logs via `LoginActivityService`.
- Shared `me`, `auth/refresh`, `auth/logout` (`routes/modules/api/auth.php:10-12`).

### Missing Features
- No dedicated teacher web portal/guard — a `Teacher` lands on the generic admin dashboard.
- No forced-password-change / first-login enforcement (`force_password_change` column exists but unused).

### Broken Features
- `TeacherAppController::login:90-91` derives `$schoolId` from `$user->current_school_id ?? first school link`, never calls `SchoolContext::set()`, and ignores `teacher->school_id` → wrong permission team possible for multi-school teachers.
- `teacher-app` API routes (teacher-app.php:12-59) have **no role/permission middleware**; enforcement is only in controller logic.

---

## 2. Dashboard

### Existing Features
- Web: `TeacherDashboardBuilder` (stat cards: Today's Classes, Pending Homework, Upcoming Exams, Attendance Pending; widgets: Today's Schedule, Student Attendance donut, Leave Overview; quick actions) + `TeacherDashboardCollector`.
- Mobile: `TeacherAppController::dashboard:183-257` returns today's classes, own attendance, pending homework, upcoming exams, unread notifications.
- Sidebar `SidebarBuilder::buildForTeacher()` (`SidebarBuilder.php:124-145`).

### Missing Features
- No self attendance card / own-leave balance / payslip / document widgets on web dashboard.
- Charts and recent-activity return empty arrays (`TeacherDashboardBuilder.php:122-130`).

### Broken Features
- **Leave Overview widget reads the student-leave `LeaveRequest` table, not `TeacherLeave`** (`TeacherDashboardCollector.php:93-107`), so a teacher's own leave never appears.
- Dashboard "Apply Leave" / Leave widget link to `admin.leave-requests.index` which requires `leave_management.view` **not granted to Teacher** → 403 (`TeacherDashboardBuilder.php:87,100`).
- `TeacherService::getDashboardStats()` (`TeacherService.php:78-85`) is dead code and **not school-scoped**.
- Sidebar Leave / My Documents / My Payslips entries pass `null` permission and are un-scoped (`SidebarBuilder.php:136-138`).

---

## 3. Attendance

### Existing Features
- Teacher (staff) attendance — admin-managed: `teachers/attendance/*` routes + `TeacherController::attendance*` (`:143-220`), `TeacherService::record/update/deleteAttendance` (`:117-149`), `MarkTeacherAttendanceRequest`.
- Student attendance — teachers can mark (have `attendance.create/update`): `AttendanceController` scopes by teacher's sections; mobile `TeacherAppController::attendanceClasses/attendanceStudents/markAttendance` (`:310-445`).
- Admin API: `GET /api/v1/teachers/{uuid}/attendance` (`TeacherApiController::attendance`).

### Missing Features
- No teacher **self-attendance** view on the web (sidebar Attendance routes to student-attendance marking). Own attendance only visible in the mobile dashboard.

### Broken Features
- `markAttendance` (`TeacherAppController.php:389-393`) validates `student_id` only against `exists:students` — **never verifies the student is enrolled in the submitted class**.
- `attendanceStudents` (`:341-367`) runs one query per student (N+1) and does not scope `Attendance` by `class_section_id`.
- `AttendanceService::markAttendance` hardcodes `marked_by => auth()->id()` (AttendanceService.php:27), ignoring the controller's passed value.

---

## 4. Homework

### Existing Features
- Web: full CRUD (`routes/modules/homework.php`), teacher-scoped `index`/`data`, ownership guard in `store` (`HomeworkController.php:89-95`), `HomeworkPolicy`.
- Mobile: `GET/POST/PUT teacher/homework(/{id})` (`TeacherAppController.php:451-576`) + attachment handling.

### Missing Features
- No homework submission/grading flow (create/edit/delete only).
- No per-subject authorization on create (section ownership only).

### Broken Features
- **`homeworkStore` (`TeacherAppController.php:512`) writes `null` into NOT NULL `homework.academic_year_id` when no active academic year → 500**.
- `homeworkStore` validates `subject_id` only by existence, not teacher assignment (`:500`).
- `homeworkUpdate` (`:532-539`) does not validate `due_date >= assigned_date`.

---

## 5. Timetable

### Existing Features
- Web: timetable CRUD + `teacher-schedule` + print (`routes/modules/timetable.php`), `TimetableController::teacherSchedule` (`:172-196`).
- Mobile: `GET teacher/timetable` (`TeacherAppController.php:263-304`); admin API `GET /api/v1/teachers/{uuid}/timetable`.

### Missing Features
- No teacher-scoped web timetable view; shared `index` shows all classes and `data()` has no teacher auto-filtering → cross-visibility leak.

### Broken Features
- **Duplicate model for the same table**: `Timetable\TimetableSlot` vs stub `Teachers\TeacherTimetableSlot`; `Teacher::timetableSlots()` (`Teacher.php:99-102`) and `TeacherReportRepository::workload()` (`:201-207`) couple to the stub.
- Sunday (`day_of_week 7`) not in `TimetableSlot::days()` → `'Unknown'`/missing classes in dashboard & timetable (`dashboard:188`, `timetable:270`).
- Timetable with no active academic year returns all years instead of none.

---

## 6. Exams

### Existing Features
- Web: exam CRUD + result entry + bulk marks + grade scales + schedules (`routes/modules/exams.php`); `ExamController` scoped to teacher's sections; `ExamPolicy` (teachers can create/update, not delete/publish).
- Mobile: `examsIndex/examsShow/examsStoreMarks` (`TeacherAppController.php:582-709`) with optional publish.

### Missing Features
- No teacher grid/result drop for the schedule/marks flow (`ExamMarkController` has no teacher ownership guard).

### Broken Features
- `examsStoreMarks` (`:689-696`) validates `student_id` only against `exists:students` — **never checks membership in the exam's class section**.
- `examsShow` runs one result query per student (N+1).
- `marks_obtained` cast integer (`ExamResult.php:31`) — no decimal/half marks.
- `bulkSave` publish branch (`ExamController.php:309`) 403s for teachers who lack `exams.publish` even though they saved marks.

---

## 7. Results

### Existing Features
- Results surface via Exams only: per-student result rows in `examsShow` (`:647-653`) and mark entry via `examsStoreMarks` → `ExamService::bulkSave`.

### Missing Features
- No dedicated teacher Results area: no report cards, grade book, class-average/subject analysis, or export for teachers.

### Broken Features
- No dedicated results endpoints for teachers (report-card endpoint exists only for student app / exam API).

---

## 8. Leave

### Existing Features
- Admin manages teacher leave via `teacher_leaves` (`routes/modules/teachers.php:23-28`, `TeacherController::leave*` `:222-300`, `TeacherService::request/update/deleteLeave` `:151-200`).
- Mobile: `GET/POST teacher/leave` + `leave-types` (`TeacherAppController.php:715-772,934-945`).
- Web student-leave self-service `my-leaves` (`routes/modules/leave.php:38-43`).

### Missing Features
- **No teacher self-service for their own `TeacherLeave`** on web or mobile. The mobile `leaveStore` requires `student_id` and notifies "student submitted a request" (`LeaveService.php:139,149`) — it is student leave, not teacher leave.

### Broken Features
- **Two disconnected leave systems** (`TeacherLeave` vs student `LeaveRequest`) with no teacher self path.
- Sidebar/Dashboard Leave links → `admin.leave-requests.index` require `leave_management.view` (Teacher lacks) → 403.
- `leaveStore` doesn't verify the student belongs to a class the teacher teaches (`TeacherAppController.php:755-762`).
- `leaveIndex` eager-load omits `middle_name` (`:721`) so `full_name` accessor is incomplete.

---

## 9. Documents

### Existing Features
- Web self-service (no permission middleware): `teacher-documents` index/data/download (`routes/modules/documents.php:20-24`, `TeacherDocumentController`). Download verifies ownership (`:45-58`).
- Documents created during admin teacher create/update via `TeacherService::syncDocuments()` (`:280-307`).

### Missing Features
- No upload of own documents (create is admin-only).
- **No API coverage** (Documents are web-only).

### Broken Features
- `TeacherDocumentPolicy` is dead code — the controller re-implements ownership inline (`TeacherDocumentController.php:49`).
- `TeacherApiController::show` eager-loads `documents` but `TeacherResource` never serializes it (`:70-77`).

---

## 10. Reports

### Existing Features
- Admin-only report suite (teachers list, attendance, subject-allocation, class-teacher map, workload + exports) gated by `reports.view` / `teachers.reports` (`app/Modules/Reports/routes.php:86-95`, `TeacherReportController`).
- Legacy reports in Teachers module (`teachers/reports/subjects`, `teachers/reports/attendance`).

### Missing Features
- No teacher self-service report view (teachers lack both `reports.view` and `teachers.reports`) — no "My Attendance"/"My Workload" for a teacher.
- **No API coverage.**

### Broken Features
- `TeacherReportController::workload()` uses non-namespaced `modules.reports.teachers.workload` (`:130`) vs `Reports::teachers.*` everywhere else.
- `TeacherReportRepository::getSchoolId()` falls back to `auth()->user()->school_id` (`:14-17`) — latent runtime risk.
- Workload `withCount(['timetableSlots'])` couples to the stub `TeacherTimetableSlot` model.

---

## 11. Payslips

### Existing Features
- **Full teacher self-service payslips** (`routes/modules/payroll.php:7-10`): my payslips, data, print, PDF (`PayrollController::myPayslips` `:781-786`, `myPayslipsData` `:788-811`, plus owner-branch in `authorizePayslipAccess` `:416-428`). Sidebar "My Payslips" → `admin.payroll.payslips.my`.

### Missing Features
- No payslip quick action/link on the teacher dashboard.
- **No API coverage** (web-only).

### Broken Features
- None functional; minor route-name inconsistency (`payroll.payslips.my` vs `payroll.my-payslips.data`).

---

## 12. Profile

### Existing Features
- Admin update: `TeacherController@update` (`:97-106`) with `UpdateTeacherRequest` (route gated `teachers.update`).
- Mobile API self-profile: `GET/PUT teacher/profile` + `changePassword` (`TeacherAppController.php:132-177`).

### Missing Features
- **No web self-service profile update for teachers** (teachers lack `teachers.view`); profile editing is mobile-API only.

### Broken Features
- `changePassword` (`:160-177`) doesn't enforce `force_password_change`, doesn't revoke old tokens, returns generic 422.
- `updateProfile` (`:156`) reloads without `subjects`, inconsistent payload vs `profile()`.
- `logout`/`notifications*`/`changePassword` operate purely on `request()->user()->id` and are callable by **any** authenticated role (parent/student/driver) — security gap.

---

## Cross-Cutting Concerns (affect multiple areas)

1. Teacher-role seed permissions are narrow (`PermissionSeeder.php:105-110`); no `teachers.*`, `leave_management.*`, `reports.view`, `notifications.view`, `payroll.payslip.view`. Several "self-service" routes therefore 403 for teachers.
2. Missing role/permission gates on teacher-app API routes (C1) — some endpoints reachable by any auth user.
3. Missing record-level ownership validation in writes: attendance/marks/leave/homework do not verify students/subjects belong to the teacher's classes.
4. Widespread N+1 / lazy-loaded relations in `TeacherAppController`.
5. Duplicate `TeacherTimetableSlot` vs `TimetableSlot` model coupling.

---

## Files Requiring Modification

### High priority (correctness / security)
- `app/Http/Controllers/Api/V1/TeacherAppController.php` — homeworkStore null `academic_year_id` (500, :512); student/class ownership in `markAttendance` (:389), `examsStoreMarks` (:689), `leaveStore` (:755); subject ownership in `homeworkStore` (:500); Sunday handling (:188,:270); eager-loads; `SchoolContext::set()` in `login` (:90); `force_password_change` + token revocation in `changePassword` (:160); scope `logout`/`notifications*` by role.
- `routes/modules/api/teacher-app.php` — add `role:Teacher` middleware; add teacher self-leave, own-attendance history, documents, reports, payslips endpoints.
- `app/Modules/Attendance/Services/AttendanceService.php` — honor passed `marked_by` (:27).

### Medium priority (feature gaps)
- `app/Modules/Dashboard/Services/Builders/TeacherDashboardBuilder.php` — fix leave widget/route (403), add payslip/doc/self-attendance links.
- `app/Modules/Dashboard/Services/DataCollectors/TeacherDashboardCollector.php` — use `TeacherLeave` for leave balance; dead `getDashboardStats`.
- `app/Modules/Dashboard/Services/SidebarBuilder.php` — scope/gate teacher sidebar items (:136-138).
- `routes/modules/teachers.php` + `TeacherController.php` — add teacher self-profile / self-attendance / self-leave routes.
- `app/Modules/Timetable/Models/TimetableSlot.php` vs `app/Modules/Teachers/Models/TeacherTimetableSlot.php` + `Teacher.php:99` + `TeacherReportRepository.php:201` — unify onto single timetable model.
- `app/Modules/Exams/Controllers/ExamController.php` / `ExamMarkController.php` — teacher ownership guards; publish-403 in bulkSave (:309).

### Low priority (cleanup / consistency)
- `app/Http/Resources/Api/V1/TeacherResource.php` — expose `documents`.
- `app/Modules/Documents/Controllers/TeacherDocumentController.php` — wire or remove dead `TeacherDocumentPolicy`.
- `app/Modules/Reports/Controllers/TeacherReportController.php` (`:130` view path) and `TeacherReportRepository.php` (`:14` school fallback).
- `database/seeders/PermissionSeeder.php` — grant the Teacher role permissions for genuinely teacher-facing self-service features.
- `app/Http/Controllers/Api/V1/TeacherApiController.php` — reconcile `show()` authorization and remove dead loads.

---

## Estimated Implementation Order

1. **Fixes (correctness):** `TeacherAppController` — homeworkStore null-year 500, ownership validation (attendance/exam-marks/leave), Sunday handling, `SchoolContext::set()` in login. Add `role:Teacher` middleware to teacher-app routes.
2. **Security:** scope `logout`/`notifications*`/`changePassword`; token revocation on password change; `force_password_change`.
3. **Teacher self-leave & self-attendance:** unify `TeacherLeave` into the teacher mobile/web surface; add self-attendance view; fix dashboard + sidebar leave/attendance links (403s).
4. **Documents, Reports, Payslips APIs:** add teacher-app endpoints; expose documents in `TeacherResource`; delegate reports/payslips to existing repositories.
5. **Results:** teacher report-cards / grade book / analysis endpoints.
6. **Timetable model unification:** collapse duplicate `TeacherTimetableSlot` into `TimetableSlot`.
7. **Eager-loading / N+1 cleanups** across `TeacherAppController`.
8. **Permission seeding + web self-service profile** for teachers.
9. **Cleanup:** dead policies, view-path consistency, unused loads.

---

*This is a read-only review. No code was modified.*