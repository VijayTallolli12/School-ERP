# Performance Report — Phase 05: Academic Exam Workflow

## Caching

- **GradingService** (`GradingService.php:12`) caches grade scales per school for **3600 seconds** using `Cache::remember("grade_scales.{$schoolId}", 3600, ...)`.
- Cache key is scoped by `school_id`, ensuring isolation across tenants.
- Cache is invalidated implicitly via TTL; no manual flush required for infrequently changed grade scale data.

## Query Optimisation

- **No N+1 issues.** ExamSchedule DataTables query uses eager loading (`->with(['subject'])` at `ExamScheduleController.php:28`).
- ExamMark DataTables query uses `->with(['student'])` (`ExamMarkController.php:33`).
- Grade scale lookup is a single indexed query (`school_id + status` composite index exists on `grade_scales` table).
- Exam marks use a unique index on `(exam_schedule_id, student_id)` for efficient upsert lookups.

## Indexes

| Table | Indexes |
|-------|---------|
| `grade_scales` | `(school_id, status)`, primary key |
| `exam_schedules` | `(exam_id, subject_id)`, `(school_id, exam_id)`, primary key |
| `exam_marks` | UNIQUE `(exam_schedule_id, student_id)`, `(exam_schedule_id, student_id)` composite, `(school_id, exam_schedule_id)`, `status` |

## Impact Assessment

- The 3 new tables add minimal query overhead to the existing exam module.
- Grade scale cache hit serves subsequent requests from memory with zero DB queries.
- Soft deletes on `exam_schedules` and `exam_marks` add a `WHERE deleted_at IS NULL` clause — indexed columns ensure no scan penalty.
- Overall performance impact: **negligible**.
