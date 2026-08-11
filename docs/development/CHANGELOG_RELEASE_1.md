# Changelog — Release 1

**Date:** 2026-08-05
**Application:** School ERP (Laravel)
**Version:** 1.0.0 RC1

---

## Release 1.0.0 RC1 — 2026-08-05

### Added
- Full multi-tenant architecture with `BelongsToSchool` global scope trait
- `SchoolContext` class for resolving school from request/user/session
- `SetSchoolContext` middleware for automatic school context resolution
- Security headers middleware (CSP, HSTS, X-Frame-Options, etc.)
- Payroll module with full CRUD (runs, items, payslips, salary structures)
- Reports module with 7 report types (Attendance, Fee, Student, Teacher, Parent, Exam, AbsentStudent)
- Activity logging via spatie/laravel-activitylog
- Login activity tracking
- AI Assistant module with intent routing
- AI Agents module for automated workflows
- Queue support with database driver (jobs, batches, failed jobs)
- Role-based access control via Spatie Permission with school-based teams
- Student guardian management with parent-student relationships
- Fee structure and payment processing
- Exam management with marks and grades
- Attendance tracking (student and teacher)
- Homework management
- Leave management
- Transport management (routes, vehicles, drivers, assignments)
- Library management (books, issues, fines)
- Calendar module with academic events
- Document management with school-scoped uploads
- Notification system with push and database delivery
- Settings module for school configuration
- HR module for employee management
- Dashboard with role-specific builders (Admin, Principal, Staff, Student, Parent)

### Changed
- PayrollController queries now rely on `BelongsToSchool` global scope (explicit scoping pending)
- `fee_payment_items` table created without `school_id` (migration pending)
- `activity_log` table created without `school_id` (migration pending)
- `employee_payslips` and `payroll_items` have `school_id` without foreign key constraint
- `login_activities.school_id` is nullable
- `.env` configured with `APP_ENV=local` for development
- `MAIL_MAILER=log` for email logging during development
- `DEMO_DATASET=true` for demo data loading
- `TRUSTED_PROXIES=*` for flexible proxy configuration
- `SESSION_SECURE_COOKIE=true` with `APP_URL=http://localhost` (mismatch for dev)

### Fixed
- N+1 query issues in dashboard builders (cached repeated queries)
- DataTable eager loading optimization across StudentRepository, TeacherRepository, AttendanceRepository, FeeRepository
- jQuery QuickForm compatibility issue in Vite build (custom plugin fix)
- Parent password reset functionality
- Parent role assignment
- Super admin role fixes

### Known Issues
- 12 orphan `student_guardians` records with invalid `user_id`
- `fee_payment_items` lacks `school_id` column
- `activity_log` lacks `school_id` column
- `payroll_items` and `employee_payslips` lack foreign key constraints on `school_id`
- No custom error pages
- No payroll-specific tests
- Pre-existing test failures in `AcademicModuleTest` and `FeeWorkflowTest`
- No log rotation strategy
- `MAIL_MAILER=log` not suitable for production
- `GEMINI_API_KEY` hardcoded in `.env`

### Removed
- (None)

### Deprecated
- (None)

---

*Changelog generated as part of Release 1 RC1 Final Production Stabilization*