# Policy Report – Phase 06: Parent Student Portal

## New Policies
None. No new authorization policies were introduced in Phase 06.

## Existing Policies Applied
| Policy | Application |
|--------|-------------|
| `StudentPolicy` | Implicit — `StudentDashboardBuilder` scopes data by `user_id` on the `Student` model. |
| `ParentPolicy` | Implicit — `ParentDashboardBuilder` scopes data via `Guardian::where('user_id', $this->user->getKey())`. |
| `AttendancePolicy` | Attendance queries are scoped by `student_id` (student) or delegated to `ParentService` (parent). |
| `ExamPolicy` | Exam queries are scoped by `class_section_id` from active sessions. |
| `HomeworkPolicy` | Homework queries are scoped by `classSection` relationship from active sessions. |
| Permission gates (`@can`) | Sidebar items in `sidebar.blade.php` are wrapped in `@can('dashboard.view')`, `@can('attendance.view')`, `@can('fees.view')`, `@can('exams.view')`, `@can('timetable.view')`, `@can('homework.view')`, `@can('notifications.view')`. |

## Notes
- The `@can` directives in the sidebar blade are the primary enforcement layer for UI visibility.
- Backend authorization relies on existing model-scoped queries — no new policy classes were required.
