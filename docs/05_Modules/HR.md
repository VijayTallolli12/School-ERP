# HR Module

Version: 1.0.0

Revision date: 2026-07-08

## Purpose

The HR module manages employee records and employee documents.

## Architecture

Implemented via EmployeeController, EmployeeDocumentController, EmployeeService, EmployeeRepository, and HR policies.

## Database Tables

- employees
- employee_documents

## Models

- App\Modules\HR\Models\Employee
- App\Modules\HR\Models\EmployeeDocument

## Controllers

- EmployeeController
- EmployeeDocumentController

## Services

- EmployeeService

## Routes

- /admin/hr
- /admin/hr/documents

## Policies

- EmployeePolicy
- EmployeeDocumentPolicy

## Permissions

- hr.view
- hr.create
- hr.update
- hr.delete
- hr.verify

## Business Rules

- Employee and document records are stored within the active school context.
- Document verification is permission-protected.

## Workflow

1. Create or update employee details.
2. Upload and manage employee documents.
3. Verify documents where permitted.

## Common Issues

- Permission errors can block document verification or employee updates.
- Missing employee records can disrupt HR workflows.

## Troubleshooting

- Confirm the employee has a valid profile and school assignment.
- Validate role and permissions.
