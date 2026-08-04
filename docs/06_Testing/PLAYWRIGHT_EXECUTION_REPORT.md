# PLAYWRIGHT EXECUTION REPORT

Generated: 2026-06-22

---

## Execution Summary

| Metric | Value |
|--------|-------|
| Total tests | 162 |
| Passed | 31 (observed) |
| Failed | 1 (+1 retry) |
| Skipped | 130 (dependant on failed beforeAll) |
| Duration | > 5 min (timeout) |
| Project | chromium |
| Workers | 1 |
| Retries | 1 |

---

## Partial Results (before timeout)

### Failed Tests

| Test | File | Duration | Error |
|------|------|----------|-------|
| should show Ask ERP button in navbar | `ask-erp-mvp.spec.ts:38` | 2ms | `beforeAll` hook failed — login/storageState setup failure; auth.json not created; all dependant tests skipped |

**Root Cause of Failure:** The `beforeAll` hook attempts to log in and create a shared `storageState`, but the Laravel dev server may not be running on `http://127.0.0.1:8000`, or login credentials do not match. Since this is the first test file alphabetically and `fullyParallel: false`, all dependant tests (including the entire `ask-erp-mvp` suite) were marked as skipped on retry.

### Passed Tests (31 of 162)

All 31 passing tests are from `erp-audit.spec.ts` (the audit suite runs after the ask-erp suite completes with retries):

| # | Test | Duration |
|---|------|----------|
| 1 | Login | 19.1s |
| 2 | Dashboard | 13.4s |
| 3 | Dashboard > Dashboard | 14.2s |
| 4 | Access Control > Roles | 19.3s |
| 5 | Access Control > Permissions | 17.7s |
| 6 | Modules > Notifications | 17.1s |
| 7 | Modules > Fees | 23.2s |
| 8 | Modules > Settings | 14.0s |
| 9 | Reports > Students > Student Reports Dashboard | 16.1s |
| 10 | Reports > Students > Student Directory | 16.9s |
| 11 | Reports > Students > Gender-wise Report | 14.7s |
| 12 | Reports > Attendance > Attendance Reports Dashboard | 14.8s |
| 13 | Reports > Attendance > Daily Attendance | 16.7s |
| 14 | Reports > Attendance > Monthly Attendance | 14.7s |
| 15 | Reports > Attendance > Class-wise Attendance | 13.6s |
| 16 | Reports > Attendance > Absent Students Report | 16.3s |
| 17-29 | (remaining module/report tests) | ~13-23s each |
| 30 | Modals open and close correctly | ~5s |
| 31 | Mobile responsiveness | ~8s |

---

## Test Execution Details

### Suites

| Suite | File | Tests | Status |
|-------|------|-------|--------|
| Ask ERP MVP | `ask-erp-mvp.spec.ts` | 14 | FAILED (beforeAll hook) |
| School ERP Audit | `erp-audit.spec.ts` | ~59 | PASSING |
| Fee Reports | `fees/fee-reports.spec.ts` | 16 | SKIPPED (queue timeout) |
| Library | `library/library.spec.ts` | 21 | SKIPPED (queue timeout) |
| Payroll Foundation | `payroll/payroll.spec.ts` | 19 | SKIPPED (queue timeout) |
| Payroll Processing | `payroll/payroll-processing.spec.ts` | 14 | SKIPPED (queue timeout) |
| Payroll Validation | `payroll/payroll-validation.spec.ts` | 20 | SKIPPED (queue timeout) |
| Payslip | `payroll/payroll-payslip.spec.ts` | 1 | SKIPPED (queue timeout) |

---

## Recommendations

1. **Start Laravel dev server** before running tests: `php artisan serve --port=8000`
2. **Update credentials** in `ask-erp-mvp.spec.ts` to match test user (currently uses `superadmin@school.com` / `password`)
3. **Increase timeout** for full suite (all 162 tests need >5 min with 1 worker)
4. **Use 2+ workers** for independent test files (remove `fullyParallel: false` constraint where possible)
