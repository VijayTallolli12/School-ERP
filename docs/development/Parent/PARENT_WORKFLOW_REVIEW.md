# PARENT WORKFLOW REVIEW — Workflow 4 (Parent Workflow)

Status: **Review only — no code modified.**
Scope: Parent Login, Dashboard, Student List, Attendance, Homework, Fees, Results,
Leave, Calendar, Notifications, Documents, Profile (web portal + admin + API).
Unrelated modules not inspected.

---

## 1. Parent Login

### Existing Features
- Web: generic auth (`routes/modules/auth.php`) → `LoginController::store` routes Parents to
  `admin.parent-portal.dashboard` after `SetSchoolContext::applySchoolContext()` (`LoginController.php:43-47`).
- API: generic `POST api/v1/auth/login` (`ApiAuthController::login`); no dedicated `parent/login`
  (unlike `teacher/login`, `student/login`, `driver/login` — `routes/modules/api.php:22-39`).
- `FixParentPassword` / `FixParentRoles` console commands exist.

### Missing Features
- No dedicated parent-app controller or `parent/login` endpoint (parent mobile flow reuses the generic
  auth). No `force_password_change` enforcement on parent login/change-password.

### Broken Features
- Parent role is seeded with `parents.view` (`PermissionSeeder.php:116`), which is an **admin**
  permission — it exposes the admin Parents pages and the entire parents API to parents themselves (see
  Critical 1/2).

---

## 2. Dashboard

### Existing Features
- Web portal dashboard: `ParentController::dashboard()` → `modules.parents.dashboard` (children cards,
  attendance %, pending fees, avg score, active homework, notifications, recent homework, recent
  notifications, quick actions) fed by `ParentService::getParentDashboardData()`.
- Generic dashboard: `ParentDashboardBuilder` (`app/Modules/Dashboard/.../ParentDashboardBuilder.php`)
  mapped via `DashboardFactory`; quick actions → portal routes.
- Dashboard service uses SQL aggregation (attendance `GROUP BY`, fees `withSum`) to avoid N+1
  (`ParentService.php:149-216`).

### Missing Features
- No per-child attendance/fee/result drill-down widgets on the dashboard (aggregate across all children only).
- No charts / recent-activity on the generic builder (`buildCharts()` / `buildWidgets()` empty).

### Broken Features
- **Homework stat card always shows 0**: `ParentDashboardBuilder.php:38` reads `$homework['active_count']`
  but `ParentService::getHomeworkSummary()` returns the key `active` (`ParentService.php:329-335`).
- Hardcoded `$` currency on the portal dashboard (`dashboard.blade.php:49`) while the app standard is `₹`
  (used in `ParentDashboardBuilder.php:36`) → inconsistent/wrong currency.
- Web dashboard "Notifications" reads `Guardian::notifications()` → `parent_notifications` table, which is
  never written (see §Notifications) → always empty in practice.

---

## 3. Student List

### Existing Features
- Admin: `ParentController::show()` returns linked students with current class/section
  (`ParentController.php:66-101`); portal dashboard lists children as cards.
- API: `GET parents/{uuid}/children` (`ParentApiController::children:82-98`).

### Missing Features
- No dedicated "My Children" / student-list page in the web portal (only dashboard cards).
- No student detail (bio/roll/section) view for parents on web.

---

## 4. Attendance

### Existing Features
- Web portal: `parent-portal.attendance` (permission `attendance.view`) → per-student summary + last 10
  records (`attendance.blade.php`).
- API: `GET parents/{uuid}/children/{childUuid}/attendance` with month/year summary + records
  (`ParentApiController::childAttendance:100-141`).
- `ParentService::getAttendanceSummary()` uses a date-range window from the active academic year instead
  of `academic_year_id` because records may lack the FK (`ParentService.php:151-155`).

### Broken Features
- **Inconsistent academic-year filtering**: the portal view filters by `academic_year_id`
  (`attendance.blade.php:22-23`) while the dashboard service deliberately uses a date-range fallback.
  Records with a null `academic_year_id` count on the dashboard but are invisible on the attendance page.
- **N+1 / logic in the view**: `attendance.blade.php:21-31` runs a query + `whereIn` per student inline in
  the blade instead of using the service.

---

## 5. Homework

### Existing Features
- Web portal: `parent-portal.homework` (no permission middleware) → cards with subject/status (Completed/
  Overdue/Due Soon/Pending), due dates, attachment download (`homework.blade.php`).
- API: `GET parents/{uuid}/children/{childUuid}/homework` scoped to the child's active session
  (`ParentApiController::childHomework:252-284`).
