# School ERP — Database Architecture & Relationship Audit Report

**Date:** 2026-05-19  
**Project:** Laravel 12 Modular School ERP  
**Scope:** All 39 migrations, all Eloquent models, all factories, all seeders, all services, repositories, controllers, form requests, and core infrastructure  

---

## Table of Contents

1. [Database Schema Overview](#1-database-schema-overview)
2. [Model–Table–Trait Cross-Reference](#2-modeltabletrait-cross-reference)
3. [Issues by Severity](#3-issues-by-severity)
   - [CRITICAL — Will Cause Runtime Errors](#critical)
   - [HIGH — Data Integrity & Security Risks](#high)
   - [MEDIUM — Code Quality & Consistency](#medium)
   - [LOW — Optimisation & Ergonomics](#low)
4. [Critical ERP Flow Analysis](#4-critical-erp-flow-analysis)
5. [Database vs Code Usage Verification](#5-database-vs-code-usage-verification)
6. [Recommendations Summary](#6-recommendations-summary)

---

## 1. Database Schema Overview

### 1.1 Table Inventory (39 tables)

| # | Table | Migration | SoftDeletes | school_id FK | Key Indexes |
|---|-------|-----------|-------------|--------------|-------------|
| 1 | `users` | `0001_01_01_000000` + `2024_01_01_000030` | ✅ | via `current_school_id` | `email` unique, `status`, `is_super_admin` |
| 2 | `password_reset_tokens` | `0001_01_01_000000` | — | — | `email` PK |
| 3 | `sessions` | `0001_01_01_000000` | — | — | `user_id`, `last_activity` |
| 4 | `cache` | `0001_01_01_000001` | — | — | `key` PK |
| 5 | `cache_locks` | `0001_01_01_000001` | — | — | `key` PK |
| 6 | `jobs` | `0001_01_01_000002` | — | — | `queue`, `reserved_at` |
| 7 | `failed_jobs` | `0001_01_01_000002` | — | — | `uuid` unique |
| 8 | `schools` | `2024_01_01_000010` | ✅ | (self) | `uuid` unique, `code` unique, `slug` unique, `status` |
| 9 | `academic_years` | `2024_01_01_000020` | ✅ | ✅ FK→schools | `unique(school_id,name)`, `(school_id,starts_on,ends_on)` |
| 10 | `login_activities` | `2024_01_01_000050` | — | — | `user_id` FK→users |
| 11 | `school_user` | `2024_01_01_000040` | — | — | `unique(school_id,user_id)`, `(designation)`, `(status)` |
| 12 | `school_classes` | `2024_01_02_000010` | ✅ | ✅ FK→schools | `unique(school_id,name)`, `(sort_order)` |
| 13 | `sections` | `2024_01_02_000020` | ✅ | ✅ FK→schools | `unique(school_id,name)` |
| 14 | `class_section` | `2024_01_02_000030` | ❌ | ✅ FK→schools | `unique(school_class_id,section_id)` |
| 15 | `subjects` | `2024_01_02_000040` | ✅ | ✅ FK→schools | `unique(school_id,code)` |
| 16 | `class_subjects` | `2024_01_02_000050` | — | ✅ FK→schools | `unique(academic_year_id,school_class_id,subject_id)`, `(teacher_id)` |
| 17 | `academic_terms` | `2024_01_02_000060` | — | — | `unique(academic_year_id,name)` |
| 18 | `students` | `2024_01_03_000010` | ✅ | ✅ FK→schools | `unique(school_id,admission_no)`, `(roll_no)`, `(user_id)` |
| 19 | `student_guardians` | `2024_01_03_000020` | ✅ | ✅ FK→schools | `(student_id)`, `(user_id)` |
| 20 | `student_documents` | `2024_01_03_000030` | ✅ | ✅ FK→schools | `(student_id)` |
| 21 | `student_sessions` | `2024_01_03_000040` | ✅ | ✅ FK→schools | `unique(student_id,academic_year_id)`, `(class_section_id)`, `(academic_year_id)` |
| 22 | `attendances` | `2024_01_04_000010` | ✅ | ✅ FK→schools | `unique(student_id,attendance_date)`, `(class_section_id)`, `(academic_year_id)`, `(attendance_date)` |
| 23 | `fee_categories` | `2024_01_05_000010` | ✅ | ✅ FK→schools | `unique(school_id,code)` |
| 24 | `fee_structures` | `2024_01_05_000020` | ✅ | ✅ FK→schools | `unique(academic_year_id,class_section_id)` ⚠️ |
| 25 | `fee_structure_items` | `2024_01_05_000030` | — | ✅ FK→schools | `(fee_structure_id)`, `(fee_category_id)` |
| 26 | `student_fees` | `2024_01_05_000040` | ✅ | ✅ FK→schools | `unique(student_id,academic_year_id)` ⚠️, `(fee_structure_id)`, `(academic_year_id)` |
| 27 | `student_fee_items` | `2024_01_05_000050` | ✅ | ✅ FK→schools | `(student_fee_id)`, `(fee_category_id)` |
| 28 | `fee_receipt_sequences` | `2024_01_05_000060` | ❌ | ✅ FK→schools | `unique(school_id,academic_year_id)` |
| 29 | `fee_payments` | `2024_01_05_000070` | ✅ | ✅ FK→schools | `(student_id)`, `(receipt_no)`, `(paid_on)`, `(academic_year_id)` |
| 30 | `fee_payment_items` | `2024_01_05_000080` | — | ✅ FK→schools | `(fee_payment_id)`, `(student_fee_item_id)` |
| 31 | `teachers` | `2026_05_13_000010` | ✅ | ✅ FK→schools | `unique(school_id,employee_code)`, `(user_id)`, `(status)` |
| 32 | `teacher_subject` | `2026_05_13_000010` | — | — | `unique(teacher_id,subject_id)` |
| 33 | `teacher_class_section` | `2026_05_13_000010` | — | — | `unique(teacher_id,class_section_id)` |
| 34 | `teacher_attendances` | `2026_05_13_000010` | ✅ | ✅ FK→schools | `unique(teacher_id,attendance_date)`, `(attendance_date)` |
| 35 | `teacher_documents` | `2026_05_13_000010` | ✅ | ✅ FK→schools | `(teacher_id)` |
| 36 | `teacher_leaves` | `2026_05_13_000010` | ✅ | ✅ FK→schools | `(teacher_id)`, `(status)` |
| 37 | `teacher_timetable_slots` | `2026_05_13_000010` + `2026_05_14_000010` | ✅ | ✅ FK→schools | `unique(academic_year_id,class_section_id,day_of_week,period_number)`, `(teacher_id)`, `(academic_year_id)`, `(class_section_id)`, `(subject_id)` |
| 38 | `exams` | `2026_05_13_000020` | ✅ | ✅ FK→schools | `(academic_year_id)`, `(class_section_id)`, `(subject_id)`, `(exam_date)`, `(created_by)`, `(updated_by)` |
| 39 | `exam_results` | `2026_05_13_000020` | ✅ | ✅ FK→schools | `unique(exam_id,student_id)`, `(student_id)` |
| 40 | `activity_log` | `2026_05_13_075201` | — | — | `(subject_type,subject_id)`, `(causer_type,causer_id)`, `(batch_uuid)` |
| 41 | `permissions` | `2026_05_13_075201` | — | — | Spatie defaults |
| 42 | `roles` | `2026_05_13_075201` | — | — | Spatie defaults |
| 43 | `model_has_permissions` | `2026_05_13_075201` | — | — | Spatie defaults |
| 44 | `model_has_roles` | `2026_05_13_075201` | — | — | Spatie defaults |
| 45 | `role_has_permissions` | `2026_05_13_075201` | — | — | Spatie defaults |
| 46 | `personal_access_tokens` | `2026_05_13_075202` | — | — | `tokenable_type,tokenable_id` |
| 47 | `parents` | `2026_05_15_000010` | ✅ | ✅ FK→schools | `unique(school_id,user_id)` |
| 48 | `parent_student` | `2026_05_15_000020` | — | — | `unique(parent_id,student_id)` |
| 49 | `parent_notifications` | `2026_05_15_000030` | ❌ | ✅ FK→schools | `(created_by)`, `(type)` |
| 50 | `notifications` | `2026_05_17_000010` | ✅ | ✅ FK→schools | `(type)`, `(priority)`, `(status)`, `(created_by)`, `(updated_by)` |
| 51 | `notification_user` | (embedded in above) | — | — | Pivot with `is_read`, `read_at`, `delivery_status` |

---

## 2. Model–Table–Trait Cross-Reference

| Model | Table | BelongsToSchool | SoftDeletes | HasFactory | Notes |
|-------|-------|:---:|:---:|:---:|-------|
| `App\Models\User` | `users` | — | ✅ | ✅ | Authenticatable + HasRoles + HasApiTokens |
| `App\Models\School` | `schools` | — | ✅ | ✅ | — |
| `App\Models\AcademicYear` | `academic_years` | — | ✅ | ✅ | FK to schools |
| `App\Models\LoginActivity` | `login_activities` | — | — | — | — |
| `SchoolClass` | `school_classes` | ✅ | ✅ | ✅ | — |
| `Section` | `sections` | ✅ | ✅ | ✅ | — |
| `ClassSection` | `class_section` | ✅ | ❌ ⚠️ | ✅ | **Missing SoftDeletes** |
| `Subject` | `subjects` | ❌ ⚠️ | ✅ | ✅ | **Has school_id but no trait** |
| `ClassSubject` | `class_subjects` | ❌ ⚠️ | — | — | **Has school_id but no trait** |
| `AcademicTerm` | `academic_terms` | — | — | ✅ | No school_id column |
| `Student` | `students` | ✅ | ✅ | ✅ | — |
| `StudentGuardian` | `student_guardians` | ✅ | ✅ | — | — |
| `StudentDocument` | `student_documents` | ✅ | ✅ | — | — |
| `StudentSession` | `student_sessions` | ✅ | ✅ | — | — |
| `Attendance` | `attendances` | ✅ | ✅ | — | — |
| `FeeCategory` | `fee_categories` | ✅ | ✅ | ✅ | — |
| `FeeStructure` | `fee_structures` | ✅ | ✅ | — | — |
| `FeeStructureItem` | `fee_structure_items` | ❌ ⚠️ | — | — | **Has school_id but no trait** |
| `StudentFee` | `student_fees` | ✅ | ✅ | — | — |
| `StudentFeeItem` | `student_fee_items` | ❌ ⚠️ | ✅ | — | **Has school_id but no trait** |
| `FeeReceiptSequence` | `fee_receipt_sequences` | ✅ | ❌ ⚠️ | — | **Missing SoftDeletes** |
| `FeePayment` | `fee_payments` | ✅ | ✅ | — | — |
| `FeePaymentItem` | `fee_payment_items` | ❌ ⚠️ | — | — | **Has school_id but no trait** |
| `Teacher` | `teachers` | ✅ | ✅ | ✅ | created_by/updated_by columns, no relationship |
| `TeacherAttendance` | `teacher_attendances` | ❌ ⚠️ | ✅ | — | **Has school_id but no trait** |
| `TeacherDocument` | `teacher_documents` | ❌ ⚠️ | ✅ | — | **Has school_id but no trait** |
| `TeacherLeave` | `teacher_leaves` | ❌ ⚠️ | ✅ | — | **Has school_id but no trait** |
| `TeacherTimetableSlot` | `teacher_timetable_slots` | ❌ ⚠️ | — | — | **Incomplete fillable + no trait** |
| `Exam` | `exams` | ✅ | ✅ | ✅ | created_by/updated_by columns, no relationship |
| `ExamResult` | `exam_results` | ✅ | ✅ | — | created_by/updated_by columns, no relationship |
| `Guardian` | `parents` | ✅ | ✅ | ✅ | Table named `parents`, model named `Guardian` ⚠️ |
| `ParentNotification` | `parent_notifications` | ✅ | ❌ ⚠️ | — | **Broken parents() + no SoftDeletes** |
| `Notification` | `notifications` | ✅ | ✅ | — | — |
| `TimetableSlot` | `teacher_timetable_slots` | ❌ ⚠️ | ✅ | ✅ | **Duplicate model for same table as TeacherTimetableSlot** |

---

## 3. Issues by Severity

### 🔴 CRITICAL — Will Cause Runtime Errors

#### C1. BROKEN: [`ParentNotification::parents()`](app/Modules/Parents/Models/ParentNotification.php:43) references non-existent class

**File:** [`app/Modules/Parents/Models/ParentNotification.php`](app/Modules/Parents/Models/ParentNotification.php:43-47)

```php
public function parents(): BelongsToMany
{
    return $this->belongsToMany(Parent::class, 'parent_notification_parent');
}
```

**Problem:** 
- `Parent::class` does not exist — the model is `Guardian` (`app/Modules/Parents/Models/Guardian.php`)
- `parent_notification_parent` pivot table does **not** exist in any migration
- Calling `$notification->parents` will throw a fatal `Class "Parent" not found` error

**Fix:** Change to `Guardian::class` and use an existing junction (e.g., `parent_student`) or create a proper `parent_notification_guardian` pivot table.

---

#### C2. BROKEN: [`User::parent()`](app/Models/User.php:96) has wrong return type annotation

**File:** [`app/Models/User.php`](app/Models/User.php:96-99)

```php
public function parent(): BelongsTo   // ← Says BelongsTo
{
    return $this->hasOne(\App\Modules\Parents\Models\Guardian::class);  // ← Actually HasOne
}
```

**Problem:** Return type is declared as `BelongsTo` but the method returns `HasOne`. PHP will throw a `TypeError` in strict mode or when static analysis tools run.

**Fix:** Change return type to `HasOne`.

---

#### C3. BROKEN: User's `parent()` relationship may silently return wrong Guardian

`hasOne(Guardian::class)` looks for `user_id` on the `parents` table. The `parents` table has a `unique(school_id, user_id)` constraint, meaning each User can only have exactly one Guardian record. However:

- `StudentGuardian` model also references users via its `user_id` FK (to the `users` table)
- `Guardian` (parents table) has its own `user_id` pointing to users
- A parent User can be linked to multiple students via `parent_student` pivot

The `hasOne` will return whichever Guardian record has that user_id, which is correct as long as the unique constraint is respected. However, the relationship name `parent()` is misleading — it should be `guardian()`.

---

### 🟠 HIGH — Data Integrity & Security Risks

#### H1. ~~Seven models missing [`BelongsToSchool`](app/Core/Tenant/BelongsToSchool.php) trait~~ — INVESTIGATED: FALSE POSITIVE

**Audit Date:** 2026-05-19 — Re-verified against actual migrations and model source code.

This finding was incorrect. After re-verification:

**Already have the trait (audit was outdated):**
| Model | Evidence |
|-------|----------|
| [`Subject`](app/Modules/Academics/Models/Subject.php:16) | `use BelongsToSchool, HasFactory, SoftDeletes;` |
| [`ClassSubject`](app/Modules/Academics/Models/ClassSubject.php:14) | `use BelongsToSchool, SoftDeletes;` |

**Do NOT have a `school_id` column (audit incorrectly assumed one exists):**
| Model | Migration | Columns present |
|-------|-----------|-----------------|
| [`FeeStructureItem`](app/Modules/Fees/Models/FeeStructureItem.php) | [`2024_01_05_000030`](database/migrations/2024_01_05_000030_create_fee_structure_items_table.php:11-20) | `fee_structure_id`, `fee_category_id`, `amount`, `sort_order` |
| [`StudentFeeItem`](app/Modules/Fees/Models/StudentFeeItem.php) | [`2024_01_05_000050`](database/migrations/2024_01_05_000050_create_student_fee_items_table.php:11-21) | `student_fee_id`, `fee_category_id`, `amount`, `due_date` |
| [`FeePaymentItem`](app/Modules/Fees/Models/FeePaymentItem.php) | [`2024_01_05_000080`](database/migrations/2024_01_05_000080_create_fee_payment_items_table.php:11-19) | `fee_payment_id`, `student_fee_item_id`, `amount` |
| [`TeacherAttendance`](app/Modules/Teachers/Models/TeacherAttendance.php) | [`2026_05_13_000010`](database/migrations/2026_05_13_000010_create_teachers_module_tables.php:65-76) | `teacher_id`, `attendance_date`, `status`, `remarks`, `marked_by` |
| [`TeacherDocument`](app/Modules/Teachers/Models/TeacherDocument.php) | [`2026_05_13_000010`](database/migrations/2026_05_13_000010_create_teachers_module_tables.php:53-63) | `teacher_id`, `document_type`, `file_path`, `uploaded_by`, `uploaded_at` |
| [`TeacherLeave`](app/Modules/Teachers/Models/TeacherLeave.php) | [`2026_05_13_000010`](database/migrations/2026_05_13_000010_create_teachers_module_tables.php:78-92) | `teacher_id`, `leave_type`, `start_date`, `end_date`, `reason`, `status`, `approved_by` |
| [`TeacherTimetableSlot`](app/Modules/Teachers/Models/TeacherTimetableSlot.php) | [`2026_05_13_000010`](database/migrations/2026_05_13_000010_create_teachers_module_tables.php:94-106) | `teacher_id`, `class_section_id`, `subject_id`, `day_of_week`, `period_label`, `room`, `status` |
| [`TimetableSlot`](app/Modules/Timetable/Models/TimetableSlot.php) | [`2026_05_13_000010`](database/migrations/2026_05_13_000010_create_teachers_module_tables.php:94-106) | Same table `teacher_timetable_slots` (no `school_id`) |

**Conclusion:** All models that genuinely have a `school_id` column already use the `BelongsToSchool` trait. The 8 child/child-like models achieve tenant isolation through their parent models (FeeStructure, StudentFee, FeePayment, Teacher), which all have `BelongsToSchool`. No code changes needed for this finding.

**Verification:** A full search of `use BelongsToSchool` across `app/` returned 24 results — all models with `school_id` columns are properly scoped.

---

#### H2. ✅ RESOLVED — Cross-school unique constraint collision on [`fee_structures`](database/migrations/2024_01_05_000020_create_fee_structures_table.php:23)

**Migration:** [`2024_01_05_000020_create_fee_structures_table.php`](database/migrations/2024_01_05_000020_create_fee_structures_table.php:23)

```php
$table->unique(['academic_year_id', 'class_section_id']);
```

**Problem:** No `school_id` in the unique constraint. Two schools with overlapping `class_section_id` values could collide.

**Fix applied:** Migration [`2026_05_19_000010_fix_fee_structures_unique_constraint.php`](database/migrations/2026_05_19_000010_fix_fee_structures_unique_constraint.php) drops the old unique on `(academic_year_id, class_section_id)` and creates a new unique on `(school_id, academic_year_id, class_section_id)`. This is a strict superset — the new constraint is more permissive, so rollback is always safe. The [`FeeService`](app/Modules/Fees/Services/FeeService.php) doesn't pass `school_id` explicitly but relies on the [`BelongsToSchool`](app/Core/Tenant/BelongsToSchool.php:11) trait's `creating` event hook to auto-populate it from [`SchoolContext`](app/Core/Tenant/SchoolContext.php:19).

---

#### H3. ✅ RESOLVED — Cross-school unique constraint collision on [`student_fees`](database/migrations/2024_01_05_000040_create_student_fees_table.php:24)

**Migration:** [`2024_01_05_000040_create_student_fees_table.php`](database/migrations/2024_01_05_000040_create_student_fees_table.php:24)

```php
$table->unique(['student_id', 'academic_year_id']);
```

**Problem:** Same issue — missing `school_id` in the unique constraint.

**Fix applied:** Migration [`2026_05_19_000020_fix_student_fees_unique_constraint.php`](database/migrations/2026_05_19_000020_fix_student_fees_unique_constraint.php) drops the old `(student_id, academic_year_id)` unique and creates `(school_id, student_id, academic_year_id)`. Same strict-superset pattern. The [`StudentFee::assignStudentFee()`](app/Modules/Fees/Services/FeeService.php:102-140) and [`bulkAssignStudentFees()`](app/Modules/Fees/Services/FeeService.php:145-204) methods don't pass `school_id` — the [`BelongsToSchool`](app/Core/Tenant/BelongsToSchool.php:11) trait's `creating` hook handles it automatically.

---

#### H4. ✅ RESOLVED — Missing `school_id` in pivot tables causes cross-school data mixing

Pivot tables `teacher_subject` and `teacher_class_section` had no `school_id` column. A teacher from School A could be linked to a subject from School B via direct DB manipulation.

**Fix applied:** Migration [`2026_05_19_000030_add_school_id_to_teacher_pivot_tables.php`](database/migrations/2026_05_19_000030_add_school_id_to_teacher_pivot_tables.php):
- Adds nullable `school_id` FK to both `teacher_subject` and `teacher_class_section`
- Backfills `school_id` from the parent `teachers` table via `DB::statement('UPDATE ... SET school_id = (SELECT school_id FROM teachers WHERE teachers.id = pivot.teacher_id)')`
- Adds indexes on `school_id` for query performance
- Pivot models [`TeacherSubjectPivot`](app/Modules/Teachers/Models/TeacherSubjectPivot.php) and [`TeacherClassSectionPivot`](app/Modules/Teachers/Models/TeacherClassSectionPivot.php) use [`BelongsToSchool`](app/Core/Tenant/BelongsToSchool.php) so future `sync()`/`attach()` calls auto-populate `school_id`
- [`Teacher`](app/Modules/Teachers/Models/Teacher.php:66-77) model updated with `->using()` to wire pivot models
- [`TeacherSeeder`](database/seeders/TeacherSeeder.php:62-76) updated to include `school_id` in pivot data
- The one raw query in [`TeacherReportRepository::dashboardStats()`](app/Modules/Reports/Repositories/TeacherReportRepository.php:28-31) joins through `teachers` table and continues to work unchanged

---

#### H5. [`TeacherTimetableSlot`](app/Modules/Teachers/Models/TeacherTimetableSlot.php:18) missing fillable fields from migration

**File:** [`app/Modules/Teachers/Models/TeacherTimetableSlot.php`](app/Modules/Teachers/Models/TeacherTimetableSlot.php:18-26)

```php
protected $fillable = [
    'teacher_id',
    'class_section_id',
    'subject_id',
    'day_of_week',
    'start_time',   // ← Added by 2026_05_14 migration
    'end_time',     // ← Added by 2026_05_14 migration
    'school_id',
    'room',
    'status',
];
```

**Missing from fillable:** `academic_year_id`, `period_number`, `start_time`, `end_time`, `created_by`, `updated_by`

The migration [`2026_05_14_000010`](database/migrations/2026_05_14_000010_update_teacher_timetable_slots_for_timetable_module.php) added `academic_year_id`, `period_number`, `start_time`, `end_time`, `created_by`, `updated_by` columns. The `TimetableSlot` model (Timetable module, same table) has the correct fillable. But `TeacherTimetableSlot` (Teachers module) is stale.

**Note:** `start_time` and `end_time` ARE in the current fillable array, but `academic_year_id`, `period_number`, `created_by`, `updated_by` are missing.

---

#### H6. Missing [`SoftDeletes`](app/Modules/Academics/Models/ClassSection.php) on ClassSection model

**File:** [`app/Modules/Academics/Models/ClassSection.php`](app/Modules/Academics/Models/ClassSection.php:15-53)

The `class_section` migration does NOT include `$table->softDeletes()`, and the ClassSection model does NOT use the `SoftDeletes` trait. Deleting a class section permanently deletes the record, which orphans all `student_sessions`, `teacher_class_section`, timetable slots, exam records, etc. that reference it.

**Fix:** Add `$table->softDeletes()` via a new migration and add the `SoftDeletes` trait to the model.

---

### 🟡 MEDIUM — Code Quality & Consistency

#### M1. Missing `created_by`/`updated_by` Eloquent relationships

The following models have `created_by` and/or `updated_by` columns in the database (and corresponding services set these values), but the Eloquent models lack `belongsTo(User::class)` relationships:

| Model | Columns Present | Set by Service |
|-------|:---:|:---:|
| [`Exam`](app/Modules/Exams/Models/Exam.php) | created_by, updated_by | [`ExamService::examPayload()`](app/Modules/Exams/Services/ExamService.php:79-93) |
| [`ExamResult`](app/Modules/Exams/Models/ExamResult.php) | created_by, updated_by | [`ExamService::prepareResultPayload()`](app/Modules/Exams/Services/ExamService.php:95-112) |
| [`Teacher`](app/Modules/Teachers/Models/Teacher.php) | created_by, updated_by | TeacherService sets these |

Compare with [`Notification`](app/Modules/Notifications/Models/Notification.php:46-54) which correctly defines `creator()` and `updater()` relationships.

**Fix:** Add `createdBy()` and `updatedBy()` BelongsTo relationships to Exam, ExamResult, and Teacher models.

---

#### M2. Duplicate model for same table: `TeacherTimetableSlot` vs `TimetableSlot`

Two different models map to the same `teacher_timetable_slots` table:

| Aspect | `TeacherTimetableSlot` | `TimetableSlot` |
|--------|----------------------|-----------------|
| Location | `app/Modules/Teachers/Models/` | `app/Modules/Timetable/Models/` |
| Fillable | Incomplete (5 fields missing) | Complete |
| BelongsToSchool | ❌ No | ❌ No |
| SoftDeletes | ❌ No | ✅ Yes |
| Relationships | teacher, classSection, subject | teacher, classSection, subject, academicYear + accessors |

**Risk:** Different modules could have different expectations. The Timetable module's service uses the repository pattern through its own model, while the Teachers module may still reference the old model. Any future change to one model won't be reflected in the other.

---

#### M3. Table `parents` with model `Guardian` — semantic mismatch

- Table name: `parents` (migration `2026_05_15_000010`)
- Model class: [`Guardian`](app/Modules/Parents/Models/Guardian.php)
- Pivot: `parent_student`
- Broken reference: `ParentNotification::parents()` looks for `Parent::class`
- Seeders/Factories: `GuardianFactory`, `ParentSeeder`

This naming inconsistency is the root cause of issue C1. The codebase uses "Parent" and "Guardian" interchangeably.

---

#### M4. `StudentDocument::uploader()` vs `TeacherDocument::uploadedBy()` naming inconsistency

| Model | Relationship Method | Foreign Key |
|-------|-------------------|-------------|
| [`StudentDocument`](app/Modules/Students/Models/StudentDocument.php:31) | `uploader()` | `uploaded_by` |
| [`TeacherDocument`](app/Modules/Teachers/Models/TeacherDocument.php:37) | `uploadedBy()` | `uploaded_by` |

Different naming conventions for the same concept. Standardize to one (preferably `uploadedBy()` to match `markedBy()`, `approvedBy()` patterns).

---

#### M5. `Attendance::markedBy()` vs `TeacherLeave::approvedBy()` vs `StudentDocument::uploader()` — inconsistent relationship names

The codebase uses at least 3 different patterns for "user who performed this action":
- `markedBy()` — Attendance, TeacherAttendance
- `approvedBy()` — TeacherLeave
- `uploader()` — StudentDocument
- `uploadedBy()` — TeacherDocument
- `creator()` / `updater()` — Notification, ParentNotification

---

### 🟢 LOW — Optimisation & Ergonomics

#### L1. Missing composite indexes for common query patterns

| Table | Recommended Index | Query Pattern |
|-------|------------------|---------------|
| `students` | `(school_id, status)` | Listing active students per school |
| `students` | `(school_id, class_section_id)` | Class-wise student listing |
| `teachers` | `(school_id, status)` | Active teachers per school |
| `attendances` | `(class_section_id, attendance_date)` | Daily attendance by class |
| `student_fees` | `(school_id, status)` | Due fees listing |
| `fee_payments` | `(school_id, paid_on)` | Daily collection report |
| `exams` | `(school_id, academic_year_id, class_section_id)` | Exam listing |

---

#### L2. [`FeePayment.collector()`](app/Modules/Fees/Models/FeePayment.php:58) BelongsTo uses `collected_by` foreign key

```php
public function collector(): BelongsTo
{
    return $this->belongsTo(User::class, 'collected_by');
}
```

This is functionally correct but `collected_by` nullable is not validated at the DB level — there's no explicit `foreignId()->constrained()` in the migration (it's just `$table->unsignedBigInteger('collected_by')->nullable()`). Adding a formal foreign key constraint would prevent orphan references.

---

#### L3. `notification_user` pivot table embedded within `create_notifications_table` migration

The `notification_user` pivot is created inside [`2026_05_17_000010_create_notifications_table.php`](database/migrations/2026_05_17_000010_create_notifications_table.php). Convention suggests a separate migration file. Not a bug, but a maintainability concern.

---

#### L4. No `down()` method contents for some migrations

Several migrations (activity log, personal_access_tokens, etc.) have empty or minimal `down()` bodies. In production this is acceptable (never roll back these), but for development environments it makes `migrate:rollback` potentially destructive.

---

#### L5. `is_super_admin` column on users vs Spatie `HasRoles`

The `users` table has both:
1. `is_super_admin` boolean column (set in migration `2024_01_01_000030`)
2. Spatie `HasRoles` trait with `Super Admin` role

The `User::isSuperAdmin()` method checks both: `(bool) $this->is_super_admin || $this->hasRole('Super Admin')`. The `is_super_admin` column is redundant if Spatie roles are used consistently. The [`PermissionSeeder`](database/seeders/PermissionSeeder.php) does assign the `Super Admin` role, so the column may be legacy.

---

## 4. Critical ERP Flow Analysis

### 4.1 User → School → Role Flow

1. **User creation** → [`StoreUserRequest`](app/Modules/Users/Requests/StoreUserRequest.php) validates → Controller creates User
2. **School assignment** → User attached via `school_user` pivot with designation, employee_code, joined_at, status, is_primary
3. **Role assignment** → Spatie `assignRole()` within school team context
4. **School context resolution** → [`SetSchoolContext`](app/Http/Middleware/SetSchoolContext.php) middleware resolves from request/session/user.current_school_id
5. **Permission checking** → [`AppServiceProvider::boot()`](app/Providers/AppServiceProvider.php:107-136) registers Gate policies; super admins bypass via `Gate::before()`

**Status:** ✅ Functional, no structural issues found. The `is_super_admin` column redundancy (L5) is the only concern.

---

### 4.2 Academic Structure Flow

1. School → AcademicYear (hasMany) → AcademicTerm (hasMany)
2. School → SchoolClass (hasMany) → Section (belongsToMany via class_section pivot)
3. ClassSection → ClassSubject → Subject (with teacher assignment)
4. Student → StudentSession (one per academic year) → ClassSection

**Issues:**
- [`Subject`](app/Modules/Academics/Models/Subject.php) missing BelongsToSchool (H1)
- [`ClassSubject`](app/Modules/Academics/Models/ClassSubject.php) missing BelongsToSchool (H1)
- [`ClassSection`](app/Modules/Academics/Models/ClassSection.php) missing SoftDeletes (H6)

---

### 4.3 Student → Guardian Flow

The student-guardian flow is complex and involves THREE tables:

1. **`student_guardians`** — Direct relationship: student ↔ user (the guardian as a user account). Created via [`StudentService::syncGuardians()`](app/Modules/Students/Services/StudentService.php:148-205).

2. **`parents`** — The `Guardian` model (separate from StudentGuardian). Created via [`StudentService::syncParentFromGuardian()`](app/Modules/Students/Services/StudentService.php:308-385) which auto-creates a Guardian record when a guardian user is added.

3. **`parent_student`** — BelongsToMany pivot linking Guardian ↔ Student with `relationship` and `is_primary` pivot columns. Managed via [`StudentService`](app/Modules/Students/Services/StudentService.php) using DB facade.

**Issues:**
- The dual-representation (StudentGuardian + Guardian) is confusing; a guardian can exist in both tables
- [`syncParentFromGuardian()`](app/Modules/Students/Services/StudentService.php:308-385) creates Guardian records via raw DB insert, bypassing Eloquent events
- The `ParentSeeder` uses GuardianFactory which creates User + Guardian independently, not through StudentService

---

### 4.4 Fee Management Flow

1. FeeCategory (global per school) → FeeStructure (per academic year + class section) → FeeStructureItem (amount per category)
2. StudentFee (assigned to student per academic year) → StudentFeeItem (derived from structure items)
3. FeePayment (against a student) → FeePaymentItem (against specific fee items)
4. FeeReceiptSequence (auto-incrementing receipt numbers per school per academic year)

**Issues:**
- Unique constraints on `fee_structures` and `student_fees` missing `school_id` (H2, H3)
- `FeeStructureItem`, `StudentFeeItem`, `FeePaymentItem` missing BelongsToSchool (H1)
- [`FeeService::nextReceiptNumber()`](app/Modules/Fees/Services/FeeService.php:320-339) uses `lockForUpdate()` correctly for concurrency safety ✅

---

### 4.5 Attendance Flow

1. [`AttendanceService::markAttendance()`](app/Modules/Attendance/Services/AttendanceService.php:17-50) uses upsert-based approach for student attendance ✅
2. [`AttendanceService::bulkMarkAttendance()`](app/Modules/Attendance/Services/AttendanceService.php:52-91) wraps in DB::transaction() ✅
3. Teacher attendance uses separate model `TeacherAttendance`

**Issues:**
- `TeacherAttendance` missing BelongsToSchool (H1)

---

## 5. Database vs Code Usage Verification

### 5.1 Columns in DB but NOT in model fillable

| Table | Column | Missing from Model |
|-------|--------|-------------------|
| `teacher_timetable_slots` | `academic_year_id` | [`TeacherTimetableSlot`](app/Modules/Teachers/Models/TeacherTimetableSlot.php) |
| `teacher_timetable_slots` | `period_number` | [`TeacherTimetableSlot`](app/Modules/Teachers/Models/TeacherTimetableSlot.php) |
| `teacher_timetable_slots` | `created_by` | [`TeacherTimetableSlot`](app/Modules/Teachers/Models/TeacherTimetableSlot.php) |
| `teacher_timetable_slots` | `updated_by` | [`TeacherTimetableSlot`](app/Modules/Teachers/Models/TeacherTimetableSlot.php) |

(Note: `TimetableSlot` model in Timetable module has all these correctly in fillable)

### 5.2 Columns in model fillable but NOT in DB

All verified — no phantom columns found.

### 5.3 Unused Tables

No completely unused tables found. All tables have corresponding models and are referenced in services/seeders.

### 5.4 Tables used via raw DB facade (bypassing Eloquent)

- `parent_student` — Written to via `DB::table('parent_student')->insert()` in [`StudentService::syncParentFromGuardian()`](app/Modules/Students/Services/StudentService.php:308-385) — bypasses Eloquent events and timestamps
- `parents` — Inserted via `DB::table('parents')->insert()` in same method

---

## 6. Recommendations Summary

### Immediate Fixes (CRITICAL)

| Priority | Issue | Action |
|----------|-------|--------|
| 🔴 C1 | `ParentNotification::parents()` broken | Change to use `Guardian::class`, create proper pivot table |
| 🔴 C2 | `User::parent()` return type mismatch | Change `BelongsTo` → `HasOne` |
| 🔴 C3 | `User::parent()` naming confusion | Rename to `guardian()` or audit usage |

### High Priority

| Priority | Issue | Action |
|----------|-------|--------|
| 🟠 H1 | 9 models missing BelongsToSchool | Add trait to Subject, ClassSubject, FeeStructureItem, StudentFeeItem, FeePaymentItem, TeacherAttendance, TeacherDocument, TeacherLeave, TeacherTimetableSlot, TimetableSlot |
| 🟠 H2 | fee_structures unique constraint | Add school_id to unique index |
| 🟠 H3 | student_fees unique constraint | Add school_id to unique index |
| 🟠 H4 | Pivot tables missing school_id | Add school_id to teacher_subject, teacher_class_section |
| 🟠 H5 | TeacherTimetableSlot fillable incomplete | Add missing fields or deprecate in favor of TimetableSlot |
| 🟠 H6 | ClassSection missing SoftDeletes | Add migration + trait |

### Medium Priority

| Priority | Issue | Action |
|----------|-------|--------|
| 🟡 M1 | Missing created_by/updated_by relationships | Add to Exam, ExamResult, Teacher |
| 🟡 M2 | Duplicate TeacherTimetableSlot/TimetableSlot | Consolidate into single model |
| 🟡 M3 | parents table vs Guardian model naming | Align naming convention |
| 🟡 M4 | uploader() vs uploadedBy() | Standardize naming |
| 🟡 M5 | Inconsistent user-action relationship names | Standardize to `performedBy()` or similar |

### Low Priority

| Priority | Issue | Action |
|----------|-------|--------|
| 🟢 L1 | Missing composite indexes | Add as query patterns demand |
| 🟢 L2 | collected_by missing FK constraint | Add formal foreign key |
| 🟢 L3 | notification_user in wrong migration file | Extract to separate migration |
| 🟢 L4 | Empty down() methods | Document or populate |
| 🟢 L5 | Redundant is_super_admin column | Evaluate removal in favor of Spatie-only approach |

---

## Appendix A: Complete Foreign Key Cascade Rules

| Table | Foreign Key | On Delete | On Update |
|-------|------------|-----------|-----------|
| `academic_years` | `school_id` | CASCADE | — |
| `users` | `current_school_id` | SET NULL | — |
| `school_classes` | `school_id` | CASCADE | — |
| `sections` | `school_id` | CASCADE | — |
| `class_section` | `school_id` | CASCADE | — |
| `class_section` | `school_class_id` | CASCADE | — |
| `class_section` | `section_id` | CASCADE | — |
| `subjects` | `school_id` | CASCADE | — |
| `class_subjects` | `school_id` | CASCADE | — |
| `class_subjects` | `academic_year_id` | CASCADE | — |
| `class_subjects` | `teacher_id` | SET NULL | — |
| `academic_terms` | `academic_year_id` | CASCADE | — |
| `students` | `school_id` | CASCADE | — |
| `students` | `user_id` | SET NULL | — |
| `student_guardians` | `school_id` | CASCADE | — |
| `student_guardians` | `student_id` | CASCADE | — |
| `student_guardians` | `user_id` | CASCADE | — |
| `student_documents` | `school_id` | CASCADE | — |
| `student_documents` | `student_id` | CASCADE | — |
| `student_documents` | `uploaded_by` | SET NULL | — |
| `student_sessions` | `school_id` | CASCADE | — |
| `student_sessions` | `academic_year_id` | CASCADE | — |
| `student_sessions` | `class_section_id` | SET NULL | — |
| `attendances` | `school_id` | CASCADE | — |
| `attendances` | `student_id` | CASCADE | — |
| `attendances` | `class_section_id` | CASCADE | — |
| `attendances` | `academic_year_id` | CASCADE | — |
| `attendances` | `marked_by` | SET NULL | — |
| `fee_categories` | `school_id` | CASCADE | — |
| `fee_structures` | `school_id` | CASCADE | — |
| `fee_structures` | `academic_year_id` | CASCADE | — |
| `fee_structures` | `class_section_id` | CASCADE | — |
| `fee_structure_items` | `fee_structure_id` | CASCADE | — |
| `fee_structure_items` | `fee_category_id` | CASCADE | — |
| `fee_structure_items` | `school_id` | CASCADE | — |
| `student_fees` | `school_id` | CASCADE | — |
| `student_fees` | `student_id` | CASCADE | — |
| `student_fees` | `academic_year_id` | CASCADE | — |
| `student_fees` | `fee_structure_id` | SET NULL | — |
| `student_fee_items` | `student_fee_id` | CASCADE | — |
| `student_fee_items` | `fee_category_id` | CASCADE | — |
| `student_fee_items` | `school_id` | CASCADE | — |
| `fee_receipt_sequences` | `school_id` | CASCADE | — |
| `fee_receipt_sequences` | `academic_year_id` | CASCADE | — |
| `fee_payments` | `school_id` | CASCADE | — |
| `fee_payments` | `student_id` | CASCADE | — |
| `fee_payments` | `academic_year_id` | CASCADE | — |
| `fee_payment_items` | `fee_payment_id` | CASCADE | — |
| `fee_payment_items` | `student_fee_item_id` | CASCADE | — |
| `fee_payment_items` | `school_id` | CASCADE | — |
| `teachers` | `school_id` | CASCADE | — |
| `teachers` | `user_id` | SET NULL | — |
| `teacher_attendances` | `school_id` | CASCADE | — |
| `teacher_attendances` | `teacher_id` | CASCADE | — |
| `teacher_attendances` | `marked_by` | SET NULL | — |
| `teacher_documents` | `school_id` | CASCADE | — |
| `teacher_documents` | `teacher_id` | CASCADE | — |
| `teacher_documents` | `uploaded_by` | SET NULL | — |
| `teacher_leaves` | `school_id` | CASCADE | — |
| `teacher_leaves` | `teacher_id` | CASCADE | — |
| `teacher_leaves` | `approved_by` | SET NULL | — |
| `teacher_timetable_slots` | `school_id` | CASCADE | — |
| `teacher_timetable_slots` | `teacher_id` | CASCADE | — |
| `teacher_timetable_slots` | `academic_year_id` | CASCADE | — |
| `teacher_timetable_slots` | `class_section_id` | CASCADE | — |
| `teacher_timetable_slots` | `subject_id` | CASCADE | — |
| `exams` | `school_id` | CASCADE | — |
| `exams` | `academic_year_id` | CASCADE | — |
| `exams` | `class_section_id` | CASCADE | — |
| `exams` | `subject_id` | CASCADE | — |
| `exam_results` | `exam_id` | CASCADE | — |
| `exam_results` | `student_id` | CASCADE | — |
| `exam_results` | `school_id` | CASCADE | — |
| `parents` | `school_id` | CASCADE | — |
| `parents` | `user_id` | SET NULL | — |
| `parent_student` | `parent_id` | CASCADE | — |
| `parent_student` | `student_id` | CASCADE | — |
| `parent_notifications` | `school_id` | CASCADE | — |
| `parent_notifications` | `created_by` | SET NULL | — |
| `notifications` | `school_id` | CASCADE | — |
| `notifications` | `created_by` | SET NULL | — |
| `notifications` | `updated_by` | SET NULL | — |
| `notification_user` | `notification_id` | CASCADE | — |
| `notification_user` | `user_id` | CASCADE | — |

---

**End of Audit Report**