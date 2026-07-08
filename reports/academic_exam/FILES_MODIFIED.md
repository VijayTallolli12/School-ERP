# Files Modified — Phase 05: Academic Exam Workflow

## New Files

| # | File | Description |
|---|------|-------------|
| 1 | `app/Modules/Exams/Models/GradeScale.php` | Eloquent model for `grade_scales` table; uses `BelongsToSchool`, `HasFactory`. |
| 2 | `app/Modules/Exams/Models/ExamSchedule.php` | Eloquent model for `exam_schedules` table; belongs to `Exam` and `Subject`, has many `ExamMark`. Uses `SoftDeletes`. |
| 3 | `app/Modules/Exams/Models/ExamMark.php` | Eloquent model for `exam_marks` table; belongs to `ExamSchedule` and `Student`. Uses `SoftDeletes`. |
| 4 | `app/Modules/Exams/Controllers/GradeScaleController.php` | CRUD controller for grade scales with DataTables, validation, and policy authorisation. |
| 5 | `app/Modules/Exams/Controllers/ExamScheduleController.php` | CRUD controller for exam schedules nested under `Exam`; authorises via exam policy. |
| 6 | `app/Modules/Exams/Controllers/ExamMarkController.php` | Controller for mark entry (bulk save + single update); injects `ExamService`. |
| 7 | `app/Modules/Exams/Services/GradingService.php` | Grade calculation service with DB-backed scales (cached) and hardcoded fallback. |
| 8 | `app/Modules/Exams/Policies/GradeScalePolicy.php` | Policy gating CRUD on grade scales via `exams.view`, `exams.create`, `exams.update`, `exams.delete`. |
| 9 | `app/Modules/Exams/Policies/ExamSchedulePolicy.php` | Policy gating CRUD on exam schedules via `exams.*` permissions. |
| 10 | `app/Modules/Exams/Policies/ExamMarkPolicy.php` | Policy gating CRUD on exam marks via `exams.*` permissions. |
| 11 | `database/migrations/2026_07_07_000002_create_exam_enhancement_tables.php` | Migration creating `grade_scales`, `exam_schedules`, `exam_marks` tables with foreign keys, indexes, and soft deletes. |

## Modified Files

| # | File | Description |
|---|------|-------------|
| 12 | `database/seeders/PermissionSeeder.php` | Added `exams.create` and `exams.update` to the Teacher role permissions array. |
| 13 | `routes/modules/exams.php` | Added three route groups: `grade-scales/*`, `exams/{exam}/schedules/*`, `exam-schedules/{schedule}/marks/*`. |
| 14 | `app/Providers/AppServiceProvider.php` | Registered `GradeScalePolicy`, `ExamSchedulePolicy`, `ExamMarkPolicy` via `Gate::policy()` calls. |
| 15 | `app/Modules/Exams/Services/ExamService.php` | Added `saveMarkWithGrade()` method that delegates grade calculation to `GradingService` and upserts `ExamMark` records. |
