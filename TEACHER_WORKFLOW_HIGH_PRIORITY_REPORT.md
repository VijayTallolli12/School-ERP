# TEACHER WORKFLOW — HIGH PRIORITY FIXES REPORT

Status: **Completed**
Scope: Only the high-priority fixes from `TEACHER_WORKFLOW_REVIEW.md`. Medium/low-priority items, Student Promotion/Transfer/TC, and Workflow 1 were **not** modified.

## Files Modified

| File | Change |
| --- | --- |
| `app/Http/Controllers/Api/V1/TeacherAppController.php` | HomeworkStore null-year fix; ownership validation (attendance/exam-marks/leave/subject); login `SchoolContext::set()`; teacher guards on logout/notifications/changePassword; token revocation on password change. |
| `routes/modules/api/teacher-app.php` | Added `role:Teacher` middleware to the group. |
| `app/Modules/Attendance/Services/AttendanceService.php` | `markAttendance()` now honours the passed `marked_by`. |

No other files were modified.

## Fixes Implemented

### 1. TeacherAppController

**homeworkStore — null `academic_year_id` (removed 500)**
- Previously wrote `$academicYear?->id` (possibly `null`) into the NOT NULL `homework.academic_year_id` column → SQL 500.
- Now returns `422 "No active academic year found."` when no active year exists, and only then sets `academic_year_id = $academicYear->id`.

**Ownership validation**
- `markAttendance`: verifies every submitted `student_id` is enrolled (active session) in the submitted `class_section_id` before marking; returns 403 otherwise.
- `examsStoreMarks`: verifies every `results.*.student_id` is enrolled in the exam's `class_section_id`; returns 403 otherwise.
- `leaveStore`: verifies the `student_id` is enrolled in one of the teacher's class sections; returns 403 otherwise.
- `homeworkStore`: verifies the `subject_id` is one the teacher actually teaches (was previously any subject by existence only).

**login — `SchoolContext::set()`**
- Previously set only the permission team id and resolved the school from the user (ignoring the teacher profile); never set `SchoolContext`.
- Now prefers `teacher->school_id` (falling back to `current_school_id`, then first school link) and calls `SchoolContext::set($schoolId)` so the rest of the request runs in the correct school context.

**logout()**
- Added teacher guard via `resolveTeacher()` so the endpoint is not callable by non-teacher roles.

**notifications()**
- Added `resolveTeacher()` guard to `notificationsIndex`, `notificationsRead`, `notificationsReadAll`.

**changePassword()**
- Added teacher guard via `resolveTeacher()`.
- After changing the password, revokes all other access tokens for the user (current token survives so the response can still be completed).

### 2. routes/modules/api/teacher-app.php
- Applied `->middleware('role:Teacher')` to the `teacher` route group (alias registered in `bootstrap/app.php`), gating all teacher-app endpoints to the `Teacher` role. Login remains public on its own route.

### 3. AttendanceService
- `markAttendance()`: `marked_by` now uses `$data['marked_by'] ?? auth()->id()`, honouring the caller-passed value instead of silently discarding it.

## Design Note (ownership check scope)
The enrollment checks scope by **class section** (not academic year) to match the module's existing behavior (`attendanceStudents`) and to avoid a regression against fixtures that contain more than one active academic year (where a session's year may differ from the year `currentAcademicYear()` resolves). This still prevents the reported cross-class data-integrity bug (marking attendance / marks / leave for students outside the teacher's classes).

## Tests Run (relevant only)

| Command | Result |
| --- | --- |
| `php artisan test tests/Feature/TeacherAppApiTest.php` | **11 passed** (37 assertions) |
| `php artisan test tests/Feature/LiveAttendanceTest.php` | **9 passed** (excl. debug removed) |
| `php artisan test tests/Feature/LiveTransportTest.php` | **17 passed** |

Combined: **37 passed** across the three teacher/attendance-related suites. `LiveAttendanceTest` and `LiveTransportTest` exercise teacher login + attendance marking (directly relevant to the changed code paths).

## Notes
- No migration required.
- Debug scaffolding created during investigation was removed; no `DEBUG`/`fwrite` remnants remain in the modified files.