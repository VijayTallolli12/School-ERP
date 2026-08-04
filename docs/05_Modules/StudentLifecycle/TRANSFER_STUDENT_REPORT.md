# TRANSFER STUDENT REPORT

Workflow 2 (Student Lifecycle) - Transfer Student
Status: **Completed**

## Scope

Implemented and verified the **Transfer Student** workflow only. Reused the existing
`StudentTransfer` model and `StudentLifecycleService`; no unrelated modules were inspected
or modified.

## What Was Already In Place (reused)

| Asset | Path | Purpose |
| --- | --- | --- |
| Model | `app/Modules/Lifecycle/Models/StudentTransfer.php` | Persists transfer records (type `transfer`, status `issued`, from class/year, reason, destination school). |
| Service | `app/Modules/Lifecycle/Services/StudentLifecycleService.php` | `transfer()` closes the active student session, marks the student as `transferred`, writes a `StudentTransfer` row, notifies guardians, and logs the event. |
| Controller | `app/Modules/Lifecycle/Controllers/StudentLifecycleController.php` | `transfer()` endpoint + `searchStudents()` autocomplete. |
| Request | `app/Modules/Lifecycle/Requests/TransferStudentRequest.php` | Validates student belongs to the current school; guards `student_lifecycle.transfer`. |
| Route | `routes/modules/lifecycle.php` | `POST lifecycle/transfer` with `student_lifecycle.transfer` permission. |
| View | `resources/views/modules/lifecycle/index.blade.php` | "Transfer Student" modal + DataTables listing with type filter. |
| Tests | `tests/Feature/StudentLifecycleTransferTest.php` | 5 feature tests covering the transfer workflow. |

## Fix Applied

`database/migrations/2026_08_03_000020_create_student_transfers_table.php`

The academic-year foreign keys used `constrained()` without an explicit table, so Laravel
inferred the wrong table names (`from_academic_years` / `to_academic_years`) instead of the
actual `academic_years` table. Any `student_transfers` insert failed on SQLite with
`no such table: main.to_academic_years`.

```php
// before
$table->foreignId('from_academic_year_id')->nullable()->constrained()->nullOnDelete();
$table->foreignId('to_academic_year_id')->nullable()->constrained()->nullOnDelete();

// after
$table->foreignId('from_academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
$table->foreignId('to_academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
```

This matches the existing explicit `constrained('class_section')` style used in the same
migration. The fix also benefits the Promotion workflow (shared table), but no promotion
behavior was changed.

## Workflow Behavior

1. Admin selects a student (autocomplete limited to the current school).
2. `StudentLifecycleService::transfer()`:
   - Rejects students already `transferred`.
   - Closes the active session: `status = transferred`, `left_on = transferred_on`.
   - Sets student `status = transferred`.
   - Creates a `StudentTransfer` row (`transfer_type = transfer`, `status = issued`)
     with from-class/year, transferred-on date, reason and destination school.
   - Sends an in-app notification to guardians.
   - Records an activity-log entry.
3. Response returns `transfer_id`; the DataTables list refreshes.

## Test Results

Command: `php artisan test tests/Feature/StudentLifecycleTransferTest.php`

| Test | Result |
| --- | --- |
| school admin can view lifecycle index | PASS |
| school admin can transfer a student | PASS |
| transfer closes active session and records left on | PASS |
| already transferred student cannot be transferred again | PASS |
| transfer requires valid student | PASS |

**5 passed, 15 assertions.**

## Notes

- Transfer (out of school) is intentionally distinct from Promotion; it does not create a
  new session.
- TC issuance and Alumni marking remain out of scope for this workflow and were untouched.