- `ParentService::getHomeworkForStudents()` aggregates across children with active-session class sections
  (`ParentService.php:292-319`).

### Broken Features
- Portal view renders homework of all children grouped flatly; the service's `active()` + academic-year
  scoping differs from the API's `where('class_section_id')` + active-session scoping → subtle
  inconsistency in what is shown per surface.

---

## 6. Fees

### Existing Features
- Web portal: `parent-portal.fees` (permission `fees.view`) → per-student fee structures/items with
  paid/balance/overdue badges (`fees.blade.php`).
- API: `GET parents/{uuid}/children/{childUuid}/fees` (`ParentApiController::childFees:143-167`).
- Dashboard summary aggregates paid/pending via `withSum` (`ParentService.php:177-216`).

### Broken Features
- **N+1 / logic in the view**: `fees.blade.php:21-26` queries `StudentFee` inline per student in the blade.
- Hardcoded `$` currency (`fees.blade.php:50-52`) vs `₹` elsewhere.

### Missing Features
- No online payment / collect flow in the portal (view-only).
- No invoice/receipt download for parents on web.

---

## 7. Results

### Existing Features
- Web portal: `parent-portal.exam-results` (permission `exams.view`) → per-student published results
  grouped by exam with totals/percentage (`exam_results.blade.php`).
- API: `GET parents/{uuid}/children/{childUuid}/exams` grouped by academic year
  (`ParentApiController::childExamResults:169-194`).
- Dashboard average aggregates published exams for the active year (`ParentService.php:218-290`).

### Broken Features
- Portal groups by `exam.exam_name` after eager-loading only `exam.subject` (`exam_results.blade.php:22-29`);
  pass/fail and totals recomputed in the view.
- N+1 pattern: per-student loop with inline `ExamResult` query in the blade.

---

## 8. Leave

### Existing Features
- API only: `GET/POST parents/{uuid}/children/{childUuid}/leave-requests` + `show`/`update`
  (`ParentApiController::childLeaveRequests:488`, `storeLeaveRequest:530`, `updateLeaveRequest:597`,
  `showLeaveRequest:671`). Enforces: pending-only edits, date validation, auto-create/loose-match of a
  school-scoped `LeaveType`, day count = diffInDays + 1.

### Missing Features
- **No web portal leave page** (parent-portal routes have none).
- No leave-type list endpoint for parents to pick from (they must send a free-text name or id).

### Broken Features
- Duplicated leave-type resolution + validation logic in both `storeLeaveRequest` and `updateLeaveRequest`
  (~90 lines each).
- No ownership check on the parent (Critical 1 applies): a parent can create/edit a leave request for any
  linked child of any parent whose UUID they know.

---

## 9. Calendar

### Existing Features
- API: `GET parents/{uuid}/children/{childUuid}/calendar` → published events filtered to
  audience all/parents/students, by month/year/type (`ParentApiController::childCalendar:286-326`).

### Missing Features
- **No web portal calendar page**.
- No calendar surfaced on the parent dashboard.

### Broken Features
- API returns the raw `AcademicCalendar` models as `events` (no resource/serialization, inconsistent with
  other endpoints), and the child is only used to 404 — no per-student calendar scoping exists
  (calendar is audience-based only).

---

## 10. Notifications

### Existing Features
- Web portal: `parent-portal.notifications` → paginated list from `Guardian::notifications()`
  (`notifications.blade.php`, `ParentController::notifications:162-167`).
- API: `GET parents/{uuid}/circulars`, `{id}`, `POST {id}/read` → generic `notifications` table with
  `target_type='parents'`, per-user read tracking via `notification_user` pivot
  (`ParentApiController::childCirculars:370`, `childCircularDetail:402`, `markCircularRead:431`).
- Dashboard reads `notifications` (same `parent_notifications` source).

### Broken Features
- **Two disconnected notification systems.** The web portal (`Guardian::notifications()` →
  `ParentNotification` / `parent_notifications` table) is never written to — the table holds 0 records
  (`DATASET_HEALTH_REPORT.md`), so the portal notifications page and dashboard notifications are always
  empty. The API circulars use the generic `notifications` table and work.
- `ParentNotification::parents()` (`ParentNotification.php:48-55`) is contradictory: the docblock says a
  null `target_parents` means "all guardians", but the method returns an empty query (`whereRaw('1 = 0')`)
  in that case. (A previously reported fatal reference to a non-existent `Parent::class` was fixed to
  `Guardian::class`, but the all-targeting case is still broken.)

---

## 11. Documents

