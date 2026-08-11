# Fees Module

Version: 1.0.0

Revision date: 2026-07-08

## Purpose

The fees module supports fee categories, structures, assignments, collections, receipts, and reporting.

## Architecture

The module uses FeesController, FeeService, FeeReportService, FeeRepository, and fee policies.

## Database Tables

- fee_categories
- fee_structures
- fee_structure_items
- student_fees
- student_fee_items
- fee_receipt_sequences
- fee_payments
- fee_payment_items

## Models

- App\Modules\Fees\Models\FeeCategory
- App\Modules\Fees\Models\FeeStructure
- App\Modules\Fees\Models\StudentFee
- App\Modules\Fees\Models\FeePayment

## Controllers

- FeesController

## Services

- FeeService
- FeeReportService

## Routes

- /admin/fees
- /admin/fees/categories
- /admin/fees/structures
- /admin/fees/assignments
- /admin/fees/collections
- /admin/fees/dues

## Policies

- FeeCategoryPolicy
- FeeStructurePolicy
- StudentFeePolicy
- FeePaymentPolicy

## Permissions

- fees.view
- fees.create
- fees.update
- fees.delete
- fees.collect
- fees.reports

## Business Rules

- Fee structures and assignments are school-scoped.
- Payment collections are recorded and can generate receipts.
- Fee reports depend on the current school context.

## Workflow

1. Configure fee categories and structures.
2. Assign fees to students.
3. Record collections.
4. Generate receipts or reports.

## Common Issues

- Collections may fail if the student fee assignment is missing.
- Receipt generation depends on the configured receipt sequence data.

## Troubleshooting

- Verify the student has a fee assignment before collecting payment.
- Confirm the user has fees.collect permission.
