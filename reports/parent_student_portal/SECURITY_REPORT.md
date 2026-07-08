# Security Report – Phase 06: Parent Student Portal

## Data Isolation

### Student
- All queries in `StudentDashboardBuilder` are scoped by `Student::where('user_id', $this->user->getKey())`.
- Attendance, homework, and exam queries are further scoped by the resolved `$student->id` or `$student->sessions()->pluck('class_section_id')`.
- **Result**: A student can only see their own data. No cross-student data leakage.

### Parent
- All queries in `ParentDashboardBuilder` are scoped by `Guardian::where('user_id', $this->user->getKey())`.
- Dashboard data is aggregated via `ParentService::getParentDashboardData()` which internally scopes to the guardian's children.
- **Result**: A parent can only see data for their own children. No cross-family data leakage.

## Authorization (Sidebar Gating)
Sidebar items in `sidebar.blade.php` are wrapped in `@can` directives:

| Permission | Role(s) |
|------------|---------|
| `dashboard.view` | Parent, Student |
| `attendance.view` | Parent, Student |
| `fees.view` | Parent (only) |
| `exams.view` | Parent, Student |
| `timetable.view` | Parent, Student |
| `homework.view` | Parent (only) |
| `notifications.view` | Parent (only) |

Student sidebar has **no** `fees.view`, `homework.view`, or `notifications.view` items — they simply aren't rendered in the `@elseif` block.

## Role Separation
- `SidebarBuilder::build()` checks roles in a priority order: Teacher → Principal → HR → **Parent** → **Student** → fallback.
- `DashboardFactory::make()` checks roles in the same priority.
- A user with multiple roles will always resolve to the first matching role in the priority list.

## Conclusion
Phase 06 maintains proper data isolation and authorization. No security regressions introduced.
