# Security Report — Phase 05: Academic Exam Workflow

## Data Isolation

| Table | Isolation Mechanism |
|-------|---------------------|
| `grade_scales` | `school_id` foreign key with `cascadeOnDelete` |
| `exam_schedules` | `school_id` foreign key with `cascadeOnDelete` |
| `exam_marks` | `school_id` foreign key with `cascadeOnDelete` |

All three new models use the `BelongsToSchool` trait which automatically scopes queries to the current school context.

## Permission Gates

### Route-level (Middleware)
All new route groups are protected by the `permission:exams.view` middleware. Mutating routes additionally require:
- `permission:exams.create` — for create operations
- `permission:exams.update` — for update operations
- `permission:exams.delete` — for delete operations

### Policy-level
Three new policies gate CRUD at the controller level:

| Policy | Gates |
|--------|-------|
| `GradeScalePolicy` | `exams.view`, `exams.create`, `exams.update`, `exams.delete` |
| `ExamSchedulePolicy` | `exams.view`, `exams.create`, `exams.update`, `exams.delete` |
| `ExamMarkPolicy` | `exams.view`, `exams.create`, `exams.update`, `exams.delete` |

### Super Admin Override
`AppServiceProvider` registers a `Gate::before` hook granting Super Admin access to all abilities.

## Soft Deletes

- `exam_schedules` and `exam_marks` use `SoftDeletes` — records are marked as deleted rather than permanently removed.
- This provides a safety net for accidental deletions and enables data restoration.

## Foreign Key Constraints

| Constraint | Referential Action |
|------------|-------------------|
| `grade_scales.school_id` → `schools.id` | CASCADE ON DELETE |
| `exam_schedules.school_id` → `schools.id` | CASCADE ON DELETE |
| `exam_schedules.exam_id` → `exams.id` | CASCADE ON DELETE |
| `exam_schedules.subject_id` → `subjects.id` | CASCADE ON DELETE |
| `exam_marks.school_id` → `schools.id` | CASCADE ON DELETE |
| `exam_marks.exam_schedule_id` → `exam_schedules.id` | CASCADE ON DELETE |
| `exam_marks.student_id` → `students.id` | CASCADE ON DELETE |

## Audit Trail

- All tables include `created_by` and `updated_by` nullable columns tracking the authenticated user who created/updated each record.

## Unique Constraints

- `exam_marks` has a unique constraint on `(exam_schedule_id, student_id)` preventing duplicate mark entries for the same student in a schedule.
