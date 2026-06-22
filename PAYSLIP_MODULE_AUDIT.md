# Payslip Management Module Audit

## Files Created (6)
| File | Purpose |
|------|---------|
| `app/Modules/Payroll/Models/EmployeePayslip.php` | Model: BelongsToSchool, SoftDeletes, morphTo employee, BelongsTo payrollRun + payrollItem, casts earnings_json/deductions_json as array |
| `app/Modules/Payroll/Requests/GeneratePayslipRequest.php` | Form request: validates locked run, checks duplicate payslip via `payslipExists()` |
| `app/Modules/Payroll/Requests/BulkGeneratePayslipRequest.php` | Form request: validates locked run for bulk generation |
| `database/migrations/2026_06_19_000004_create_employee_payslips_table.php` | Migration: `employee_payslips` table with unique constraint on `(payroll_run_id, payroll_item_id)` |
| `resources/views/modules/payroll/payslip_pdf.blade.php` | PDF layout: school logo, employee info, earnings/deductions tables, summary |
| `resources/views/modules/payroll/payslip_print.blade.php` | Printable HTML: same data as PDF, auto-print trigger |

## Files Modified (8)
| File | Changes |
|------|---------|
| `app/Modules/Payroll/Repositories/PayrollRepositoryInterface.php` | +6 method signatures: `employeePayslips()`, `payslipHistory()`, `createPayslip()`, `getNextPayslipNumber()`, `findPayslip()`, `payslipExists()` |
| `app/Modules/Payroll/Repositories/PayrollRepository.php` | +6 implementations: sequential payslip number format `PS-YYYY-MM-XXXXXX`, morphTo employee data copy, frozen earnings/deductions JSON |
| `app/Modules/Payroll/Services/PayrollService.php` | +`generatePayslipItem()`, `bulkGeneratePayslips()`, `getPayslipData()`, `calculateBreakdown()` — recalculates component amounts from active salary components |
| `app/Modules/Payroll/Controllers/PayrollController.php` | +7 endpoints: `payslipsData`, `payslipHistoryData`, `generatePayslip`, `bulkGeneratePayslips`, `showPayslip`, `downloadPayslipPdf`, `printPayslip`; fixed missing request imports |
| `app/Modules/Payroll/Policies/PayrollPolicy.php` | +3 gates: `payslipView`, `payslipGenerate`, `payslipExport` |
| `routes/modules/payroll.php` | +7 routes for payslip CRUD + generate + bulk-generate + PDF + print |
| `resources/views/modules/payroll/index.blade.php` | +Payslips tab: locked-run dropdown selector, "Generate All" button, DataTable with payslip data |
| `resources/views/modules/payroll/reports.blade.php` | +Payslip History tab: search filter + DataTable (search on employee name / payslip #) |
| `database/seeders/PermissionSeeder.php` | +`'payroll.payslip' => ['view', 'generate', 'export']` |
| `app/Providers/AppServiceProvider.php` | +`EmployeePayslip::class => PayrollPolicy::class` registration |

## Architecture
- **Generation**: `generatePayslipItem()` loops active salary components, calculates earnings (fixed = value, percentage = (value/100)×(total_ctc/12)) and deductions, stores frozen JSON in `earnings_json`/`deductions_json`.
- **Bulk generation**: `bulkGeneratePayslips()` iterates all items in a locked run, skips items that already have payslips (idempotent).
- **Frozen data**: Employee name, department, designation are copied from the morph relation at generation time. Financial totals stored directly (not computed from payroll_item at view time).
- **Payslip number format**: `PS-YYYY-MM-000001` — sequential per month, reset each month.
- **PDF**: Uses DomPDF with A4 portrait, loads `payslip_pdf.blade.php` view.
- **Print**: Standalone Blade view with auto-print JavaScript trigger.
- **Guardrails**: Enforced at FormRequest (authorize + withValidator), Service (locked check + duplicate check), and DB (unique constraint) levels.
- **DataTables**: All payslip data uses `serverSide: true` with Yajra DataTables.

## Route Summary (7 new routes)
| Method | URI | Name | Permission |
|--------|-----|------|------------|
| GET | `payslips/data` | `payroll.payslips.data` | payroll.payslip.view |
| GET | `payslips/history/data` | `payroll.payslips.history.data` | payroll.payslip.view |
| POST | `payslips/generate` | `payroll.payslips.generate` | payroll.payslip.generate |
| POST | `payslips/bulk-generate` | `payroll.payslips.bulk-generate` | payroll.payslip.generate |
| GET | `payslips/{payslip}` | `payroll.payslips.show` | payroll.payslip.view |
| GET | `payslips/{payslip}/pdf` | `payroll.payslips.pdf` | payroll.payslip.export |
| GET | `payslips/{payslip}/print` | `payroll.payslips.print` | payroll.payslip.view |

## New Permissions
- `payroll.payslip.view` — View payslips and payslip history
- `payroll.payslip.generate` — Generate single or bulk payslips
- `payroll.payslip.export` — Download PDF payslips

## Key Design Decisions
- Employee name/department/designation stored as frozen text at generation time (morphTo employee, falls back to "Unknown" if relation missing).
- Department/designation read via optional nested morph relations on employee (nullable fallback).
- Breakdown calculation reuses component logic from processing engine — not read from payroll_item data.
- One payslip per payroll_item — enforced by unique constraint + `payslipExists()` check.
- Payslip History is a tab on the reports page (not a separate page).
- No email, WhatsApp, bank transfer, or digital signature implementation (out of scope).
- `payroll.payslip.*` permissions are independent from base `payroll.*` permissions.

## Validation
- Migration ran successfully (676ms), rolled back and re-ran after column type fix
- `employee_id` column changed from `unsignedBigInteger` to `string(50)` to match actual employee ID format
- All 7 payslip routes registered correctly (52 total payroll routes)
- Permissions seeded with `view`, `generate`, `export` under `payroll.payslip`
- Single payslip generation: **PASS** (200, success=true, payslip_number matches `/^PS-\d{4}-\d{2}-\d{6}$/`)
- Duplicate payslip prevention: **PASS** (422 validation error)
- Bulk generate: **PASS** (200, success=true)
- Show endpoint: **PASS** (200, success=true, returns payslip data)
- Draft run prevention: **PASS** (422 validation error)
- Print view: **PASS** (payslip-print-area visible)
- PDF download: **PASS** (200, content-type contains `application/pdf`)
- Payslip History tab: **PASS** (DataTable visible with correct columns)
- All 14 processing engine tests: **PASS** (0 regressions)
