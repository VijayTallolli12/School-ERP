# Policy Report — Phase 04 (HR Payroll Workflow)

## New Policies

### EmployeePolicy (`app/Modules/HR/Policies/EmployeePolicy.php`)

| Method | Permission Check | Description |
|--------|-----------------|-------------|
| `viewAny(User $user)` | `$user->can('hr.view')` | List all employees |
| `view(User $user, Employee $employee)` | `$user->can('hr.view')` | View single employee |
| `create(User $user)` | `$user->can('hr.create')` | Create new employee |
| `update(User $user, Employee $employee)` | `$user->can('hr.update')` | Update existing employee |
| `delete(User $user, Employee $employee)` | `$user->can('hr.delete')` | Delete (soft) employee |

### EmployeeDocumentPolicy (`app/Modules/HR/Policies/EmployeeDocumentPolicy.php`)

| Method | Permission Check | Description |
|--------|-----------------|-------------|
| `viewAny(User $user)` | `$user->can('hr.view')` | List all documents |
| `view(User $user, EmployeeDocument $document)` | `$user->can('hr.view')` | View single document |
| `create(User $user)` | `$user->can('hr.create')` | Upload document |
| `update(User $user, EmployeeDocument $document)` | `$user->can('hr.update')` | Update document |
| `delete(User $user, EmployeeDocument $document)` | `$user->can('hr.delete')` | Delete document |
| `verify(User $user, EmployeeDocument $document)` | `$user->can('hr.verify')` | Verify/reject document |

## Permissions Created

| Permission | Module | Guard |
|-----------|--------|-------|
| `hr.view` | hr | web |
| `hr.create` | hr | web |
| `hr.update` | hr | web |
| `hr.delete` | hr | web |
| `hr.verify` | hr | web |
| `payroll.payslip.view` | payroll.payslip | web |
| `payroll.payslip.generate` | payroll.payslip | web |
| `payroll.payslip.export` | payroll.payslip | web |

## Roles Updated

### HR Role (New)
**Source:** `database/seeders/PermissionSeeder.php:118-120`
```
HR: dashboard.view, teachers.view, teachers.create, teachers.update, teachers.reports,
    reports.view, hr.view, hr.create, hr.update, hr.delete, hr.verify
```

### Payroll Manager Role (Updated)
**Source:** `database/seeders/PermissionSeeder.php:116`
```
Payroll Manager: dashboard.view, payroll.view, payroll.create, payroll.update,
                  payroll.delete, payroll.export, payroll.process, payroll.lock,
                  payroll.payslip.view, payroll.payslip.generate, payroll.payslip.export,
                  reports.view
```

## Policy Registration
**Source:** `app/Providers/AppServiceProvider.php:243-244`
```php
Gate::policy(Employee::class, EmployeePolicy::class);
Gate::policy(EmployeeDocument::class, EmployeeDocumentPolicy::class);
```

Both are registered in `AppServiceProvider::boot()` alongside all other module policies.

## Authorization Flow
1. **Route middleware:** `middleware('permission:hr.view')` on the group, specific perms on individual routes (`hr.create`, `hr.update`, `hr.delete`, `hr.verify`)
2. **Policy gates:** `$this->authorize('delete', $employee)` in controller `destroy()` and `destroy()`/`verify()` for documents
3. **Super Admin bypass:** `Gate::before()` in `AppServiceProvider` allows Super Admin to bypass all policy checks
