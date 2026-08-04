# Implementation Report — Phase 05: Academic Exam Workflow

## Phase
Academic Exam Workflow (Phase 05)

## Objective
Enhance exam management with configurable grading, exam scheduling, and subject-wise mark entry.

## New Files

| File | Description |
|------|-------------|
| `app/Modules/Exams/Models/GradeScale.php` | Model for configurable grade scales (school-scoped). |
| `app/Modules/Exams/Models/ExamSchedule.php` | Model mapping subjects to dates/times within an exam. Uses `SoftDeletes`. |
| `app/Modules/Exams/Models/ExamMark.php` | Model for individual student marks per exam schedule. Uses `SoftDeletes`. |
| `app/Modules/Exams/Controllers/GradeScaleController.php` | CRUD controller for grade scales with DataTables support. |
| `app/Modules/Exams/Controllers/ExamScheduleController.php` | CRUD controller for exam schedules nested under exams. |
| `app/Modules/Exams/Controllers/ExamMarkController.php` | Controller for mark entry (bulk save, individual update). |
| `app/Modules/Exams/Services/GradingService.php` | Service to calculate grade/grade_point from percentage using DB scales or hardcoded defaults. |
| `app/Modules/Exams/Policies/GradeScalePolicy.php` | Policy gated by `exams.*` permissions. |
| `app/Modules/Exams/Policies/ExamSchedulePolicy.php` | Policy gated by `exams.*` permissions. |
| `app/Modules/Exams/Policies/ExamMarkPolicy.php` | Policy gated by `exams.*` permissions. |
| `database/migrations/2026_07_07_000002_create_exam_enhancement_tables.php` | Migration creating `grade_scales`, `exam_schedules`, `exam_marks` tables. |

## Files Modified

| File | Change |
|------|--------|
| `database/seeders/PermissionSeeder.php` | Teacher role granted `exams.create` and `exams.update`. |
| `routes/modules/exams.php` | Added three route groups: grade-scales, exam schedules, exam marks. |
| `app/Providers/AppServiceProvider.php` | Registered `GradeScalePolicy`, `ExamSchedulePolicy`, `ExamMarkPolicy` via `Gate::policy`. |
| `app/Modules/Exams/Services/ExamService.php` | Added `saveMarkWithGrade()` method that uses `GradingService` for automatic grade calculation. |

## Database Changes

| Table | Key Columns |
|-------|------------|
| `grade_scales` | `school_id`, `grade`, `min_percentage`, `max_percentage`, `grade_point`, `is_fail`, `sort_order`, `status` |
| `exam_schedules` | `school_id`, `exam_id`, `subject_id`, `exam_date`, `start_time`, `end_time`, `room`, `maximum_marks`, `pass_marks`, `sort_order`, `soft_deletes` |
| `exam_marks` | `school_id`, `exam_schedule_id`, `student_id`, `marks_obtained`, `grade`, `grade_point`, `status`, `remarks`, `soft_deletes` |

## Architecture Decisions

- **GradingService** looks up DB grade scales per school (cached 3600s). Falls back to hardcoded defaults when no scales are configured.
- **Mark entry** (`saveMarkWithGrade`) follows the existing bulk-save pattern: upsert by `(exam_schedule_id, student_id)`, auto-calculates grade from percentage.
- **Status values** for exam marks: `pass`, `fail`, `absent`, `pending`.
- All new models use the `BelongsToSchool` trait for multi-tenant data isolation.
- Exam schedules and marks use `SoftDeletes` for safe removal.
