# Performance Report — Phase 04 (HR Payroll Workflow)

## Caching Strategy

### HR Dashboard Collector (`HRCollector.php`)
The `HRCollector` class uses Laravel's `Cache::remember()` with a TTL of **300 seconds** (5 minutes) for all dashboard metrics.

| Method | Cache Key | TTL | Query |
|--------|-----------|-----|-------|
| `totalEmployeeCount()` | `dashboard.hr.total.{schoolId}` | 300s | `SELECT COUNT(*) FROM employees` |
| `activeEmployeeCount()` | `dashboard.hr.active.{schoolId}` | 300s | `SELECT COUNT(*) FROM employees WHERE employment_status = 'active'` |
| `newHiresThisMonth()` | `dashboard.hr.new_hires.{schoolId}` | 300s | `SELECT COUNT(*) FROM employees WHERE employment_status = 'active' AND MONTH(date_of_joining) = ? AND YEAR(date_of_joining) = ?` |
| `contractsExpiringSoon()` | `dashboard.hr.expiring_contracts.{schoolId}` | 300s | `SELECT COUNT(*) FROM employee_contracts WHERE status = 'active' AND end_date BETWEEN ? AND ?` |
| `summary()` | `dashboard.hr.summary.{schoolId}` | 300s | Aggregates all 4 methods above |

### Dashboard Caching Impact
- Dashboard page load fetches from cache (no DB query on cache hit)
- 300-second TTL balances data freshness with performance
- Cache is per-school (key includes `{schoolId}`), preventing cross-tenant data leaks

## Query Performance

### Employee DataTable (`EmployeeController::data()`)
- Uses `DataTables::of($this->employees->query())`
- Repository query uses eager loading: `with(['department', 'designation'])`
- Avoids N+1 problem on department and designation name lookups
- Columns: `full_name` (computed accessor), `department.name`, `designation.name`, rendered actions

### Employee Show (`EmployeeController::show()`)
- Eager loads 5 relationships: `department`, `designation`, `reportingTo`, `contracts`, `documents`
- Single query with joins vs. multiple individual queries

### Employee Document DataTable (`EmployeeDocumentController::data()`)
- Eager loads `employee` and `verifier` relationships
- Status badge computed in PHP (no additional DB calls)

## Indexing Considerations
- `employee_code` has a `UNIQUE` index (but not scoped by school — may need a composite unique index `(school_id, employee_code)` in future)
- All `school_id` foreign keys are indexed (Laravel `constrained()` creates FK indexes)
- `softDeletes()` adds `deleted_at` nullable column; queries may benefit from partial indexes on `WHERE deleted_at IS NULL` for large datasets

## Overall Impact
- **Minimal.** HR module queries are limited to:
  - Dashboard: 4 cached queries per 5 minutes
  - Employee listing: 1 query with eager loads
  - Employee CRUD: Single row operations
  - Document listing: 1 query with eager loads
- No heavy aggregation, full-text search, or reporting queries in this phase
- No additional database load beyond normal CRUD operations
