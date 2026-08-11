# Business Rule Report — Phase 04 (HR Payroll Workflow)

## Rule 1: Employee Codes are School-Scoped Unique
- **Source:** `database/migrations/2026_07_07_000001_create_hr_tables.php:14`
- **Description:** The `employee_code` column in the `employees` table has a `unique()` constraint.
- **Note:** Currently globally unique, not composite with `school_id`. A future migration should add a composite unique index `(school_id, employee_code)` for proper multi-tenant isolation.

## Rule 2: Document Verification Workflow
- **Source:** `app/Modules/HR/Models/EmployeeDocument.php`, `app/Modules/HR/Controllers/EmployeeDocumentController.php:94-109`
- **Description:** Employee documents go through a verification workflow with three statuses:
  - `pending` (default on creation)
  - `verified` (set via `verify()` method which requires `hr.verify` permission)
  - `rejected` (set via `update()` method)
- **Verification action:** Sets `verified_at` timestamp and `verified_by` user ID when verified
- **Permission:** Verification is gated by `hr.verify` permission at both route middleware and policy level

## Rule 3: HR Role Capabilities
- **Source:** `database/seeders/PermissionSeeder.php:118-120`
- **Description:** The `HR` role has the following permissions:
  - `hr.view` — View employee records and documents
  - `hr.create` — Create employees and upload documents
  - `hr.update` — Update employee records and documents
  - `hr.delete` — Delete employees and documents
  - `hr.verify` — Verify employee documents
- Additionally, HR role inherits: `dashboard.view`, `teachers.view`, `teachers.create`, `teachers.update`, `teachers.reports`, `reports.view`

## Rule 4: Payroll Manager Payslip Permissions
- **Source:** `database/seeders/PermissionSeeder.php:116`
- **Description:** The `Payroll Manager` role has been updated with payslip-specific permissions:
  - `payroll.payslip.view` — View payslips
  - `payroll.payslip.generate` — Generate payslips
  - `payroll.payslip.export` — Export payslips
- This extends the existing payroll permissions (view, create, update, delete, export, process, lock).

## Rule 5: Employee Contract Status Tracking
- **Source:** `database/migrations/2026_07_07_000001_create_hr_tables.php:65`
- **Description:** Employee contracts track status with values:
  - `active` — Contract is currently in effect
  - `expired` — Contract end date has passed
  - `terminated` — Contract was ended early
- Default status is `active`. The `contractsExpiringSoon()` collector queries contracts where `end_date` is within the next 30 days and `status = 'active'`.

## Rule 6: Employee-User Relationship
- **Source:** `app/Modules/HR/Models/Employee.php:70-73`
- **Description:** Each employee can be linked to a `User` account via `user()` relationship. This enables login access for employees. The `user_id` FK is implied but managed through the existing `User` model.

## Rule 7: Data Isolation via School ID
- **Source:** `app/Core/Tenant/BelongsToSchool` (trait used by all HR/Payroll models)
- **Description:** All HR models (Employee, EmployeeContract, EmployeeDocument) and PayrollSetting are scoped by `school_id`, ensuring data isolation between schools in the multi-tenant system.

## Rule 8: Soft Deletes for Data Retention
- **Source:** All 4 migration tables
- **Description:** All HR tables use `softDeletes()` for non-destructive removal. Deleted records remain in the database with a `deleted_at` timestamp, allowing recovery and audit trails.
