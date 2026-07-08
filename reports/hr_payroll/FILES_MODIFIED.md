# Files Modified — Phase 04 (HR Payroll Workflow)

## New Files

### Models
- `app/Modules/HR/Models/Employee.php`
- `app/Modules/HR/Models/EmployeeContract.php`
- `app/Modules/HR/Models/EmployeeDocument.php`
- `app/Modules/Payroll/Models/PayrollSetting.php`

### Controllers
- `app/Modules/HR/Controllers/EmployeeController.php`
- `app/Modules/HR/Controllers/EmployeeDocumentController.php`

### Services & Repositories
- `app/Modules/HR/Services/EmployeeService.php`
- `app/Modules/HR/Repositories/EmployeeRepository.php`
- `app/Modules/HR/Repositories/EmployeeRepositoryInterface.php`

### Requests
- `app/Modules/HR/Requests/StoreEmployeeRequest.php`
- `app/Modules/HR/Requests/UpdateEmployeeRequest.php`
- `app/Modules/HR/Requests/StoreEmployeeDocumentRequest.php`

### Policies
- `app/Modules/HR/Policies/EmployeePolicy.php`
- `app/Modules/HR/Policies/EmployeeDocumentPolicy.php`

### Dashboard
- `app/Modules/Dashboard/Services/Builders/HRDashboardBuilder.php`
- `app/Modules/Dashboard/Services/DataCollectors/HRCollector.php`

### Service Providers
- `app/Modules/HR/Providers/HRServiceProvider.php`
- `app/Modules/HR/Providers/HRRouteServiceProvider.php`

### Routes
- `routes/modules/hr.php`

### Migration
- `database/migrations/2026_07_07_000001_create_hr_tables.php`

## Modified Files

| # | File | Change |
|---|------|--------|
| 1 | `database/seeders/PermissionSeeder.php` | Added `hr` module with permissions `view`, `create`, `update`, `delete`, `verify`; created `HR` role; added `payroll.payslip` permissions (`view`, `generate`, `export`) to permissions list and to `Payroll Manager` role |
| 2 | `app/Providers/AppServiceProvider.php` | Added imports for HR models/policies/repositories; bound `EmployeeRepositoryInterface::class` to `EmployeeRepository::class`; registered `EmployeePolicy` for `Employee::class` and `EmployeeDocumentPolicy` for `EmployeeDocument::class` via `Gate::policy()`; added `'employee' => Employee::class` to morph map |
| 3 | `app/Modules/Dashboard/Services/SidebarBuilder.php` | Added `buildForHR(User $user)` method returning HR sidebar items (Dashboard, Employees, Documents, Notifications); added HR role check in `build()` method |
| 4 | `resources/views/layouts/partials/sidebar.blade.php` | Added `@elseif(auth()->user()->hasRole('HR'))` block with Dashboard, Employees (`admin.hr.employees.*`), Documents (`admin.hr.documents.*`), Notifications, and Ask ERP links |
| 5 | `routes/web.php` | Added `require __DIR__.'/modules/hr.php';` inside the admin route group (line 40) |
| 6 | `app/Modules/Dashboard/Services/DashboardFactory.php` | Added `'HR' => HRDashboardBuilder::class` to `ROLE_PRIORITY` constant (line 21) |
