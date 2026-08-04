# Fees Report Consolidation Plan

## Objective
Eliminate duplicate fee reporting by making **Reports > Fee Reports** the single source of truth. This is a UI/navigation refactor only — no report logic, controller, or DataTable changes.

---

## Audit Summary

### Routes Audited

#### A. Fees Module Report Routes (`routes/modules/fees.php` lines 42-51)

| # | Route Name | URI | Controller | Purpose |
|---|-----------|-----|-----------|---------|
| 1 | `admin.fees.reports.collection` | `GET /admin/fees/reports/collection` | `FeesController::reportCollection` | Collection printable view |
| 2 | `admin.fees.reports.collection.pdf` | `GET /admin/fees/reports/collection/pdf` | `FeesController::reportCollectionPdf` | Collection PDF |
| 3 | `admin.fees.reports.due` | `GET /admin/fees/reports/due` | `FeesController::reportDue` | Due report printable view |
| 4 | `admin.fees.reports.due.pdf` | `GET /admin/fees/reports/due/pdf` | `FeesController::reportDuePdf` | Due report PDF |
| 5 | `admin.fees.reports.class-wise` | `GET /admin/fees/reports/class-wise` | `FeesController::reportClassWise` | Class-wise printable view |
| 6 | `admin.fees.reports.class-wise.pdf` | `GET /admin/fees/reports/class-wise/pdf` | `FeesController::reportClassWisePdf` | Class-wise PDF |
| 7 | `admin.fees.reports.daily` | `GET /admin/fees/reports/daily` | `FeesController::reportDaily` | Daily collection printable view |
| 8 | `admin.fees.reports.daily.pdf` | `GET /admin/fees/reports/daily/pdf` | `FeesController::reportDailyPdf` | Daily collection PDF |

#### B. Reports Module Fee Report Routes (`app/Modules/Reports/routes.php` lines 52-64)

| # | Route Name | URI | Controller | Purpose |
|---|-----------|-----|-----------|---------|
| 1 | `reports.fees.index` | `GET /reports/fees` | `FeeReportController::index` | Dashboard with KPIs |
| 2 | `reports.fees.paid` | `GET /reports/fees/paid` | `FeeReportController::paid` | Paid fees DataTable + view |
| 3 | `reports.fees.pending` | `GET /reports/fees/pending` | `FeeReportController::pending` | Pending fees DataTable + view |
| 4 | `reports.fees.overdue` | `GET /reports/fees/overdue` | `FeeReportController::overdue` | Overdue fees DataTable + view |
| 5 | `reports.fees.collection_summary` | `GET /reports/fees/collection-summary` | `FeeReportController::collectionSummary` | Collection summary DataTable + view |
| 6 | `reports.fees.defaulters` | `GET /reports/fees/defaulters` | `FeeReportController::defaulters` | Fee defaulters with charts + DataTable |
| 7 | `reports.fees.defaulters.students_by_class` | `GET /reports/fees/defaulters/students-by-class` | `FeeReportController::getStudentsByClass` | AJAX student dropdown |
| 8 | `reports.fees.defaulters.export.pdf` | `GET /reports/fees/defaulters/export/pdf` | `FeeReportController::exportDefaultersPdf` | Defaulters PDF |
| 9 | `reports.fees.defaulters.export.excel` | `GET /reports/fees/defaulters/export/excel` | `FeeReportController::exportDefaultersExcel` | Defaulters Excel |
| 10 | `reports.fees.defaulters.print` | `GET /reports/fees/defaulters/print` | `FeeReportController::printDefaulters` | Defaulters print view |
| 11 | `reports.fees.export.pdf` | `GET /reports/fees/{type}/export/pdf` | `FeeReportController::exportPdf` | Generic PDF export |
| 12 | `reports.fees.export.excel` | `GET /reports/fees/{type}/export/excel` | `FeeReportController::exportExcel` | Generic Excel export |
| 13 | `reports.fees.print` | `GET /reports/fees/{type}/print` | `FeeReportController::printReport` | Generic print view |

### Mapping: Fees Module Reports → Reports Module Fee Reports

