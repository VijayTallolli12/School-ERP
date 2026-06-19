# Payroll Processing Engine Audit

## Files Created (7)
| File | Purpose |
|------|---------|
| `database/migrations/2026_06_19_000003_create_payroll_processing_tables.php` | Migration: `payroll_runs` + `payroll_items` tables |
| `app/Modules/Payroll/Models/PayrollRun.php` | Model: BelongsToSchool, SoftDeletes, scope draft/locked |
| `app/Modules/Payroll/Models/PayrollItem.php` | Model: BelongsToSchool, SoftDeletes, morphTo employee |
| `app/Modules/Payroll/Requests/GeneratePayrollRequest.php` | Form request: validates month/year, checks duplicate runs |
| `app/Modules/Payroll/Requests/LockPayrollRunRequest.php` | Form request: validates notes for lock action |
| `resources/views/modules/payroll/_run_actions.blade.php` | Partial: view/lock/delete buttons for runs table |
| `e2e/payroll/payroll-processing.spec.ts` | 14 Playwright tests |

## Files Modified (12)
| File | Changes |
|------|---------|
| `app/Modules/Payroll/Repositories/PayrollRepositoryInterface.php` | +6 method signatures for run/item CRUD |
| `app/Modules/Payroll/Repositories/PayrollRepository.php` | +6 method implementations for run/item CRUD |
| `app/Modules/Payroll/Services/PayrollService.php` | +`generatePayroll()` and `lockRun()` methods with activity logging |
| `app/Modules/Payroll/Controllers/PayrollController.php` | +6 run endpoints, +3 report DataTables, +3 export data types |
| `app/Modules/Payroll/Policies/PayrollPolicy.php` | +3 gates: `process`, `lock`, `export` |
| `routes/modules/payroll.php` | +8 routes for runs CRUD + generate + lock, +3 report routes, export middleware updated |
| `resources/views/modules/payroll/index.blade.php` | +Payroll Runs tab, +Generate modal, +Run Details modal, +JS handlers |
| `resources/views/modules/payroll/reports.blade.php` | +3 processing report tabs: Run Summary, Employee Payroll, Gross vs Net |
| `database/seeders/PermissionSeeder.php` | +`process` and `lock` actions to payroll module, added to Payroll Manager role |
| `app/Providers/AppServiceProvider.php` | +PayrollRun, PayrollItem policy registration |

## Architecture
- **Processing engine**: Single `generatePayroll()` in PayrollService loops active salary structures + active salary components, calculates Gross (earnings) and Total Deductions, writes `payroll_items`.
- **Component calculation**: Fixed = `value`, Percentage = `(value/100) × (total_ctc/12)`.
- **Locking**: Draft→Locked one-way via `lockRun()`. Locked runs reject modifications.
- **Unique constraint**: `(school_id, month, year)` prevents duplicate runs for same period.
- **DataTables**: All run and report data uses `serverSide: true` with Yajra DataTables.
- **CSRF**: Lock handler includes `X-CSRF-TOKEN` header for POST AJAX.

## Route Summary (8 new routes)
| Method | URI | Name | Permission |
|--------|-----|------|------------|
| GET | `runs/data` | `payroll.runs.data` | payroll.view |
| POST | `runs/generate` | `payroll.runs.generate` | payroll.process |
| GET | `runs/{payrollRun}` | `payroll.runs.show` | payroll.view |
| POST | `runs/{payrollRun}/lock` | `payroll.runs.lock` | payroll.lock |
| DELETE | `runs/{payrollRun}` | `payroll.runs.destroy` | payroll.delete |
| GET | `runs/{runId}/items/data` | `payroll.runs.items.data` | payroll.view |
| GET | `reports/run-summary/data` | `payroll.reports.run-summary.data` | payroll.view |
| GET | `reports/employee-payroll/data` | `payroll.reports.employee-payroll.data` | payroll.view |
| GET | `reports/gross-vs-net/data` | `payroll.reports.gross-vs-net.data` | payroll.view |

## New Permissions
- `payroll.process` — Generate payroll runs
- `payroll.lock` — Lock (finalize) payroll runs
- `payroll.export` — Export reports (previously used `payroll.view`)

## Key Design Decisions
- Export middleware changed from `payroll.view` to `payroll.export` for granular control
- Run details modal uses a separate DataTable for items with server-side loading
- Lock is a simple `confirm()` + `$.ajax()` (no form needed)
- Generate modal uses standard `ajax-form` pattern with `erp:success` event
- Payroll Runs tab follows exact same pattern as existing foundation tabs
- 3 processing report tabs follow same filter + DataTable + export pattern as foundation reports

## Validation
- All PHP syntax checked (12 files, 0 errors)
- Migration ran successfully (693ms)
- All 45 payroll routes registered correctly
- Permissions seeded with `process`, `lock`, `export`
