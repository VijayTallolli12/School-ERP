# Performance Report — Supporting Roles (Phase 07)

## Query Analysis

### Accountant Dashboard
| Query | Type | Count |
|-------|------|-------|
| `FeePayment::whereDate('payment_date', today())->sum('amount')` | Aggregate (SUM) with date filter | 1 query |
| `StudentFee::where('status', 'pending')->count()` | Aggregate (COUNT) with status filter | 1 query |
| `StudentFee::where('status', 'overdue')->count()` | Aggregate (COUNT) with status filter | 1 query |
| `FeeCollector::dashboardStats($schoolId)` | Uses pre-existing FeeCollector with caching where available | 1 query |

**Total: 4 queries** — all simple counting/aggregation. No N+1 risk.

### Librarian Dashboard
| Query | Type | Count |
|-------|------|-------|
| `Book::count()` | Aggregate (COUNT) full table | 1 query |
| `BookIssue::whereNull('returned_at')->count()` | Aggregate (COUNT) with null filter | 1 query |
| `BookIssue::whereNull('returned_at')->where('due_date', '<', now())->count()` | Aggregate (COUNT) with null + date filters | 1 query |

**Total: 3 queries** — all simple counting. Available Books computed in-memory from Total − Issued.

### Receptionist Dashboard
| Query | Type | Count |
|-------|------|-------|
| `Student::count()` | Aggregate (COUNT) full table | 1 query |
| `Student::whereDate('created_at', today())->count()` | Aggregate (COUNT) with date filter | 1 query |

**Total: 2 queries** — all simple counting.

### Staff Dashboard
| Query | Type | Count |
|-------|------|-------|
| `CalendarCollector::todaySchedulesCount($schoolId)` | Uses existing collector | 1 query |
| `AttendanceCollector::todayAttendanceRate($schoolId)` | Uses existing collector | 1 query |
| `LeaveRequest::where('status', 'pending')->count()` | Aggregate (COUNT) × 2 (stat card + widget) | 2 queries |
| `LeaveRequest::where('status', 'approved')->whereDate('created_at', today())->count()` | Aggregate (COUNT) | 1 query |
| `CalendarCollector::upcomingEvents($schoolId, 4)` | Uses existing collector | 1 query |
| `LoginActivity::withoutGlobalScopes()->with('user')->latest()->limit(5)->get()` | Eager-loaded relation, limited to 5 rows | 1 query |

**Total: 7 queries** — `pending` count is duplicated across stat card and widget (minor; candidate for future caching).

## Key Observations
- All dashboard queries are simple aggregate/COUNT queries — no complex joins or subqueries.
- FeeCollector leverages existing caching layer where available.
- No N+1 issues detected — all queries are single-shot aggregates.
- The duplicated `pending` leave count (staff dashboard) is negligible at realistic data volumes.
