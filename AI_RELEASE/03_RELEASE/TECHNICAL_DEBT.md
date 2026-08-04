# Technical Debt — Release 1 RC1

**Date:** 2026-08-05
**Application:** School ERP (Laravel)
**Version:** 1.0.0 RC1

---

## Critical Technical Debt

### TD-001: PayrollController School Scoping
- **File:** `app/Modules/Payroll/Controllers/PayrollController.php`
- **Issue:** Queries like `PayrollRun::query()->findOrFail($runId)` and `EmployeePayslip::query()` rely on the `BelongsToSchool` global scope rather than explicit `school_id` checks
- **Risk:** Multi-tenancy data leak if global scope is bypassed or not active
- **Effort:** Medium — add explicit `where('school_id', ...)` to all payroll queries
- **Impact:** High — potential cross-school data exposure

### TD-002: Missing school_id on activity_log Table
- **File:** `database/migrations/2026_05_13_075201_create_activity_log_table.php`
- **Issue:** The `activity_log` table has no `school_id` column
- **Risk:** Activity logs are not tenant-scoped; cross-school data visibility
- **Effort:** Medium — add migration to add `school_id` column, update spatie activitylog config
- **Impact:** High — tenant isolation violation

### TD-003: Missing school_id on fee_payment_items Table
- **File:** `database/migrations/2024_01_05_000080_create_fee_payment_items_table.php`
- **Issue:** The `fee_payment_items` table has no `school_id` column
- **Risk:** Fee payment items are not school-scoped; cross-school data leak
- **Effort:** Medium — add migration to add `school_id` column and backfill
- **Impact:** High — tenant isolation violation

### TD-004: No Custom Error Pages
- **Directory:** `resources/views/errors/` (empty)
- **Issue:** No custom 404, 500, or other error views
- **Risk:** Poor user experience; unbranded error pages
- **Effort:** Low — create Blade templates for each error code
- **Impact:** Medium — UX degradation

### TD-005: Hardcoded GEMINI_API_KEY in .env
- **File:** `.env`
- **Issue:** API key is hardcoded in the environment file
- **Risk:** Key exposure if `.env` is committed to version control
- **Effort:** Low — move to secrets manager or use environment variable injection
- **Impact:** Medium — security risk

## Major Technical Debt

### TD-006: Missing Foreign Key Constraints
- **Tables:** `payroll_items.school_id`, `employee_payslips.school_id`
- **Issue:** No foreign key constraint on `school_id` columns
- **Risk:** Orphan records can exist; referential integrity not enforced
- **Effort:** Low — add `->constrained()->cascadeOnDelete()` to migrations
- **Impact:** Medium — data integrity risk

### TD-007: No Payroll Tests
- **Directory:** `tests/Feature/`, `tests/Unit/`
- **Issue:** Zero test coverage for the payroll module
- **Risk:** Payroll regressions undetected; critical business logic untested
- **Effort:** High — write comprehensive tests for all payroll CRUD operations
- **Impact:** High — no safety net for payroll changes

### TD-008: Pre-Existing Test Failures
- **Tests:** `AcademicModuleTest`, `FeeWorkflowTest`
- **Issue:** These tests fail before this release cycle
- **Risk:** New regressions may be masked by existing failures
- **Effort:** Medium — investigate and fix root causes
- **Impact:** Medium — reduced test reliability

### TD-009: No Log Rotation
- **File:** `storage/logs/laravel.log`
- **Issue:** No log rotation strategy; log file grows indefinitely
- **Risk:** Disk space exhaustion over time
- **Effort:** Low — configure daily log channel or system logrotate
- **Impact:** Medium — operational risk

### TD-010: Mail Driver Set to Log
- **File:** `.env` (`MAIL_MAILER=log`)
- **Issue:** Emails are only logged, not actually sent
- **Risk:** No email notifications delivered in production
- **Effort:** Low — configure SMTP or SES with proper credentials
- **Impact:** Medium — notifications not delivered

## Minor Technical Debt

### TD-011: login_activities.school_id Nullable
- **File:** `database/migrations/2024_01_01_000050_create_login_activities_table.php`
- **Issue:** `school_id` is nullable; inconsistent with other school-scoped tables
- **Effort:** Low — make non-nullable or add constraint
- **Impact:** Low — minor inconsistency

### TD-012: TRUSTED_PROXIES Too Permissive
- **File:** `.env` (`TRUSTED_PROXIES=*`)
- **Issue:** Trusts all proxies; IP spoofing possible
- **Effort:** Low — set to specific proxy IPs
- **Impact:** Low — security hardening

### TD-013: DEMO_DATASET Enabled
- **File:** `.env` (`DEMO_DATASET=true`)
- **Issue:** Demo data present in instance
- **Effort:** Low — set to `false` and clear demo data
- **Impact:** Low — data cleanliness

### TD-014: Empty DB_PASSWORD
- **File:** `.env` (`DB_PASSWORD=`)
- **Issue:** No database password
- **Effort:** Low — set a strong password
- **Impact:** Low — security hardening

### TD-015: APP_ENV=local
- **File:** `.env` (`APP_ENV=local`)
- **Issue:** Environment set to local instead of production
- **Effort:** Low — change to `production`
- **Impact:** Low — configuration correctness

---

*Report generated as part of Release 1 RC1 Final Production Stabilization*