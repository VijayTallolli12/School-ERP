# Payroll Module

Version: 1.0.0

Revision date: 2026-07-08

## Purpose

The payroll module manages payroll processing, payroll runs, and payslip generation for employees.

## Architecture

The module is implemented through PayrollController, PayrollService, PayrollRepository, and payroll policies.

## Database Tables

- payroll_runs
- payroll_processing_tables
- employee_payslips
- employees

## Models

- App\Modules\Payroll\Models\PayrollRun
- App\Modules\Payroll\Models\EmployeePayslip

## Controllers

- PayrollController

## Services

- PayrollService

## Routes

- /admin/payroll

## Policies

- PayrollPolicy
- PayrollDepartmentPolicy

## Permissions

- payroll.view
- payroll.create
- payroll.update
- payroll.delete

## Business Rules

- Payroll runs are tied to employee and school context data.
- Payslip generation and payroll processing are permission-protected.

## Workflow

1. Payroll is prepared for the relevant period.
2. Runs and payslips are generated.
3. Results are available to administrators and HR users.

## Common Issues

- Payroll generation may fail when employee setup is incomplete.
- Missing permissions can block payroll actions.

## Troubleshooting

- Verify employee and salary-related data exists.
- Check the active school context before generating payroll.
