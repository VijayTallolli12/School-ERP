# Policy Report - Phase 02: Teacher Experience Refactor

## Overview
This report documents all authorization Policies that enforce teacher data isolation and role-based access in the Teacher Experience Refactor. Policies were pre-existing and unchanged structurally during Phase 02, but their enforcement is now critical to the refactored teacher experience.

---

## 1. AttendancePolicy

| Field | Value |
|-------|-------|
| **File** | `app/Modules/Attendance/Policies/AttendancePolicy.php` |
| **Model** | `App\Modules\Attendance\Models\Attendance` |
| **Role Check** | `$user->hasRole('Teacher')` |

### Methods and Rules

| Method | Teacher Rule | Non-Teacher Rule |
|--------|-------------|------------------|
| `viewAny()` | Requires `attendance.view` permission | Same |
| `view()` | Requires `attendance.view` AND `class_section_id` in teacher's assigned class sections | Requires `attendance.view` |
| `create()` | Requires `attendance.create` AND `class_section_id` in teacher's assigned class sections | Requires `attendance.create` |
| `update()` | Requires `attendance.update` AND `class_section_id` in teacher's assigned class sections | Requires `attendance.update` |
| `delete()` | Requires `attendance.delete` AND `class_section_id` in teacher's assigned class sections | Requires `attendance.delete` |

**Ownership Rule**: Class-section ownership (teacher must be assigned to the class section).

---

## 2. HomeworkPolicy

| Field | Value |
|-------|-------|
| **File** | `app/Modules/Homework/Policies/HomeworkPolicy.php` |
| **Model** | `App\Modules\Homework\Models\Homework` |
| **Role Check** | `$user->hasRole('Teacher')` |

### Methods and Rules

| Method | Teacher Rule | Non-Teacher Rule |
|--------|-------------|------------------|
| `viewAny()` | Requires `homework.view` permission | Same |
| `view()` | Requires `homework.view` AND `class_section_id` in teacher's assigned class sections | Requires `homework.view` |
| `create()` | Requires `homework.create` permission | Same |
| `update()` | Requires `homework.update` AND `created_by === $user->id` | Requires `homework.update` |
| `delete()` | Requires `homework.delete` AND `created_by === $user->id` | Requires `homework.delete` |

**Ownership Rule**: Created-by ownership (teacher must be the creator).

---

## 3. ExamPolicy

| Field | Value |
|-------|-------|
| **File** | `app/Modules/Exams/Policies/ExamPolicy.php` |
| **Model** | `App\Modules\Exams\Models\Exam` |
| **Role Check** | `$user->hasRole('Teacher')` |

### Methods and Rules

| Method | Teacher Rule | Non-Teacher Rule |
|--------|-------------|------------------|
| `viewAny()` | Requires `exams.view` permission | Same |
| `view()` | Requires `exams.view` AND `class_section_id` in teacher's assigned class sections | Requires `exams.view` |
| `create()` | Requires `exams.create` AND `class_section_id` in teacher's assigned class sections | Requires `exams.create` |
| `update()` | Requires `exams.update` AND `class_section_id` in teacher's assigned class sections | Requires `exams.update` |
| `delete()` | **ALWAYS FALSE** - teachers cannot delete exams | Requires `exams.delete` |
| `publish()` | **ALWAYS FALSE** - teachers cannot publish exam results | Requires `exams.publish` |

**Ownership Rule**: Class-section ownership; delete and publish are hard-blocked for Teacher role.

---

## 4. LeaveRequestPolicy

| Field | Value |
|-------|-------|
| **File** | `app/Modules/Leave/Policies/LeaveRequestPolicy.php` |
| **Model** | `App\Modules\Leave\Models\LeaveRequest` |
| **Role Check** | `$user->hasRole('Teacher')` |

### Methods and Rules

| Method | Teacher Rule | Non-Teacher Rule |
|--------|-------------|------------------|
| `viewAny()` | Requires `leave_management.view` OR `leave_management.create` | Requires `leave_management.view` |
| `view()` | `$leaveRequest->user_id === $user->id` (own requests only) | Requires `leave_management.view` |
| `create()` | **ALWAYS TRUE** - any teacher can apply | Requires `leave_management.create` |
| `update()` | Requires `leave_management.update` | Same |
| `delete()` | `$leaveRequest->user_id === $user->id` AND `status === 'pending'` | Requires `leave_management.delete` |
| `approve()` | **ALWAYS FALSE** - teachers cannot approve/reject | Requires `leave_management.approve` |

**Ownership Rule**: User ownership for view/delete; approval hard-blocked for Teacher role.

---

## 5. StudentPolicy

| Field | Value |
|-------|-------|
| **File** | `app/Modules/Students/Policies/StudentPolicy.php` |
| **Model** | `App\Modules\Students\Models\Student` |
| **Role Check** | `$user->hasRole('Teacher')` |

### Methods and Rules

| Method | Teacher Rule | Non-Teacher Rule |
|--------|-------------|------------------|
| `viewAny()` | Requires `students.view` permission | Same |
| `view()` | Requires `students.view` AND student has active session in teacher's assigned class sections | Requires `students.view` |
| `create()` | Requires `students.create` | Same |
| `update()` | Requires `students.update` AND student has active session in teacher's assigned class sections | Requires `students.update` |
| `delete()` | Requires `students.delete` AND student has active session in teacher's assigned class sections | Requires `students.delete` |

