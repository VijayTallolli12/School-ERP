# Regression Report — Phase 05: Academic Exam Workflow

## Test Cases

| # | Test Case | Result |
|---|-----------|--------|
| 1 | **Grade Scale CRUD** — Create, read, update, and delete grade scales via API and UI. | PASS |
| 2 | **Exam Schedule CRUD** — Create, read, update, and delete exam schedules nested under an exam. | PASS |
| 3 | **Exam Mark Bulk Entry** — Submit marks for multiple students against an exam schedule; verify upsert behaviour. | PASS |
| 4 | **Grade Calculation (DB scales)** — Configure school-specific grade scales; verify marks auto-assign correct grade and grade_point. | PASS |
| 5 | **Grade Calculation (fallback)** — With no DB scales configured for the school; verify hardcoded defaults (`A+` through `F`) are used. | PASS |
| 6 | **Teacher Exam Permissions** — Teacher role can create (`exams.create`) and update (`exams.update`) exams for own class sections. | PASS |
| 7 | **Existing Exam CRUD** — Previously working exam create/read/update/delete/publish/results flows remain unaffected. | PASS |
| 8 | **Route Authorisation** — All new routes gated by `permission:exams.*` middleware; unauthenticated/unauthorised requests receive 403. | PASS |
| 9 | **Policy Authorisation** — `GradeScalePolicy`, `ExamSchedulePolicy`, `ExamMarkPolicy` gate against `exams.view`, `exams.create`, `exams.update`, `exams.delete`. | PASS |
| 10 | **Soft Delete Cascade** — Deleting an exam schedule soft-deletes associated exam marks; restoring the schedule restores marks. | PASS |
| 11 | **Data Isolation** — Data from different schools is properly isolated via `school_id` on all new tables. | PASS |

**Overall Result: ALL PASS**
