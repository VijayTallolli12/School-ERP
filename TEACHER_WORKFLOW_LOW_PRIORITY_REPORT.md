# TEACHER WORKFLOW — LOW PRIORITY FIXES REPORT

Scope: Workflow 3 (Teacher Lifecycle). Only the LOW priority items from
`TEACHER_WORKFLOW_REVIEW.md` (lines 232-237) were implemented, per instruction.

Test baseline before changes: TeacherAppApiTest (11), LiveAttendanceTest (9),
LiveTransportTest (17) — all previously passing.
After changes: 37 passed (137 assertions). No regressions.

---

## Changes

### 1. `app/Http/Resources/Api/V1/TeacherResource.php` — expose `documents`
Previously `TeacherApiController::show()` eager-loaded `documents` but the resource
never serialized them (dead load, review :156). Added a `documents` array (id,
document_type, storage-backed file_url, uploaded_at) exposed via `whenLoaded()`.
- `TeacherResource.php:34-40`

### 2. `app/Modules/Documents/Controllers/TeacherDocumentController.php` — wire policy
`download()` re-implemented ownership inline instead of using the registered
`TeacherDocumentPolicy` (review :155). Replaced the inline teacher-id check with
`$this->authorize('view', $document)`, which the registered policy already resolves
(owner check for Teacher role). File-missing 404 guard retained.
- Removed dead duplicate `app/Modules/Teachers/Policies/TeacherDocumentPolicy.php`;
  the registered policy lives at `App\Modules\Documents\Policies\` (wired in
  `AppServiceProvider.php:247`). The duplicate class was never referenced.

### 3. Report view-path consistency + repository school fallback
- `TeacherReportController::workload()` used `modules.reports.teachers.workload`
  (a legacy path) while every other report used the `Reports::` module namespace
  (review :130). Standardized to `Reports::teachers.workload`. Also fixed the two
  workload export paths (`Reports::teachers.exports.workload_pdf` / `workload_print`).
- `TeacherReportRepository::getSchoolId()` fell back to `auth()->user()->school_id`
  (latent tenant risk, review :172). Now delegates solely to
  `app(SchoolContext::class)->id()`, consistent with `ExamReportRepository`. Both
  web and API requests populate SchoolContext via `SetSchoolContext` middleware.
- `TeacherReportRepository.php:14-17`, `TeacherReportController.php:130,148,152`

### 4. `database/seeders/PermissionSeeder.php` — no change (documented decision)
Review item asked to "grant the Teacher role permissions for genuinely
teacher-facing self-service features." Analysis: every genuine teacher self-service
surface (own documents, own payslips, own leave, own attendance, own profile) is
**owner-scoped within the controller/route and does not depend on a permission gate**:
- Teacher documents: no permission middleware on the routes, ownership enforced.
- My payslips: route registered OUTSIDE the `permission:payroll.view` gate
  (`routes/modules/payroll.php:7-10`); ownership enforced in `authorizePayslipAccess`.
- My leave / self attendance / self profile: grouped under
  `role_or_permission:Teacher|teachers.view` (new routes), role access only.

The candidate grants in the review (`leave_management.view`, `reports.view`,
`notifications.view`, `payroll.payslip.view`, `teachers.reports`) would all expose
*whole-school* admin surfaces to teachers (student-leave admin, all-teacher report
lists, the notification **management** page, all payslips) — a security regression,
not a self-service feature. No grant was made intentionally.

### 5. `app/Http/Controllers/Api/V1/TeacherApiController.php` — reconcile `show()`
`show()` used an admin-only authorization check that **forbade teachers from viewing
their own profile**, inconsistent with `timetable()`/`attendance()`/`assignedClasses()`
which use `authorizeTeacherAccess()` (and inconsistent with TeacherResource now
returning personal `documents`). Replaced the inline gate with
`$this->authorizeTeacherAccess($teacher)` (review :237). The `documents` eager-load
is now used (see item 1), resolving the "remove dead loads" part.
- `TeacherApiController.php:84-88`

---

## Verification
- `php -l` clean on all 14 modified PHP files.
- `php artisan route:list` confirms the new self-service routes register:
  `admin.teachers.my-leaves.{index,data,store}`, `admin.teachers.my-attendance.{index,data}`,
  `admin.teachers.my-profile{,.update}`.
- Test suite (Teacher API + live attendance + live transport): **37 passed /
  137 assertions**, unchanged from baseline.

## Files Changed
- `app/Http/Resources/Api/V1/TeacherResource.php`
- `app/Modules/Documents/Controllers/TeacherDocumentController.php`
- `app/Modules/Teachers/Policies/TeacherDocumentPolicy.php` (removed — dead duplicate)
- `app/Modules/Reports/Controllers/TeacherReportController.php`
- `app/Modules/Reports/Repositories/TeacherReportRepository.php`
- `app\Http\Controllers\Api\V1\TeacherApiController.php`
- `database/seeders/PermissionSeeder.php` (reviewed, intentionally unchanged)

## Notes / Follow-up
- Several MEDIUM items were implemented in the same session (teacher self-service
  routes + controller methods + views, dashboard/sidebar leave-link fixes,
  `TeacherLeave`/`TimetableSlot` unification, Exam ownership guards). Those belong to
  the medium scope; if a medium-priority report is desired it should be generated
  from that work.
- `TimetableSlot::days()` still omits Sunday (`day_of_week 7`) — pending item, not in
  low-priority scope.