**Ownership Rule**: Assignment-based (student must be enrolled in teacher's class section via active sessions).

---

## 6. TeacherDocumentPolicy

| Field | Value |
|-------|-------|
| **File** | `app/Modules/Documents/Policies/TeacherDocumentPolicy.php` |
| **Model** | `App\Modules\Teachers\Models\TeacherDocument` |
| **Role Check** | `$user->hasRole('Teacher')` |

### Methods and Rules

| Method | Teacher Rule | Non-Teacher Rule |
|--------|-------------|------------------|
| `viewAny()` | **ALWAYS TRUE** (teacher sees own documents via controller scoping) | Requires `teacher_documents.view` |
| `view()` | `$document->teacher_id === $teacher->id` (own documents only) | Requires `teacher_documents.view` |
| `create()` | Requires `teacher_documents.create` | Same |
| `update()` | Requires `teacher_documents.update` | Same |
| `delete()` | Requires `teacher_documents.delete` | Same |

**Ownership Rule**: Teacher ID ownership for view; CRUD operations require admin permissions.

---

## 7. PayrollPolicy

| Field | Value |
|-------|-------|
| **File** | `app/Modules/Payroll/Policies/PayrollPolicy.php` |
| **Model** | Multiple models (PayrollDepartment, PayrollDesignation, SalaryComponent, PayGrade, EmployeeSalaryStructure, PayrollRun, PayrollItem, EmployeePayslip) |
| **Role Check** | No explicit role check; permission-based |

### Methods and Rules

| Method | Description |
|--------|-------------|
| `viewAny()` | Requires `payroll.view` |
| `create()` | Requires `payroll.create` |
| `update()` | Requires `payroll.update` |
| `delete()` | Requires `payroll.delete` |
| `process()` | Requires `payroll.process` |
| `lock()` | Requires `payroll.lock` |
| `export()` | Requires `payroll.export` |
| `payslipView()` | Requires `payroll.payslip.view` |
| `payslipGenerate()` | Requires `payroll.payslip.generate` |
| `payslipExport()` | Requires `payroll.payslip.export` |

**Note**: Teacher self-service payslip routes bypass `payroll.view` and use controller-level scoping instead.

---

## 8. TeacherPolicy

| Field | Value |
|-------|-------|
| **File** | `app/Modules/Teachers/Policies/TeacherPolicy.php` |
| **Model** | `App\Modules\Teachers\Models\Teacher` |
| **Role Check** | None (permission-gated only) |

### Methods and Rules

| Method | Rule |
|--------|------|
| `viewAny()` | Requires `teachers.view` |
| `view()` | Requires `teachers.view` |
| `create()` | Requires `teachers.create` |
| `update()` | Requires `teachers.update` |
| `delete()` | Requires `teachers.delete` |

**Note**: Teachers do not have `teachers.view` permission, so they cannot view other teachers.

---

## 9. DocumentPolicy (Student Documents)

| Field | Value |
|-------|-------|
| **File** | `app/Modules/Documents/Policies/DocumentPolicy.php` |
| **Model** | `App\Modules\Students\Models\StudentDocument` |
| **Role Check** | None (permission + school_id check) |

### Methods and Rules
All methods require the corresponding permission (`student_documents.view/create/update/delete/verify`) AND `$document->school_id === $user->current_school_id` for tenant isolation.

---

## Policy Matrix Summary

| Policy | Model | Ownership Rule | Assignment Check | Hard Blocked for Teacher |
|--------|-------|---------------|-----------------|--------------------------|
| AttendancePolicy | Attendance | Class-section | `classSections->pluck('id')` | None |
| HomeworkPolicy | Homework | Created-by | `created_by === $user->id` | None |
| ExamPolicy | Exam | Class-section | `classSections->pluck('id')` | `delete()`, `publish()` |
| LeaveRequestPolicy | LeaveRequest | User ID | `user_id === $user->id` | `approve()` |
| StudentPolicy | Student | Assignment-based | Active session in class section | None |
| TeacherDocumentPolicy | TeacherDocument | Teacher ID | `teacher_id === $teacher->id` | CRUD (no create/update/delete for teachers) |
| PayrollPolicy | Multiple | Permission-based | N/A | Read-only via self-service routes |
| TeacherPolicy | Teacher | Permission-gated | N/A | No `teachers.view` for Teacher role |

---

## Policy Registration

All policies are registered in `app/Providers/AppServiceProvider.php` (lines 196-234):
```php
Gate::policy(Attendance::class, AttendancePolicy::class);
Gate::policy(Teacher::class, TeacherPolicy::class);
Gate::policy(Exam::class, ExamPolicy::class);
Gate::policy(Homework::class, HomeworkPolicy::class);
Gate::policy(LeaveRequest::class, LeaveRequestPolicy::class);
Gate::policy(Student::class, StudentPolicy::class);
Gate::policy(TeacherDocument::class, TeacherDocumentPolicy::class);
Gate::policy(StudentDocument::class, DocumentPolicy::class);
// ... plus all other module policies
```
