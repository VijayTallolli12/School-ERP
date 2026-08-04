# Searchable Dropdown Audit

## Overview
All converted `<select>` elements using Select2 with AJAX (large datasets) or client-side search (small datasets). See `TRANSPORT_UX_REFACTOR.md` for the broader transport assignment workflow plan.

---

## Transport — Assignment Modal (`resources/views/modules/transport/index.blade.php`)

| Select | Selector | Strategy | Endpoint | Search columns | Auth |
|---|---|---|---|---|---|
| Student | `#assignmentStudentId` | AJAX (≥2 chars) | `route('admin.transport.search.students')` | `first_name`, `middle_name`, `last_name`, `admission_no` | `transport.view` |
| Route | `#assignmentRouteId` | Client-side | — (all routes pre-loaded as JSON via `ROUTES`) | `route_name`, `start_point`, `end_point` | — |
| Stop | `#assignmentStopId` | Client-side (dynamic) | — (rebuilt from `ROUTE_STOPS` on route change) | `stop_name` | — |

### Performance notes
- Route dataset is small (≤50 per school), client-side search is appropriate.
- Stop dataset is per-route (≤20 per route), client-side is fine.
- Student data (500–5000+) requires AJAX; endpoint capped at 20 results.

---

## Fees — Assign Modal (`resources/views/modules/fees/index.blade.php`)

| Select | Selector | Strategy | Endpoint | Search columns | Auth |
|---|---|---|---|---|---|
| Student | `#feeAssignStudentId` | AJAX (≥2 chars) | `route('admin.students.search')` | `first_name`, `middle_name`, `last_name`, `admission_no` | `students.view` |

### Performance notes
- Student dataset requires AJAX; endpoint shared with other modules.

---

## Fees — Collect Modal (`resources/views/modules/fees/index.blade.php`)

| Select | Selector | Strategy | Endpoint | Search columns | Auth |
|---|---|---|---|---|---|
| Student | `#collectStudentId` | AJAX (≥2 chars) | `route('admin.students.search')` | `first_name`, `middle_name`, `last_name`, `admission_no` | `students.view` |

### Performance notes
- Same student search endpoint as assign modal.

---

## Attendance — Mark Modal (`resources/views/modules/attendance/index.blade.php`)

| Select | Selector | Strategy | Endpoint | Search columns | Auth |
|---|---|---|---|---|---|
| Student | `#attendanceStudentId` | Client-side (pre-loaded by class section) | — (loaded via `loadMarkStudents()`) | — | — |

### Performance notes
- Students are loaded per class-section (≤60), client-side is fine.
- Select2 is destroyed/re-initialised in `loadMarkStudents()` and `resetMarkForm()` to handle `innerHTML` DOM replacement.
- Edit flow sets value via `$('#attendanceStudentId').val(...).trigger('change')` after re-init.

---

## Server-Side Search Endpoints

| Endpoint name | Class::method | Route | Key query |
|---|---|---|---|
| `students.search` | `StudentController::search()` | `GET /admin/students/search` | `WHERE first_name LIKE '%q%' OR middle_name LIKE '%q%' OR last_name LIKE '%q%' OR admission_no LIKE '%q%'` |
| `teachers.search` | `TeacherController::search()` | `GET /admin/teachers/search` | `WHERE first_name LIKE '%q%' OR middle_name LIKE '%q%' OR last_name LIKE '%q%' OR employee_id LIKE '%q%'` |
| `transport.search.students` | `TransportController::searchStudents()` | `GET /admin/transport/search/students` | Same as `students.search` but under `transport.view` permission |
| `transport.search.routes` | `TransportController::searchRoutes()` | `GET /admin/transport/search/routes` | `WHERE route_name LIKE '%q%' OR start_point LIKE '%q%' OR end_point LIKE '%q%'` |

### Query optimisations
- All queries are scoped by `school_id` via the `BelongsToSchool` global scope → index `[school_id, first_name, last_name]` on `students` is used for school filtering.
- `students` has a composite index on `[school_id, first_name, last_name]`.
- `students.admission_no` part of unique index `[school_id, admission_no]`.
- `teachers.employee_id` part of unique index `[school_id, employee_id]`.
- `LIKE '%term%'` with leading wildcard cannot use B-tree indexes efficiently; FULLTEXT indexes were added (`idx_students_name_fulltext`, `idx_teachers_name_fulltext`) for future query migration to `MATCH ... AGAINST`.
- Route search indexes added: `idx_routes_school_name`, `idx_routes_school_start`, `idx_routes_school_end`.
- Endpoints cap response at 20 results (`limit` param, max 50).

### SQL injection / input safety
- Input is passed directly to `LIKE` with `%` wrapping. No raw `DB::raw()` — uses Eloquent parameter binding.

---

## Select2 Configuration (global)

| Setting | Value | Rationale |
|---|---|---|
| `minimumInputLength` | 2 | Avoid overwhelming DB with single-character searches |
| `maximumSelectionLength` | 1 | Single-select dropdowns only |
| `dropdownParent` | auto-detected (closest `.modal`) | Fixes z-index rendering inside Bootstrap modals |
| `theme` | `bootstrap-5` | Matches `select2-bootstrap-5-theme` |
| `width` | `100%` | Responsive |
| `delay` | 250ms (AJAX) | Debounce keystrokes |
| `cache` | `true` (AJAX) | Avoids duplicate requests for same query |

---

## Database Indexes Migration

**Migration:** `2026_06_18_000002_add_search_indexes.php`

| Table | Index name | Columns | Type |
|---|---|---|---|
| `routes` | `idx_routes_school_name` | `school_id`, `route_name` | B-tree |
| `routes` | `idx_routes_school_start` | `school_id`, `start_point` | B-tree |
| `routes` | `idx_routes_school_end` | `school_id`, `end_point` | B-tree |
| `students` | `idx_students_name_fulltext` | `first_name`, `middle_name`, `last_name` | FULLTEXT |
| `teachers` | `idx_teachers_name_fulltext` | `first_name`, `middle_name`, `last_name` | FULLTEXT |