### Existing Features
- API: `GET parents/{uuid}/children/{childUuid}/documents` returns `StudentDocument` metadata incl.
  `download_url` (`ParentApiController::childDocuments:328-368`); download URL is
  `route('admin.documents.download', $id)` (`StudentDocument.php:117-124`).

### Missing Features
- **No web portal documents page** (no student-documents route in parent-portal).
- No verification status badge/filter or file preview on the portal.

### Broken Features
- The download link points at the admin `documents.download` route — whether it authorizes a parent
  (non-`student_documents.view` admin) to fetch the file must be verified; the API endpoint itself only
  checks child membership of the given parent (Critical 1 applies to the parent UUID).

---

## 12. Profile

### Existing Features
- API: `PUT parents/{uuid}` (profile) and `PUT parents/{uuid}/change-password`
  (`ParentApiController::updateParentProfile:715`, `changeParentPassword:739`).

### Missing Features
- **No web portal profile / change-password page.**

### Broken Features
- **No ownership check** (Critical 1): both endpoints are gated only by `permission:parents.view`, which
  the Parent role holds → a parent can edit another parent's profile and **change another parent's
  account password** (account takeover).
- `changeParentPassword` does **not revoke existing Sanctum tokens** and does **not enforce
  `force_password_change`** (inconsistent with the teacher-app fix pattern).

---

## Critical Issues

1. **IDOR — Parent API has no object-level ownership check (account takeover / cross-parent data leak).**
   Every `api/v1/parents/{uuid}/...` endpoint resolves the parent by UUID and only then scopes the child
   lookup to that parent's students — it **never verifies `$parent->user_id === auth()->id()`** (or an
   admin role). Gated by `permission:parents.view`, which the Parent role holds
   (`PermissionSeeder.php:116`). A logged-in parent can therefore read any other parent's children,
   attendance, fees, exam results, timetable, homework, calendar, documents and leave, update that
   parent's profile, and reset that parent's password. Contrast: `StudentApiController`,
   `FeeApiController`, `ExamApiController` all do `$user->guardian` + child-membership checks; the parent
   controller does not. (`routes/modules/api/parents.php:10-57`, `ParentApiController.php:68-767`.)

2. **Wrong permission grants expose admin surfaces to the Parent role.**
   The Parent role holds `parents.view` (admin CRUD permission), so a parent can call
   `GET api/v1/parents` (enumerate all parents), `GET api/v1/parents/{uuid}` (any parent's full profile),
   AND the web admin page `/admin/parents`. `ParentController::data()` (`:37-53`) applies no policy and no
   `school_id` filter to the query, so anyone with `parents.view` can list parents across the whole
   system. `parents.view` should not be granted to the Parent role (it is a staff/management permission).

3. **Notifications are effectively broken for the portal.**
   The web portal (dashboard + notifications page) reads the `parent_notifications` table, which is never
   populated (0 records), so portal notifications are always empty while the API circulars (generic
   `notifications` table) work. The `ParentNotification` targeting model is also internally inconsistent
   (null = all vs `whereRaw('1 = 0')`).

---

## Estimated Fix Order

1. **Authorization (Critical 1 + 2):** add a shared ownership/role guard to `ParentApiController`
   (mirror `StudentApiController`'s `$user->guardian` pattern), require that the parent UUID belongs to the
   caller unless an admin/`parents.view` staff role; **remove `parents.view` from the Parent role seed**;
   add `school_id` scoping + policy to `ParentController::data()`.
2. **Password change hardening:** revoke other Sanctum tokens and enforce `force_password_change` in
   `changeParentPassword`.
3. **Notifications:** either write portal notifications into `parent_notifications` (fixing
   `ParentNotification::parents()` "all" case) or point the portal at the generic `notifications` table so
   web and API surfaces agree.
4. **Dashboard/currency correctness:** fix `ParentDashboardBuilder.php:38` key (`active`), unify currency
   to `₹` in `dashboard.blade.php:49` / `fees.blade.php:50-52`.
5. **Web portal feature gaps:** add parent-portal Leave, Calendar, Documents and Profile pages (routes +
   views) to match the API surface.
6. **Query/N+1 cleanup:** move attendance/fees/exam/timetable queries out of blades into the service;
   align academic-year filtering between `attendance.blade.php` and `ParentService`.
7. **Consistency + tests:** add a `tests/Feature/*Parent*` suite (authn/authz, circulars, leave, fees);
   fix `SidebarBuilder::buildForParent()` route names (unprefixed `parent-portal.*` vs actual
   `admin.parent-portal.*`).

---

*This is a read-only review. No code was modified.*