| Old Route (Fees Module) | Maps To (Reports Module) | Reason |
|------------------------|--------------------------|--------|
| `fees.reports.collection` | `reports.fees.paid` | Both use `FeeService::collectionReport()` |
| `fees.reports.due` | `reports.fees.pending` | Both use `FeeService::dueReport($id, false)` |
| `fees.reports.class-wise` | `reports.fees.collection_summary` | Both use `FeeService::classWiseFeeReport()` |
| `fees.reports.daily` | `reports.fees.paid` | Daily is subset of paid; redirect with date params |
| `fees.reports.collection.pdf` | `reports.fees.export.pdf` (type=paid) | Both use DomPDF |
| `fees.reports.due.pdf` | `reports.fees.export.pdf` (type=pending) | Both use DomPDF |
| `fees.reports.class-wise.pdf` | `reports.fees.export.pdf` (type=collection_summary) | Both use DomPDF |
| `fees.reports.daily.pdf` | `reports.fees.export.pdf` (type=paid) | Both use DomPDF |

### Controllers
- **FeesController** (`App\Modules\Fees\Controllers\FeesController`): 8 report methods
- **FeeReportController** (`App\Modules\Reports\Controllers\FeeReportController`): 14 report methods

**No controllers will be modified.**

### DataTables
- Fees module: `#duesTable` (client-side), `#collectionsTable` (server-side)
- Reports module: `#paidTable` (server-side), `#pendingTable` (server-side), `#overdueTable` (server-side), `#summaryTable` (server-side), `#defaultersTable` (client-side)

**No DataTables logic will be modified.**

### Exports
- Fees module: PDF via `FeesController` (DomPDF, legacy views)
- Reports module: Excel (`FeeReportExport`) + PDF (DomPDF) + Print — 6 report types × 3 formats

**No export logic will be modified.**

### Sidebar
- `Reports > Fee Reports` submenu already exists (lines 136-181) with all 6 fee report links
- `Fees` is a standalone top-level link (no sub-nav)

**No sidebar changes needed** — the Reports module Fee Reports navigation is already the single source of truth.

---

## Changes to Make

### Change 1: Fees Module Index — Replace Reports Tab (lines 14-27, 145-238)
Replace the `#reportsPane` tab with a "View Fee Reports" external link.
- Tab label: `"Reports"` → `"View Reports"`
- Tab click: Opens `route('reports.fees.index')` in a new tab
- Remove the 4 report card forms (Collection, Due, Class-wise, Daily)
- Keep the tab element but make it a link to Reports > Fee Reports Dashboard

### Change 2: Fees Module Routes — Add Permanent Redirects (lines 42-51)
Replace the 8 inline route definitions with `Route::permanentRedirect()` entries:
```
/admin/fees/reports/collection  → /reports/fees/paid
/admin/fees/reports/due         → /reports/fees/pending
/admin/fees/reports/class-wise  → /reports/fees/collection-summary
/admin/fees/reports/daily       → /reports/fees/paid
```

PDF routes redirect to the appropriate report page (PDF params are not forwarded — user re-downloads from the new UI).

### Change 3: JS Reference in Fees Index (line 699-704)
Update `#collectionPdfBtn` click handler — since the report routes will redirect, this JS should be removed or pointed to the reports module.

---

## Routes That Remain
**All** `reports.fees.*` routes — these ARE the single source of truth.

## Routes That Redirect
All `admin.fees.reports.*` routes will use `Route::permanentRedirect()`.

## Routes That Are Removed
None — the 8 legacy fee report routes still exist as redirect entries. No routes are deleted.

## No-Changes Guarantee
- ✅ Fee calculations: untouched
- ✅ Report queries: untouched
- ✅ DataTables logic: untouched
- ✅ Export logic: untouched
- ✅ Controllers: untouched
- ✅ Services: untouched
- ✅ Repositories: untouched
- ✅ Report generation: untouched
- ✅ PDF generation: untouched
- ✅ Excel generation: untouched
- ✅ Print generation: untouched
- ✅ Controller methods: untouched
