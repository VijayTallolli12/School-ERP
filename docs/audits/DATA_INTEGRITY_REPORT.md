# Data Integrity Report

**Date:** 2026-08-05
**Application:** School ERP (Laravel)
**Scope:** Full database integrity audit

---

## Summary

A comprehensive data integrity audit was conducted across all database tables. The audit checked for orphan records, duplicate records, broken foreign keys, and incorrect relationships.

---

## Findings

### Critical Issues

| # | Issue | Severity | Status |
|---|-------|----------|--------|
| 1 | 12 orphan `student_guardians` records with invalid `user_id` | Critical | Needs Fix |
| 2 | `fee_payment_items` table lacks `school_id` column (not school-scoped) | High | Needs Fix |
| 3 | `payroll_items` table lacks `school_id` foreign key constraint | High | Needs Fix |
| 4 | `employee_payslips` table lacks `school_id` foreign key constraint | High | Needs Fix |

### Medium Issues

| # | Issue | Severity | Status |
|---|-------|----------|--------|
| 5 | `student_sessions` table has no `class_id` column (uses `class_section_id` instead) | Medium | Informational |
| 6 | `library_issues` table has no `student_id` or `book_id` columns | Medium | Informational |
| 7 | `sessions` table has no `school_id` column | Medium | Informational |
| 8 | `fee_receipt_sequences` table has no `year` or `receipt_number` columns | Medium | Informational |

### Clean Findings

| Check | Result |
|-------|--------|
| Orphan `student_fees` (missing school_id) | 0 |
| Orphan `fee_payments` (missing school_id) | 0 |
| Orphan `attendances` (missing school_id) | 0 |
| Orphan `exam_marks` (missing school_id) | 0 |
| Orphan `exam_results` (missing school_id) | 0 |
| Orphan `student_guardians` (invalid student_id) | 0 |
| Orphan `parent_student` (invalid student_id) | 0 |
| Orphan `parent_student` (invalid parent_id) | 0 |
| Orphan `notifications` (missing school_id) | 0 |
| Orphan `student_sessions` (invalid student_id) | 0 |
| Orphan `teacher_attendances` (invalid teacher_id) | 0 |
| Orphan `teacher_leaves` (invalid teacher_id) | 0 |
| Orphan `transport_assignments` (invalid student_id) | 0 |
| Orphan `library_fine_settings` (missing school_id) | 0 |
| Orphan `payroll_items` (invalid payroll_run_id) | 0 |
| Orphan `employee_payslips` (invalid payroll_run_id) | 0 |
| Orphan `employee_payslips` (invalid payroll_item_id) | 0 |
| Duplicate students (same school, same admission_no) | 0 |
| Duplicate parents (same school, same email) | 0 |
| Duplicate teachers (same school, same email) | 0 |
| Duplicate employee_payslips (same payslip_number) | 0 |

---

## Detailed Findings

### 1. Orphan Student Guardians (Critical)

**12 records** in `student_guardians` reference `user_id` values that do not exist in the `users` table.

**Impact:** These records cannot be displayed or managed through the UI since the associated user no longer exists. They may cause errors when trying to load guardian information.

**Recommended Fix:** Either restore the referenced users or soft-delete the orphaned `student_guardians` records.

### 2. fee_payment_items Missing school_id (High)

The `fee_payment_items` table does not have a `school_id` column, which means it is not school-scoped. This could allow fee payment items from one school to be visible in another school's context.

**Impact:** Multi-tenancy data leak - schools could see each other's fee payment items.

**Recommended Fix:** Add `school_id` column to `fee_payment_items` and backfill existing records.

### 3. payroll_items Missing school_id Foreign Key (High)

The `payroll_items` table has a `school_id` column but lacks a foreign key constraint to the `schools` table.

**Impact:** Referential integrity is not enforced at the database level. Orphan payroll items could exist.

**Recommended Fix:** Add foreign key constraint: `$table->foreignId('school_id')->constrained()->cascadeOnDelete();`

### 4. employee_payslips Missing school_id Foreign Key (High)

The `employee_payslips` table has a `school_id` column but lacks a foreign key constraint to the `schools` table.

**Impact:** Referential integrity is not enforced at the database level. Orphan payslips could exist.

**Recommended Fix:** Add foreign key constraint: `$table->foreignId('school_id')->constrained()->cascadeOnDelete();`

---

## Relationships Verified

| Relationship | Status |
|-------------|--------|
| Student ↔ Parent (via student_guardians) | OK (except 12 orphan user_ids) |
| Student ↔ Guardian (via parent_student) | OK |
| Teacher ↔ User | OK |
| Parent ↔ User | OK |
| Fees (student_fees → fee_payments → fee_payment_items) | OK (fee_payment_items not school-scoped) |
| Attendance | OK |
| Exam Marks | OK |
| Transport | OK |
| Library | OK |
| Notifications | OK |
| Student Sessions | OK |
| Academic Year | OK |
| Payroll Items → Payroll Run | OK |
| Employee Payslips → Payroll Run | OK |
| Employee Payslips → Payroll Item | OK |

---

## Recommendations

1. **Fix orphan student_guardians** - Restore or remove the 12 orphaned records
2. **Add school_id to fee_payment_items** - Add column and backfill existing data
3. **Add foreign key constraints** - Add FK constraints for `payroll_items.school_id` and `employee_payslips.school_id`
4. **Add payroll-specific tests** - Create tests for payroll CRUD, payslip generation, and report exports
5. **Add school_id to sessions table** - For consistency with other school-scoped tables
6. **Add school_id to library_issues** - For consistency with other school-scoped tables

---

## Audit Methodology

- Checked all tables for missing `school_id` columns
- Verified foreign key relationships
- Checked for orphan records (records referencing non-existent parent records)
- Checked for duplicate records (same business key within a school)
- Verified all model relationships match database schema

---

*Report generated as part of Release 1 RC1 Final Production Stabilization*