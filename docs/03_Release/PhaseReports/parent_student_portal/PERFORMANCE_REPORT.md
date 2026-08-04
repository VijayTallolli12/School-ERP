# Performance Report – Phase 06: Parent Student Portal

## Query Analysis

### StudentDashboardBuilder
- **Attendance**: 2 queries — `Attendance::count()` (total days) and `Attendance::whereIn()` (present days), both scoped by `student_id`.
- **Homework**: 1 query via `whereHas` with a subquery on `sessions()->pluck()` — the pluck is executed as a separate query, then used in a `whereIn`.
- **Upcoming Exams**: 1 query — same pattern as homework, filtered by `exam_date >= now()`.
- **Active Sessions**: 1 query — `sessions()->where('status', 'active')->count()`.
- **Total**: ~5-6 simple queries per page load, all indexed by foreign keys (`student_id`, `user_id`).

### ParentDashboardBuilder
- **Guardian lookup**: 1 query.
- **Dashboard data**: Delegated to `ParentService::getParentDashboardData()` which uses cached/aggregated data.
- **Quick actions**: No queries — they only build route arrays.
- **Total**: Minimal query impact; reuses existing service layer caching.

## N+1 Concerns
- **None introduced.** All queries use `count()`, `pluck()`, or `whereHas()` — no lazy-loaded loops.
- Student homework/exam queries use a single `whereIn` with a pre-resolved id list, avoiding nested loop queries.

## Conclusion
Performance impact is negligible. Phase 06 adds no more than 6 simple SELECT queries for the student dashboard and reuses cached data for the parent dashboard.
