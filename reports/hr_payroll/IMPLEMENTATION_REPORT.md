# Implementation Report — Phase 04 (HR Payroll Workflow)

## Phase Information
- **Phase Name:** HR Payroll Workflow
- **Phase Number:** 04
- **Objective:** Build HR management module and enhance Payroll infrastructure with employee records, document verification, contract tracking, and payroll configuration.

## New Files Created (20+)

### Models
| File | Description |
|------|-------------|
| `app/Modules/HR/Models/Employee.php` | Core employee model with polymorphic morph map `employee`, BelongsToSchool, SoftDeletes, relationships for department, designation, reportingTo, contracts, documents |
| `app/Modules/HR/Models/EmployeeContract.php` | Contract tracking with statuses (active/expired/terminated), probation periods, notice periods |
| `app/Modules/HR/Models/EmployeeDocument.php` | Document uploads with verification workflow (pending/verified/rejected) |
| `app/Modules/Payroll/Models/PayrollSetting.php` | School-scoped payroll configuration (currency, salary day, PF/ESI/professional tax rates) |

### Controllers
| File | Description |
|------|-------------|
| `app/Modules/HR/Controllers/EmployeeController.php` | CRUD + DataTable for employees |
| `app/Modules/HR/Controllers/EmployeeDocumentController.php` | CRUD + DataTable + verify action for documents |

### Services & Repositories
| File | Description |
|------|-------------|
| `app/Modules/HR/Services/EmployeeService.php` | Business logic layer for employee operations |
| `app/Modules/HR/Repositories/EmployeeRepository.php` | Data access layer implementation |
| `app/Modules/HR/Repositories/EmployeeRepositoryInterface.php` | Repository contract |

### Requests
| File | Description |
|------|-------------|
| `app/Modules/HR/Requests/StoreEmployeeRequest.php` | Validation for employee creation |
| `app/Modules/HR/Requests/UpdateEmployeeRequest.php` | Validation for employee updates |
| `app/Modules/HR/Requests/StoreEmployeeDocumentRequest.php` | Validation for document uploads |

### Policies
| File | Description |
|------|-------------|
| `app/Modules/HR/Policies/EmployeePolicy.php` | Gates: viewAny/view, create, update, delete |
| `app/Modules/HR/Policies/EmployeeDocumentPolicy.php` | Gates: viewAny/view, create, update, delete, verify |

### Dashboard
| File | Description |
|------|-------------|
| `app/Modules/Dashboard/Services/Builders/HRDashboardBuilder.php` | HR-specific dashboard stat cards, widgets, quick actions |
| `app/Modules/Dashboard/Services/DataCollectors/HRCollector.php` | Cached data collectors for HR dashboard metrics |

### Service Providers
| File | Description |
|------|-------------|
| `app/Modules/HR/Providers/HRServiceProvider.php` | Module service registration |
| `app/Modules/HR/Providers/HRRouteServiceProvider.php` | Module route registration |

### Routes
| File | Description |
|------|-------------|
| `routes/modules/hr.php` | All HR routes grouped under `prefix/hr`, gated by `permission:hr.view` |

### Database
| File | Description |
|------|-------------|
| `database/migrations/2026_07_07_000001_create_hr_tables.php` | Creates 4 tables: employees, employee_contracts, employee_documents, payroll_settings |

## Files Modified

| File | Change |
|------|--------|
| `database/seeders/PermissionSeeder.php` | Added `hr.*` permissions (`view`, `create`, `update`, `delete`, `verify`), created `HR` role with hr + teacher perms, updated `Payroll Manager` role with payslip perms |
| `app/Providers/AppServiceProvider.php` | Bound `EmployeeRepositoryInterface` to `EmployeeRepository`, registered `Employee` & `EmployeeDocument` policies, added `employee` morph map entry |
| `app/Modules/Dashboard/Services/SidebarBuilder.php` | Added `buildForHR()` method returning HR-specific sidebar items |
| `resources/views/layouts/partials/sidebar.blade.php` | Added `@elseif(auth()->user()->hasRole('HR'))` block with Employees, Documents, Dashboard links |
| `routes/web.php` | Added `require __DIR__.'/modules/hr.php';` in admin group |
| `app/Modules/Dashboard/Services/DashboardFactory.php` | Added `'HR' => HRDashboardBuilder::class` to ROLE_PRIORITY mapping |

## Database Changes

| Table | Columns |
|-------|---------|
| `employees` | 40+ columns including personal info, bank details, PF/ESI numbers, employment type/status, department/designation/reporting FKs, soft deletes |
| `employee_contracts` | contract_type, start_date, end_date, probation_period_months, notice_period_days, documents_json, status, soft deletes |
| `employee_documents` | document_type, document_name, document_number, file_path, verified_at, verified_by, status (pending/verified/rejected), remarks, soft deletes |
| `payroll_settings` | payroll_currency, salary_day, enable_professional_tax, enable_provident_fund, enable_esi, PF/ESI shares, professional_tax_monthly, overtime_rate_multiplier, pay_period |

## Architecture Decisions

- **Module structure:** HR module follows existing module patterns (Models, Controllers, Policies, Repositories, Services, Requests, Providers)
- **Polymorphic relationship:** Employee registered in Laravel morphMap as `employee` for future polymorphic integrations
- **BelongsToSchool trait:** All HR models (Employee, EmployeeContract, EmployeeDocument) and PayrollSetting use school-scoped multi-tenancy
- **Soft deletes:** All 4 new tables implement `softDeletes()` for data retention and recovery
- **DataTables:** Employee and Document listings use Yajra DataTables with eager loading for department, designation, and verifier
- **Dashboard caching:** HRCollector uses Cache facade with 300-second TTL; dashboard key pattern `dashboard.hr.*`
- **Repository pattern:** EmployeeRepository implements EmployeeRepositoryInterface for testability and swap-ability
- **Permission-driven:** Every route gated via `permission` middleware; policies mirror permission checks at model level
