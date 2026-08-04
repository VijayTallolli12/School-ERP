# Database Review

Database score: 82 / 100 for schema design, 68 / 100 for current deployed state.

## Evidence

- `php artisan migrate:status` shows most migrations ran, but four are pending.
- Schema includes schools, users, roles/permissions, academic structure, students, guardians, attendance, fees, teachers, exams, parents, notifications, homework, leave, calendar, documents, transport, library, payroll, trips, HR, AI query logs and activity logs.
- Many migrations define foreign keys, unique constraints, indexes and soft deletes.

## Strengths

- Strong multi-school modeling with `school_id` across many tables.
- Frequent use of `foreignId()->constrained()`.
- Good use of unique constraints such as `school_id + code`, `school_id + admission_no`, and fee structure uniqueness.
- Soft deletes are widely used for business entities.
- Performance indexes were added for fees, exams, homework, teacher attendance and transport search.

## Issues

| Severity | Issue | Evidence | Recommendation |
|---|---|---|---|
| Critical | Pending migrations. | Four migrations pending in `migrate:status`. | Apply and verify migrations before release. |
| High | HR code exists but HR tables are pending. | `app/Modules/HR` exists; `2026_07_07_000001_create_hr_tables` pending. | Block HR release until schema is live. |
| High | Exam enhancement code exists but enhanced tables are pending. | GradeScale/ExamSchedule/ExamMark code exists; migration pending. | Block enhanced exams/results release until schema is live. |
| Medium | SQLite current DB differs from common production MySQL/PostgreSQL behavior. | `php artisan about`: Database sqlite. | Test migrations and queries against production database engine. |
| Medium | Raw MySQL-specific SQL appears in reports. | `DATE_FORMAT`, `GROUP_CONCAT ... SEPARATOR`, and concatenation raw SQL in report repositories. | Verify portability or declare MySQL as required. |

## Recommendations

1. Run `php artisan migrate --force` in staging and validate all module pages.
2. Add migration status to CI/CD.
3. Add foreign-key/index audit for all school-scoped tables.
4. Add data integrity tests for admission -> student -> parent -> fee -> attendance -> exam -> result workflows.
5. Confirm production database engine and remove SQLite-only assumptions.

