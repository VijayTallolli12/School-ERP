# Business Rule Report — Phase 05: Academic Exam Workflow

## Grade Scales

| Rule | Details |
|------|---------|
| **School-scoped** | Grade scales are isolated by `school_id`. Each school manages its own grading configuration. |
| **Active-only** | Only scales with `status = 'active'` are used for grade calculation. |
| **Ordered evaluation** | Scales are evaluated by `sort_order` ascending, then `min_percentage` descending. The first matching range wins. |
| **Fallback defaults** | If no DB scales exist for the school, hardcoded defaults apply: `A+` (≥90%), `A` (≥80%), `B+` (≥70%), `B` (≥60%), `C+` (≥50%), `C` (≥40%), `D` (≥33%, fail), `F` (<33%, fail). |
| **Fail indicator** | The `is_fail` boolean on a scale determines whether the grade is considered a failing grade. |

## Exam Scheduling

| Rule | Details |
|------|---------|
| **Nested under exam** | Schedules belong to an exam via `exam_id`. An exam can have multiple subject schedules. |
| **Pass marks ≤ max marks** | Validated at controller level: `pass_marks` must be `lte:maximum_marks`. |
| **Date & time** | Each schedule has an `exam_date`, optional `start_time`/`end_time`, and optional `room`. |
| **Soft deletes** | Deleting a schedule soft-deletes it; associated marks are cascade soft-deleted. |

## Mark Entry

| Rule | Details |
|------|---------|
| **Status values** | `pass` — marks ≥ pass_marks; `fail` — marks < pass_marks; `absent` — student was absent (no grade calculated); `pending` — marks not yet entered (grade_point = null). |
| **Auto-grade calculation** | When marks are saved via `saveMarkWithGrade()`, the percentage is computed as `(marks_obtained / maximum_marks) × 100` and passed to `GradingService`. |
| **Upsert behaviour** | If a mark record already exists for the `(exam_schedule_id, student_id)` pair, it is updated; otherwise created. |
| **Absent handling** | When `absent=true`, marks_obtained is stored as null, status is `absent`, grade is null, grade_point is null, and is_fail is true. |
| **Unique constraint** | DB enforces uniqueness on `(exam_schedule_id, student_id)`. |

## Teacher Permissions

| Rule | Details |
|------|---------|
| **Exam creation/update** | Teacher role now has `exams.create` and `exams.update` permissions (in addition to existing `exams.view` and `exams.reports`). |
| **Scope** | Teacher is expected to create/update exams for their own assigned class sections (enforced at application layer if needed). |

## Data Integrity

| Rule | Details |
|------|---------|
| **School isolation** | All 3 new tables include `school_id` with a foreign key constraint cascading on delete. |
| **Audit trail** | All tables track `created_by` and `updated_by` (nullable unsigned big integers). |
| **Soft deletes** | `exam_schedules` and `exam_marks` use `SoftDeletes` for safe removal and restoration. |
