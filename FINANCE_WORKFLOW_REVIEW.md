# Finance Workflow (Fees) — Production-Readiness Review

- **Date:** 2026-08-04
- **Scope:** Fees / Finance workflow only (`app/Modules/Fees`, fee tables, fee reports in `app/Modules/Reports`, fee API routes, fee views). No other modules reviewed.
- **Method:** Static code + schema + route + view audit. No code modified. No automated test run performed.
- **Verdict:** The core loop (categories → structures → assignment → collection → receipt → reports) is implemented, reasonably well-isolated per school, and protected by role-based permissions. It is **not yet production-ready for a financial module**: there is no audit trail, no refunds/discounts/scholarships/late-fees, no online payments, no payment correction, the "waived/cancelled" assignment state is broken, and automated test coverage is near zero.

---

## Severity Legend

| Level | Meaning |
|---|---|
| **Critical** | Financial integrity / compliance gap; must fix before production |
| **High** | Functional correctness, data integrity, or security risk; fix soon |
| **Medium** | Meaningful gap or risk; plan for next release |
| **Low** | Polish, hygiene, or defense-in-depth |

---

## 1. Feature Parity Matrix

| Feature (from brief) | Status | Severity | Where |
|---|---|---|---|
| Fee Categories | ✅ Existing (CRUD, sort, per-school unique code) | — | `FeeService.php:35-54` |
| Fee Structures | ✅ Existing (per class+year, line items, status) | — | `FeeService.php:56-109` |
| Fee Assignment (individual) | ✅ Existing | — | `FeeService.php:111-149` |
| Bulk Assignment (class-wise) | ✅ Existing | — | `FeeService.php:154-215` |
| Discounts / Concessions | ❌ Missing entirely | **High** | no tables/models/UI |
| Scholarships | ❌ Missing entirely | **High** | no tables/models/UI |
| Fines / Late-fee calculation | ❌ Missing for fees (Library has its own fines only) | **High** | — |
| Collection (incl. partial payments) | ✅ Existing (partial amounts per line, overpay rejected) | — | `FeeService.php:264-323` |
| Online Payments | ❌ Missing — gateway config UI exists but is never consumed; `paymentModes()` has no `online` | **High** | `FeePayment.php:38-46` |
| Receipts (number, print, PDF) | ✅ Existing (per school+year sequence, `RCP-{school}-{year}-{seq}`) | — | `FeeService.php:331-350` |
| Refunds | ❌ Missing entirely | **High** | no tables/models/UI |
| Defaulters | ⚠️ Partial — report + charts + exports exist; no automated communication flow (manual AI-agent only) | **Medium** | `FeeDefaulterReportRepository.php` |
| Reports (dashboard/paid/pending/overdue/class summary) | ✅ Existing with PDF/Excel/print | — | `FeeReportController.php` |
| Excel export | ✅ Existing (Maatwebsite, via Reports module) | — | `FeeReportExport.php` |
| PDF export | ✅ Existing (dompdf, receipts + reports) | — | — |
| Audit log | ❌ Missing — `spatie/laravel-activitylog` installed and used by Library module, but **zero** usage in Fees | **Critical** | `app/Modules/Fees/**` |
| Fee reminders / notifications | ⚠️ Partial — manual AI agent creates in-app notifications; `FeeReminderGenerated` event never dispatched; no SMS/email | **Medium** | `FeeCollectionAgent.php` |
| Payment edit / reversal | ❌ Only hard-delete (no audit, no reversal ledger) | **High** | `FeeService.php:325-329` |

---

## 2. Existing Features (verified)

