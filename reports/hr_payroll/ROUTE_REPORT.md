# Route Report — Phase 04 (HR Payroll Workflow)

## Route File
`routes/modules/hr.php` — Loaded via `require __DIR__.'/modules/hr.php';` in `routes/web.php:40`

## Route Group Configuration
- **Prefix:** `admin/hr`
- **Name prefix:** `admin.hr.`
- **Middleware:** `auth`, `school`, `permission:hr.view` (group-level)
- All routes are within the existing `web.php` admin group which applies `auth` and `school` middleware

## Employee Routes (`admin.hr.employees.*`)

| Method | URI | Name | Middleware | Controller Method |
|--------|-----|------|------------|-------------------|
| GET | `/admin/hr` | `admin.hr.index` | `permission:hr.view` | `EmployeeController::index` |
| GET | `/admin/hr/data` | `admin.hr.data` | `permission:hr.view` | `EmployeeController::data` |
| POST | `/admin/hr` | `admin.hr.store` | `permission:hr.create` | `EmployeeController::store` |
| GET | `/admin/hr/{employee}` | `admin.hr.show` | `permission:hr.view` | `EmployeeController::show` |
| PUT | `/admin/hr/{employee}` | `admin.hr.update` | `permission:hr.update` | `EmployeeController::update` |
| DELETE | `/admin/hr/{employee}` | `admin.hr.destroy` | `permission:hr.delete` | `EmployeeController::destroy` |

## Document Routes (`admin.hr.documents.*`)

| Method | URI | Name | Middleware | Controller Method |
|--------|-----|------|------------|-------------------|
| GET | `/admin/hr/documents` | `admin.hr.documents.index` | `permission:hr.view` | `EmployeeDocumentController::index` |
| GET | `/admin/hr/documents/data` | `admin.hr.documents.data` | `permission:hr.view` | `EmployeeDocumentController::data` |
| POST | `/admin/hr/documents` | `admin.hr.documents.store` | `permission:hr.create` | `EmployeeDocumentController::store` |
| GET | `/admin/hr/documents/{document}` | `admin.hr.documents.show` | `permission:hr.view` | `EmployeeDocumentController::show` |
| PUT | `/admin/hr/documents/{document}` | `admin.hr.documents.update` | `permission:hr.update` | `EmployeeDocumentController::update` |
| DELETE | `/admin/hr/documents/{document}` | `admin.hr.documents.destroy` | `permission:hr.delete` | `EmployeeDocumentController::destroy` |
| POST | `/admin/hr/documents/{document}/verify` | `admin.hr.documents.verify` | `permission:hr.verify` | `EmployeeDocumentController::verify` |

## Route Ordering Notes
- Static sub-routes (`documents`, `documents/data`, `documents/store`, etc.) are defined **before** the wildcard `{employee}` routes to prevent route collision
- The `{employee}` wildcard routes (`show`, `update`, `destroy`) are placed last in the group

## Summary
- **Total new routes:** 13 (6 employee + 7 document)
- **All gated by permission middleware:** True
- **Group middleware:** `permission:hr.view` (base access)
- **Individual route permissions:** `hr.create`, `hr.update`, `hr.delete`, `hr.verify` applied per action
