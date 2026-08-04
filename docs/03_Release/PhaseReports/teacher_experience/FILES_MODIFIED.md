# Files Modified - Phase 02: Teacher Experience Refactor

## Legend
- **NEW** = File created during this phase
- **MODIFIED** = Existing file changed during this phase

---

## Dashboard Module

| # | File | Change Type | Description | Module |
|---|------|-------------|-------------|--------|
| 1 | `app/Modules/Dashboard/Services/DataCollectors/TeacherDashboardCollector.php` | **NEW** | Dedicated data collector for teacher dashboard with 6 cached methods: `todayClassesCount()`, `pendingHomeworkCount()`, `upcomingExamsCount()`, `attendancePendingCount()`, `leaveBalance()`, `todaySchedule()`. Each method uses cache keys incorporating teacher ID and school ID for multi-tenant isolation. TTLs: 60s-300s. | Dashboard |
| 2 | `app/Modules/Dashboard/Services/Builders/TeacherDashboardBuilder.php` | **MODIFIED** | Rewritten to use `TeacherDashboardCollector` for data. Removed all Finance, Payroll, Transport, Library, School Analytics queries. Stat cards reduced to 4 (Today's Classes, Pending Homework, Upcoming Exams, Attendance Pending). Widgets: Today's Schedule, Student Attendance, Leave Overview. Quick Actions: Record Attendance, Manage Homework, View Timetable, View Exams, Apply Leave. | Dashboard |
| 3 | `app/Modules/Dashboard/Services/DashboardFactory.php` | **MODIFIED** | Added `'Teacher' => TeacherDashboardBuilder::class` mapping in `ROLE_PRIORITY` constant. When a user with Teacher role accesses the dashboard, the factory returns `TeacherDashboardBuilder`. | Dashboard |
| 4 | `app/Modules/Dashboard/Services/SidebarBuilder.php` | **MODIFIED** | Added `buildForTeacher(User $user)` method returning a programmatic sidebar with only teacher-relevant items: Dashboard, My Timetable, Attendance, Homework, My Students, Marks, Leave, My Documents, My Payslips, Notifications, Calendar, Ask ERP. No Executive Copilot, AI Agents, or Execution History. | Dashboard |

---

## AI Assistant Module

| # | File | Change Type | Description | Module |
|---|------|-------------|-------------|--------|
| 5 | `app/Modules/AiAssistant/Services/AIService.php` | **MODIFIED** | Added `TEACHER_ALLOWED_INTENTS` constant with 8 allowed intents (attendance.absent_today, attendance.monthly_percentage, attendance.below_75, student.total, student.by_class, homework.create, exam.publish, notification.send, school.summary). Added `isTeacherAuthorized()` method that returns `true` for non-teacher roles and checks allowed intents for teachers. Added `scopeToTeacherData()` method that injects `class_section_ids` and `teacher_id` parameters. Modified `ask()` method to call both authorization and scoping. | AiAssistant |

---

## Views (Blade Templates)

| # | File | Change Type | Description | Module |
|---|------|-------------|-------------|--------|
| 6 | `resources/views/layouts/partials/sidebar.blade.php` | **MODIFIED** | Added dedicated teacher sidebar section (lines 14-119) wrapped in `@if(auth()->user()->hasRole('Teacher'))`. Teacher section includes: Dashboard, My Timetable, Attendance, Homework, My Students, Marks, Leave, My Documents, My Payslips, Notifications, Calendar, Ask ERP. Non-teacher section (lines 121+) remains unchanged with full Operations/Academics/Finance/AI Workspace/Administration sections. | Layouts |

---

## Route Files

| # | File | Change Type | Description | Module |
|---|------|-------------|-------------|--------|
| 7 | `routes/modules/leave.php` | **MODIFIED** | Added `my-leaves` route prefix (lines 38-43) for teacher self-service: `GET /my-leaves` -> `LeaveRequestController@myLeaves`, `GET /my-leaves/data` -> `LeaveRequestController@myLeavesData`. No permission middleware - auto-scopes to authenticated user. | Leave |
| 8 | `routes/modules/documents.php` | **MODIFIED** | Added `teacher-documents` route prefix (lines 20-24): `GET /teacher-documents` -> `TeacherDocumentController@index`, `GET /teacher-documents/data` -> `TeacherDocumentController@data`, `GET /teacher-documents/{document}/download` -> `TeacherDocumentController@download`. No permission middleware - ownership is verified in controller. | Documents |
| 9 | `routes/modules/payroll.php` | **MODIFIED** | Added teacher payslip routes (lines 7-8) outside `payroll.view` permission gate: `GET /payroll/payslips/my` -> `PayrollController@myPayslips`, `GET /payroll/my-payslips/data` -> `PayrollController@myPayslipsData`. These routes are accessible without admin payroll permission; scoped to authenticated teacher. | Payroll |

---

## Controllers

| # | File | Change Type | Description | Module |
|---|------|-------------|-------------|--------|
| 10 | `app/Modules/Leave/Controllers/LeaveRequestController.php` | **MODIFIED** | Added `myLeaves()` method returning `modules.leave.requests.my_leaves` view filtered by authenticated user. Added `myLeavesData()` method returning DataTables JSON scoped to `leave_requests.user_id = auth()->id()`. Modified `data()` method with teacher check: if Teacher role, auto-adds `where('leave_requests.user_id', auth()->id())` filter. | Leave |
| 11 | `app/Modules/Payroll/Controllers/PayrollController.php` | **MODIFIED** | Added `myPayslips()` method returning `modules.payroll.my_payslips` view with teacher data. Added `myPayslipsData()` method scoping payslips to `employee_type = 'teacher'` and `employee_id = $teacher->id` where teacher is resolved from `user_id`. | Payroll |
| 12 | `app/Modules/Documents/Controllers/TeacherDocumentController.php` | **NEW** | New controller for teacher self-service documents. `index()` loads teacher's documents via `$teacher->documents()->latest()->get()`. `data()` returns DataTables JSON of teacher's documents. `download()` verifies document ownership before serving file. | Documents |

---

## Policy Files (Pre-existing, unchanged during Phase 02 but integral to teacher scoping)

These policy files were already in place and are referenced by the refactored code:

| # | File | Description |
|---|------|-------------|
| P1 | `app/Modules/Attendance/Policies/AttendancePolicy.php` | Teacher-scoped: view/create/update/delete check teacher's `classSections->pluck('id')` |
| P2 | `app/Modules/Homework/Policies/HomeworkPolicy.php` | Teacher-scoped: view checks `class_section_id`, update/delete check `created_by === $user->id` |
| P3 | `app/Modules/Exams/Policies/ExamPolicy.php` | Teacher-scoped: view/create/update check `class_section_id`, delete/publish return false for Teacher |
| P4 | `app/Modules/Leave/Policies/LeaveRequestPolicy.php` | Teacher-scoped: view checks `user_id`, delete checks `user_id + pending`, approve returns false |
| P5 | `app/Modules/Students/Policies/StudentPolicy.php` | Teacher-scoped: view/update/delete check student session `class_section_id` in teacher's assigned IDs |
| P6 | `app/Modules/Documents/Policies/TeacherDocumentPolicy.php` | Teacher-scoped: view checks `document.teacher_id === teacher.id` |
| P7 | `app/Modules/Payroll/Policies/PayrollPolicy.php` | Contains `view_own` method for self-service payslip access |
| P8 | `app/Modules/Teachers/Policies/TeacherPolicy.php` | Permission-gated; teachers cannot view/update/delete other teachers |

---

## Summary

| Category | New | Modified | Total |
|----------|-----|----------|-------|
| Dashboard Module | 1 | 2 | 3 |
| AI Assistant Module | 0 | 1 | 1 |
| Views | 0 | 1 | 1 |
| Routes | 0 | 3 | 3 |
| Controllers | 1 | 2 | 3 |
| Policies | 0 | 0 | 0 (pre-existing) |
| **Total** | **2** | **9** | **11** |