1. **Fee Categories** — full CRUD; `code` unique per school; delete blocked when referenced by structure items or student fee items (`FeeService.php:47-54`). Default category codes available (`FeeCategory::defaultCodes()`).
2. **Fee Structures** — one structure per (school, academic year, class section), enforced by request rule (`SaveFeeStructureRequest.php:41-53`) and a composite unique index (`2026_05_19_000010`). Line items validated for duplicate categories. Update/delete guards: delete blocked once assigned to students (`FeeService.php:101-109`).
3. **Student Assignment** — individual + bulk. Duplicate assignment per (student, year) rejected; bulk skips already-assigned. Structure/academic-year consistency validated. Line items snapshotted from the structure at assignment time (good — later structure edits don't mutate live dues).
4. **Collections** — multi-line partial payment entry with per-line balance cap and overpay rejection inside a DB transaction with row locks (`lockForUpdate`), so concurrent double-submission is mitigated (`FeeService.php:299-319`). Payment modes: cash / UPI / bank transfer / cheque.
5. **Receipts** — per-school-per-year sequential receipt numbers (`FeeReceiptSequence` + `lockForUpdate`); printable HTML receipt and PDF (`receipt_print.blade.php`), accessible from the collections table.
6. **Due tracking** — Dues tab in the Fees page (pending + overdue badges) and a dedicated "pending" report with `overdue` flag.
7. **Defaulters report** — filters by year/class/student/structure/due-date range/outstanding range; summary KPIs; three charts (outstanding by class, collected vs outstanding, monthly trend); parent contact info; PDF/Excel/print exports.
8. **Reports dashboard** — total collected, pending fees, monthly collection, collection efficiency, links to all fee reports.
9. **Report exports** — PDF (dompdf) and Excel (Maatwebsite) for paid/pending/overdue/collection-summary/defaulters; print views.
10. **School isolation** — all fee entities (categories, structures, student_fees, student_fee_items, fee_payments, receipt sequences) use `BelongsToSchool` global scoping; `student_fee_items.school_id` was backfilled via `2026_08_03_000001`. Reports controllers/repositories scope by `SchoolContext` explicitly. School context is set centrally by `SetSchoolContext` middleware.
11. **RBAC** — `fees.view/create/collect/update/delete/reports` permissions; policies registered (`AppServiceProvider.php:237-240`); `Gate::before` grants super-admin; routes gated by `permission:fees.*` middleware plus `AuthorizesRequests` in the controller.
12. **Parent/Student API (read-only)** — `api.v1.fees.*` endpoints with guardian-ownership checks (`FeeApiController.php`).

---

## 3. Missing Features

| # | Feature | Notes |
|---|---|---|
| M1 | **Discounts / Concessions** | No table, model, request, UI, or report. The only "waiver" is a string `status` on the assignment. Cannot express percentage/amount discounts per category or per student. |
| M2 | **Scholarships** | Absent entirely (same as M1). |
| M3 | **Fines / Late-fee** | No per-category or per-structure late-fee / due-date penalty, no `fine` field anywhere in fee tables. `FeeCollectionAgent.php:186` only *mentions* late fees in reminder text. |
| M4 | **Online payments** | `routes/modules/settings` stores Razorpay/Stripe keys+secrets in the DB, but nothing consumes them; `FeePayment::paymentModes()` excludes `online`; parent/student apps are **read-only** for fees (no POST anywhere). No payment-gateway package in `composer.json`. |
| M5 | **Refunds** | No refund table/flow. A payment can only be hard-deleted (items permanently gone, no reversal record). |
| M6 | **Audit trail** | No `activity()` logging on any fee mutation (compare Library: `LibraryService.php:155`). `spatie/laravel-activitylog` is installed but unused by Fees. |
| M7 | **Payment correction/edit** | No update route for a payment; a mis-entered payment must be deleted and re-entered (receipt number is burned, no audit of either action). |
| M8 | **Payment reference fields** | No cheque number, UPI transaction ID, bank reference, or "collected at counter vs online" tracking — hard to reconcile. |
| M9 | **Automated defaulter follow-up** | Reminders exist only via a manually-run AI agent that creates in-app notifications. The `FeeReminderGenerated` event + listeners (`EventServiceProvider.php:46`, `SendPushNotification`, `CreateDatabaseNotification`, `LogNotificationActivity`) are **never dispatched** — dead wiring. No SMS/email channel. |
| M10 | **Fee settings** | No configurable receipt prefix/footer, currency, default due-date policy, or late-fee rules. Receipt hard-codes `₹`. |

---

## 4. Broken Features / Bugs

| # | Severity | Finding | Location |
|---|---|---|---|
| B1 | **High** | **Waived/cancelled assignments are still collectable & reported.** No query filters `student_fees.status = 'active'`. A `waived` or `cancelled` assignment's lines still appear in the collect modal (`listStudentFeeItemsForCollection`), the dues table, pending/overdue/defaulters reports, and the "waived" balance stays outstanding. The `waived` status option in the edit UI is therefore misleading. | `FeeService.php:355-364`, `FeeService.php:410-460`, `FeesController.php:347-378`, `FeeDefaulterReportRepository.php:29-59` |
| B2 | **High** | **No audit trail for money movements** (create/delete payment, delete assignment). A deleted payment leaves no record of who did it, when, or why. | `FeeService.php:325-329` |
| B3 | **Medium** | **API `status` filter accepted but ignored.** `FeeApiController::studentFees()` validates `status in:paid,partial,pending,overdue` then never applies it. | `FeeApiController.php:23-68` |
| B4 | **Medium** | **"Online" payment-mode filter is dead** in the paid report dropdown; backend modes have no `online`, so selecting it always returns empty. | `Reports/Views/fees/paid.blade.php:36` |
| B5 | **Low** | **Dead code: legacy report methods** in `FeesController` (`reportCollection`, `reportDue`, `reportClassWise`, `reportDaily` + 4 `*Pdf`) and the `modules.fees.reports.print_*` views are orphaned by the permanent redirects in `routes/modules/fees.php:42-50`. | `FeesController.php:380-502` |
| B6 | **Low** | **Dead code: `FeeReportService`** is never referenced. It also queries `FeePayment.payment_status` (`whereIn('payment_status', ['paid','completed'])`) — a column that **does not exist** in `fee_payments` — so it would throw if invoked. | `FeeReportService.php:17-42` |
| B7 | **Low** | **Dead event wiring:** `FeeReminderGenerated` registered with 3 listeners but never dispatched. | `app/Events/FeeReminderGenerated.php` |
| B8 | **Low** | **Legacy redirects change report semantics:** old `reports/{collection,due,class-wise,daily}/pdf` redirect to an HTML report page, not a PDF. Old route names (`reports.collection`, etc.) still exist as redirects. | `routes/modules/fees.php:42-50` |
| B9 | **Low** | **Unreachable "Fee Reports Moved" tab** (`#reportsPane`) — the tab nav never renders a button for it (only the external "View Fee Reports" link). | `index.blade.php:149-160` |
| B10 | **Low** | **Individual assignment doesn't check class-section match** (bulk path does). A structure for class 10-A can be assigned to a class 9 student. | `FeeService.php:111-149` vs `FeeService.php:163-165` |
| B11 | **Low** | `reports.fees.export.{pdf,excel}` accepts an arbitrary `{type}`; unknown types export an empty file. | `routes.php:62-64`, `FeeReportController.php:120-140` |

---

## 5. Security Issues

### Critical
| # | Finding | Location |
|---|---|---|
| S1 | **No financial audit trail** — every money/entity mutation (payment recorded, payment deleted, assignment changed/deleted, receipt burned) is unlogged and unattributable. For a finance workflow this is a compliance-grade gap (cf. Library module logs activity; Fees does not). | `app/Modules/Fees/**` |

### High
| # | Finding | Location |
|---|---|---|
| S2 | **Payment deletion destroys line-level records permanently** — `deleteFeePayment()` hard-deletes `fee_payment_items` and soft-deletes the payment with no reversal record, no audit, and no "already printed receipt" guard. Financial integrity risk. | `FeeService.php:325-329` |
| S3 | **Waived/cancelled dues remain payable** (see B1) — a collector can accept money for lines the school intended to waive. | see B1 |

### Medium
| # | Finding | Location |
|---|---|---|
| S4 | **`FeePaymentItem` is not school-scoped** (no `school_id` column/model trait). Direct `FeePaymentItem::query()` calls bypass isolation; currently only reached through scoped parents so not exploitable today, but it's one query away from cross-school leakage. | `FeePaymentItem.php`, `FeeService.php:256-262` |
| S5 | **Request-layer validation doesn't tie payment lines to the student/year.** `StoreFeePaymentRequest` validates `student_id`/`academic_year_id` by school but only checks `lines.*.student_fee_item_id` is an integer. The *service* blocks cross-student lines (good), but the validation layer is incomplete (defense-in-depth). | `StoreFeePaymentRequest.php:20-29` |
| S6 | **Unrestricted backdating** — `paid_on` accepts any past/future date with no window or approval control. | `StoreFeePaymentRequest.php:23` |
| S7 | **Payment-gateway keys/secrets stored in DB settings** (Razorpay/Stripe), unencrypted, and the feature is unused. Dead sensitive config. | `SettingsService.php:63-89` |
| S8 | **`classWiseFeeReport` uses raw joins without explicit school filters** on `students`/`student_sessions`/`class_section`; currently contained by the scoped driver table (`student_fee_items`), but fragile SQL. | `FeeService.php:465-506` |
| S9 | **Double gate on fee reports** — the Reports group requires `reports.view` and the controller adds `can:fees.reports`; a user holding `fees.reports` without `reports.view` is silently locked out. | `app/Modules/Reports/routes.php:6`, `FeeReportController.php:27` |

### Low
| # | Finding | Location |
|---|---|---|
| S10 | `studentFeeItems` (admin API) validates `student_id`/`academic_year_id` as bare integers; isolation relies on the global scope only. | `FeesController.php:325-328` |
| S11 | `FeeApiController` validates `student_id` against all `students` (not per-school); a cross-school id yields an empty result rather than 403. | `FeeApiController.php:24` |
| S12 | Concurrency on first-ever receipt sequence row: two simultaneous payments for a new (school, year) can race the `firstOrCreate` and throw a unique-constraint 500. | `FeeService.php:331-345` |
| S13 | Concurrent assignment duplicate check is read-then-write inside the transaction; the DB unique index will surface as a QueryException (500) rather than a clean 422 under a race. | `FeeService.php:122-129` |

---

## 6. Performance Issues

| # | Severity | Finding | Location |
|---|---|---|---|
| P1 | **High** | **Defaulters report loads up to 50,000 fee items** with deep eager loading (student → guardians, sessions → class/section, structure) and groups/sums in PHP, then ships the whole result as JSON. Will degrade badly at scale. | `FeeDefaulterReportRepository.php:59-205` |
| P2 | **Medium** | **Fake server-side paging on reports.** `paid` (limit 5,000) and `pending` (limit 10,000) are computed into arrays, wrapped with `DataTables::of($array)`, and the entire dataset is sent to the browser each request. | `FeeReportController.php:79-105` |
| P3 | **Medium** | Dues table loads up to 5,000 items with `withSum` correlated subqueries and filters in PHP. | `FeesController.php:347-378` |
| P4 | **Medium** | Collection-summary (`classWiseFeeReport`) is SQL-aggregated (good), but the legacy `FeeReportService::collectionSummary` (dead) and the defaulters path load full rows. | `FeeService.php:465-506` |
| P5 | **Low** | `index()` loads **all active students** + all structures + all years/class-sections on every page open; the `students` variable is unused (searchable select uses AJAX). | `FeesController.php:39-64` |
| P6 | **Low** | `withSum`/`paid_sum` correlated subqueries per item across big lists (pending/due/defaulters). | `FeeRepository.php:43-55` |

---

## 7. Missing APIs

| # | Missing API | Notes |
|---|---|---|
| A1 | **Online payment initiation + webhook/callback** | No POST to create a gateway order, no return/webhook handler, no `online` mode. Parent/student apps are read-only. |
| A2 | **Parent/student fee payment** | No authenticated POST to record a payment from the parent/student apps. |
| A3 | **Payment correction / reverse / refund** | No update, void-with-reason, or refund endpoints. |
| A4 | **Bulk discount/waiver** | No API to apply concessions per category/student/class. |
| A5 | **Per-student fee ledger/statement** | `studentFees` returns items+payments but no consolidated "statement" (opening, billed, paid, balance, history per head). |
| A6 | **Defaulter reminder trigger** | No dedicated endpoint; only the internal AI agent flow. |
| A7 | **`status` filter on `fees.index` API is broken** | Validated but never applied (see B3) — the API advertises a filter it doesn't honor. |

---

## 8. Missing UI

| # | Missing UI | Notes |
|---|---|---|
| U1 | **Student fee ledger / statement screen** | Admin has no per-student consolidated fee view (billed / paid / balance / receipts / history); only the collection modal and reports tables. |
| U2 | **Discounts / Concessions / Scholarships screens** | Feature absent (see M1/M2). |
| U3 | **Refund / reversal screen** | Feature absent (see M5). |
| U4 | **Payment edit screen** | No way to correct a payment (see M7). |
| U5 | **Fee settings screen** | No receipt footer/prefix/currency/late-fee configuration. |
| U6 | **Parent/student online payment + fee statement** | API/UI read-only today. |
| U7 | **Bulk due-date editor** | Assignment items are edited one row at a time only. |

---

## 9. Missing Reports

| # | Missing Report | Notes |
|---|---|---|
| R1 | **Receipt register** (all receipts issued in a period, with collector + mode + amount) | Partially covered by "paid" report; no receipt-number-ordered register. |
| R2 | **Category/head-wise collection summary** | Only class-wise summary exists. |
| R3 | **Concession / discount / scholarship register** | Feature absent. |
| R4 | **Refund register** | Feature absent. |
| R5 | **Late-fee / fine ledger** | Feature absent. |
| R6 | **Payment-mode-wise collection summary** | Only a filter in the paid report. |
| R7 | **Per-student statement report (printable/PDF)** | Only row-level pending/paid tables. |

---

## 10. Test Coverage

- **Only test:** `tests/Feature/FeeApiSmokeTest.php` — 4 tests, all guardian read-only API cases.
- **No coverage for:** category/structure/assignment CRUD, bulk assignment, payment recording (partial/overpay/duplicate), receipt numbering, payment deletion, dues data, defaulters, reports, PDF/Excel exports, school-isolation on all fee entities, authorization matrix, and the **waived/cancelled bug (B1)**.
- **Impact:** The finance loop has essentially no regression safety net. The B1 bug and B3 bug would both have been caught by basic feature tests.

---

## 11. Prioritized Recommendations

1. **[Critical]** Add audit logging to all fee mutations (`spatie/laravel-activitylog`, already installed) — category/structure/assignment/payment create-update-delete, receipts issued. Log actor, payload, and reason. *(S1)*
2. **[High]** Fix the waived/cancelled handling: filter `student_fees.status = 'active'` in `listStudentFeeItemsForCollection`, `dueReport`, `duesData`, defaulters, and `FeeCollectionAgent`; decide whether a waiver is a status or an amount. *(B1, S3)*
3. **[High]** Replace payment deletion with a **void/reversal flow**: mark payment `void` (keep items), record reason + actor, exclude from collection/receipt totals, and reprint-void receipts. *(S2, M5, M7)*
4. **[High]** Implement **discounts/concessions/scholarships** and **late-fee** as first-class concepts (fields on `student_fee_items` + structure lines, plus reports). *(M1, M2, M3)*
5. **[High]** Either implement **online payment** (consume existing Razorpay/Stripe settings) or remove the dead config; add `online` mode + webhook + reconciliation fields (gateway txn id). *(M4, S7)*
6. **[Medium]** Add per-student fee **statement/ledger** view + printable PDF, and a receipt register. *(U1, R1, R7)*
7. **[Medium]** Convert report "server-side" tables to real SQL-paginated queries; cap and index the defaulters query (already partially indexed via `student_fee_items(school_id, student_fee_id)`). *(P1–P4)*
8. **[Medium]** Apply `status` filter in `FeeApiController::studentFees`; drop the dead `online` filter option. *(B3, B4)*
9. **[Medium]** Validate payment lines at the request layer (belong to student+year), restrict backdating, and add idempotency key to `storeCollection`. *(S5, S6)*
10. **[Low]** Delete dead code: legacy report methods + print views (B5), `FeeReportService` (B6), unreachable tab (B9); wire or remove `FeeReminderGenerated` (B7); scope raw SQL joins in `classWiseFeeReport` explicitly (S8); catch unique-constraint races with clean 422s (S12, S13).
11. **[Low]** Add feature tests for the finance workflow (payment recording, overpay rejection, receipt sequencing, bulk assign, school isolation, reports/exports) and a regression test for B1.

---

## Appendix — Files Reviewed

- `app/Modules/Fees/Controllers/FeesController.php`
- `app/Modules/Fees/Services/{FeeService,FeeReportService}.php`
- `app/Modules/Fees/Repositories/{FeeRepository,FeeRepositoryInterface}.php`
- `app/Modules/Fees/Models/{FeeCategory,FeeStructure,FeeStructureItem,StudentFee,StudentFeeItem,FeePayment,FeePaymentItem,FeeReceiptSequence}.php`
- `app/Modules/Fees/Requests/*.php`, `app/Modules/Fees/Policies/*.php`
- `routes/modules/fees.php`, `routes/modules/api/fees.php`, `routes/web.php`, `routes/api.php`, `routes/modules/api.php`, `routes/modules/reports.php`
- `app/Modules/Reports/routes.php`, `app/Modules/Reports/Controllers/FeeReportController.php`, `app/Modules/Reports/Repositories/FeeDefaulterReportRepository.php`, `app/Modules/Reports/Exports/FeeReportExport.php`
- `app/Modules/Reports/Views/fees/*` (index, paid, pending, overdue, collection_summary, defaulters, pdf, print, defaulters_pdf, defaulters_print)
- `resources/views/modules/fees/*` (index, receipt_print, `_actions_*`, `reports/print_*`)
- `app/Http/Controllers/Api/V1/FeeApiController.php`, `app/Core/Tenant/{BelongsToSchool,SchoolContext}.php`, `app/Http/Middleware/SetSchoolContext.php`
- `app/Providers/AppServiceProvider.php`, `app/Providers/EventServiceProvider.php`, `app/Events/FeeReminderGenerated.php`, `app/Listeners/*`
- `app/Modules/AiAgents/Agents/FeeCollectionAgent.php`
- `database/migrations/2024_01_05_0000{10..80}*fee*`, `2026_05_19_0000{10,20}`, `2026_08_03_000001`
- `database/seeders/PermissionSeeder.php`, `tests/Feature/FeeApiSmokeTest.php`
- `composer.json` (dependency check)
