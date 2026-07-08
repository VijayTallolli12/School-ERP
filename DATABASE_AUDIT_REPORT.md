# DATABASE AUDIT REPORT

**Date:** 2026-07-08
**Tables:** 51
**Models:** 63+
**Migrations:** 66

---

## 🔴 CRITICAL ISSUES

### C1. ParentNotification::parents() References Non-Existent Class
**File:** `app/Modules/Parents/Models/ParentNotification.php:43`
```php
return $this->belongsToMany(Parent::class, 'parent_notification_parent');
```
- `Parent::class` does NOT exist — model is `Guardian`
- Pivot table `parent_notification_parent` does NOT exist
- **Impact:** Calling this relationship throws fatal error

### C2. User::parent() Return Type Mismatch
**File:** `app/Models/User.php:96`
```php
public function parent(): BelongsTo  // WRONG — returns HasOne
{
    return $this->hasOne(Guardian::class);
}
```
- **Impact:** TypeError in strict mode

---

## 🟠 HIGH ISSUES

### H1. TeacherTimetableSlot Missing Fillable Fields
**File:** `app/Modules/Teachers/Models/TeacherTimetableSlot.php:18`
- Missing from fillable: `academic_year_id`, `period_number`, `created_by`, `updated_by`
- **Impact:** Mass assignment fails for these columns through Teachers module

### H2. ClassSection Missing SoftDeletes
**File:** `app/Modules/Academics/Models/ClassSection.php`
- `class_section` table has no `deleted_at` column
- **Impact:** Deleting a class section permanently orphans student_sessions, teacher assignments, timetable slots, exam records

### H3. Duplicate Model: TeacherTimetableSlot vs TimetableSlot
Two models map to the same table with different fillable/relationships:
| Aspect | TeacherTimetableSlot | TimetableSlot |
|--------|---------------------|---------------|
| Location | Teachers module | Timetable module |
| Fillable | Incomplete (5 missing) | Complete |
| BelongsToSchool | Missing | Missing |
| SoftDeletes | Missing | Present |

---

## 🟡 MEDIUM ISSUES

### M1. Table `parents` with Model `Guardian` — Semantic Mismatch
- Table: `parents`
- Model: `Guardian`
- Pivot: `parent_student`
- Seeders/Factories: `GuardianFactory`, `ParentSeeder`
- **Root cause** of broken ParentNotification::parents()

### M2. Missing created_by/updated_by Relationships
Models with `created_by`/`updated_by` columns but no BelongsTo relationships:
- Exam
- ExamResult
- Teacher

### M3. Employee Code Not Composite Unique
- `unique(school_id, employee_code)` needed — currently globally unique
- **Risk:** Cross-school employee code collision

### M4. Inconsistent Relationship Naming
- `uploader()` vs `uploadedBy()` vs `markedBy()` vs `approvedBy()`
- At least 3 different naming patterns for the same concept

---

## 🟢 LOW ISSUES

### L1. Missing Composite Indexes (7 Recommended)
| Table | Index |
|-------|-------|
| students | (school_id, status) |
| students | (school_id, class_section_id) |
| teachers | (school_id, status) |
| attendances | (class_section_id, attendance_date) |
| student_fees | (school_id, status) |
| fee_payments | (school_id, paid_on) |
| exams | (school_id, academic_year_id, class_section_id) |

### L2. FeePayment::collector() Missing Foreign Key
`collected_by` column is nullable unsignedBigInteger — no explicit foreign key constraint

### L3. Empty `down()` Methods
Several migrations have empty or minimal down() — makes rollback potentially destructive

### L4. Redundant `is_super_admin` Column
Both boolean column and Spatie 'Super Admin' role exist — redundant dual system

---

## DATA INTEGRITY VERIFICATION

| Check | Result |
|-------|--------|
| All models with school_id use BelongsToSchool | ✅ 55/63 (8 intentional omissions) |
| Foreign key cascade rules correct | ✅ Verified |
| Unique constraints correct | ✅ 2 previously fixed, employee_code remains |
| Soft deletes on major models | ⚠️ ClassSection, FeeReceiptSequence, ParentNotification missing |
| Migration order verified | ✅ Chronological order |
| No orphan columns in fillable | ✅ |
| Seed data integrity | ⚠️ Report pages empty (no seed data) |

---

## SCHEMA CHANGE RECOMMENDATIONS

| Priority | Change | Migration Required |
|----------|--------|-------------------|
| 🔴 | Fix ParentNotification::parents() relationship | No (code fix) |
| 🔴 | Fix User::parent() return type | No (code fix) |
| 🟠 | Add SoftDeletes to class_section | Yes |
| 🟠 | Add composite unique (school_id, employee_code) | Yes |
| 🟡 | Add created_by/updated_by relationships | No (code fix) |
| 🟡 | Consolidate TeacherTimetableSlot/TimetableSlot | Yes (deprecate one) |
| 🟢 | Add 7 recommended composite indexes | Yes |
