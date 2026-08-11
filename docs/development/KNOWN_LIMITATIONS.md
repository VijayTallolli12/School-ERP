# Known Limitations — Release 1 RC1

**Date:** 2026-08-05
**Application:** School ERP (Laravel)
**Version:** 1.0.0 RC1

---

## Data Integrity Limitations

### 1. Orphan Student Guardians
- **12 records** in `student_guardians` reference `user_id` values that do not exist in the `users` table
- These records cannot be displayed or managed through the UI
- **Impact:** Guardian information for affected students is inaccessible
- **Workaround:** Restore the referenced users or soft-delete the orphaned records

### 2. fee_payment_items Not School-Scoped
- The `fee_payment_items` table lacks a `school_id` column
- Fee payment items are not isolated by school
- **Impact:** Multi-tenancy data leak — schools could see each other's fee payment items
- **Workaround:** Add `school_id` column and backfill existing records

### 3. payroll_items Missing Foreign Key
- `payroll_items.school_id` has no foreign key constraint to `schools` table
- **Impact:** Referential integrity not enforced at DB level; orphan payroll items could exist
- **Workaround:** Add foreign key constraint

### 4. employee_payslips Missing Foreign Key
- `employee_payslips.school_id` has no foreign key constraint to `schools` table
- **Impact:** Referential integrity not enforced at DB level; orphan payslips could exist
- **Workaround:** Add foreign key constraint

## Tenant Isolation Limitations

### 5. activity_log Lacks school_id
- The `activity_log` table (spatie/laravel-activitylog) has no `school_id` column
- Activity records are not scoped to a school
- **Impact:** Cross-school data visibility in activity logs
- **Workaround:** Add `school_id` column and update the activitylog configuration

### 6. login_activities school_id Nullable
- The `login_activities` table has `school_id` as nullable
- Login records for users without a school association will have NULL school_id
- **Impact:** Inconsistent tenant isolation for login audit trail
- **Workaround:** Make `school_id` non-nullable or add a database constraint

## Testing Limitations

### 7. No Payroll Tests
- The payroll module has zero test coverage
- No test files exist in `tests/Feature/` or `tests/Unit/` for payroll
- **Impact:** Payroll functionality is untested and regressions are undetected
- **Workaround:** Write comprehensive payroll tests

### 8. Pre-Existing Test Failures
- `AcademicModuleTest` fails before this release cycle
- `FeeWorkflowTest` fails before this release cycle
- **Impact:** These failures may mask new regressions
- **Workaround:** Investigate and fix pre-existing failures

## Configuration Limitations

### 9. Mail Driver Set to Log
- `MAIL_MAILER=log` — emails are only logged, not actually sent
- **Impact:** No email notifications are delivered in production
- **Workaround:** Configure `MAIL_MAILER=smtp` or `ses` with proper credentials

### 10. Demo Dataset Enabled
- `DEMO_DATASET=true` — demo data is loaded
- **Impact:** Production instance may contain sample/demo data
- **Workaround:** Set `DEMO_DATASET=false` and clear demo data

### 11. No Log Rotation
- No log rotation strategy is configured
- `storage/logs/laravel.log` grows indefinitely
- **Impact:** Disk space consumption over time
- **Workaround:** Configure log rotation (e.g., `daily` channel, or use `logrotate`)

### 12. No Custom Error Pages
- `resources/views/errors/` is empty
- Users see default Laravel error pages
- **Impact:** Poor user experience for error states; no branded error pages
- **Workaround:** Create custom 404, 500, and other error views

## Security Limitations

### 13. Hardcoded API Key
- `GEMINI_API_KEY` is hardcoded in `.env`
- **Impact:** API key exposed in version control if `.env` is committed
- **Workaround:** Use a secrets manager or environment variable injection

### 14. Empty Database Password
- `DB_PASSWORD` is empty in `.env`
- **Impact:** Database connection uses no password; security risk
- **Workaround:** Set a strong database password

### 15. TrustProxies Too Permissive
- `TRUSTED_PROXIES=*` trusts all proxies
- **Impact:** IP spoofing possible if behind an untrusted proxy
- **Workaround:** Set to specific proxy IPs or CIDR ranges

---

*Report generated as part of Release 1 RC1 Final Production Stabilization*