# Security Report — Phase 04 (HR Payroll Workflow)

## Data Isolation via School ID
- **Mechanism:** `BelongsToSchool` trait applied to all HR models: `Employee`, `EmployeeContract`, `EmployeeDocument`, and `PayrollSetting`
- **Effect:** Every table includes a `school_id` foreign key cascading on delete, ensuring strict data separation between schools in the multi-tenant environment
- **Enforcement:** The `school` middleware ensures the authenticated user's school context is applied to all queries within the admin group

## Role-Based Access Control (RBAC)

### HR Role
- Users with the `HR` role receive a dedicated sidebar (Dashboard, Employees, Documents, Notifications)
- `HRDashboardBuilder` provides role-specific dashboard stat cards and quick actions
- The `DashboardFactory::ROLE_PRIORITY` maps `HR` to `HRDashboardBuilder`, ensuring HR users get the correct dashboard

### Permission Hierarchy
| Permission | Action |
|------------|--------|
| `hr.view` | View employees and documents |
| `hr.create` | Create employees and upload documents |
| `hr.update` | Update employee records and documents |
| `hr.delete` | Delete employees and documents |
| `hr.verify` | Verify employee documents |

## Defense in Depth: Route + Policy Gates

### Layer 1: Route Middleware
- Group-level `middleware('permission:hr.view')` prevents unauthorized access to all HR routes
- Individual routes have additional `middleware('permission:hr.*')`:
  - `hr.create` on POST routes
  - `hr.update` on PUT routes
  - `hr.delete` on DELETE routes
  - `hr.verify` on document verify route

### Layer 2: Policy Gates
- `EmployeePolicy` and `EmployeeDocumentPolicy` perform identical checks at the model level
- Controller methods explicitly authorize via `$this->authorize('delete', $employee)` in `destroy()`
- Document `verify()` method calls `$this->authorize('verify', $document)`

### Layer 3: Super Admin Bypass
- `AppServiceProvider::boot()` registers `Gate::before()` allowing Super Admin to bypass all policy checks

## Document Verification Security
- Verification requires the `hr.verify` permission (separate from `hr.update`)
- Verification sets `verified_at` timestamp and `verified_by` user ID for audit trail
- Only one endpoint (`POST /admin/hr/documents/{document}/verify`) can change status to `verified`

## Soft Deletes
- All 4 HR/payroll tables (`employees`, `employee_contracts`, `employee_documents`, `payroll_settings`) use `softDeletes()`
- Data is never permanently removed via the API endpoints
- Enables audit trail and data recovery

## Data Sensitivity
- Employee records contain PII (name, DOB, address, phone, bank account details, PAN, PF/ESI numbers)
- Access to this data is restricted to users with `hr.view` permission
- Document files stored in `hr/documents/` under the `public` disk — file access should be further restricted via signed URLs or middleware in production

## Sidebar Security
- Sidebar items conditionally rendered based on role and permissions:
  - Blade: `@can('hr.view')` wraps Employees and Documents links
  - SidebarBuilder: `$this->item(...)` checks `hr.view` permission before including items
- Non-HR roles (Teacher, Principal, default) do not see HR links

## Migration Constraints
- All foreign keys use `cascadeOnDelete()` for school-scoped cleanup when a school is removed
- `nullOnDelete()` for user references (`created_by`, `updated_by`, `verified_by`) to preserve records if users are deleted
