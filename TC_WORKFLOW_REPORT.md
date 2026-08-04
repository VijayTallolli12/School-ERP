# TC WORKFLOW REPORT

Workflow 2 (Student Lifecycle) - Transfer Certificate (TC)
Status: **Completed**

## Scope

Implemented and verified the **Transfer Certificate (TC)** workflow only. Reused the
existing `StudentTransfer` model and `StudentLifecycleService`. Student Promotion and
Transfer Student workflows were **not** modified. No unrelated modules were inspected.

## Implementation Summary

The TC workflow was already wired in the Lifecycle module; verification confirmed it is
complete and correct. **No production code changes and no new migration were required** —
the `student_transfers` table already carries all TC fields (`tc_no`, `tc_issued_on`,
`conduct`, `destination_school`, `reason`).

### Reused assets

| Asset | Path | Purpose |
| --- | --- | --- |
| Model | `app/Modules/Lifecycle/Models/StudentTransfer.php` | TC records (`transfer_type = tc`, `status = issued`, TC number/date, conduct, destination school). |
| Service | `app/Modules/Lifecycle/Services/StudentLifecycleService.php` | `issueTc()` closes the active session, marks student `transferred`, persists the TC row, auto-generates the TC number (`TC-<year>-NNNN`) when omitted, notifies guardians, logs the event. |
| Controller | `app/Modules/Lifecycle/Controllers/StudentLifecycleController.php` | `issueTc()` endpoint + `printTc()` printable view. |
| Request | `app/Modules/Lifecycle/Requests/IssueTcRequest.php` | Validates student belongs to the current school; guards `student_lifecycle.tc`. |
| Routes | `routes/modules/lifecycle.php` | `POST lifecycle/tc` and `GET lifecycle/tc/{transfer}/print` with `student_lifecycle.tc` permission. |
| Views | `resources/views/modules/lifecycle/index.blade.php`, `_actions.blade.php`, `tc-print.blade.php` | TC issue modal, per-row Print TC action, printable certificate. |

### Workflow behavior

1. Admin selects a student and optionally supplies TC No, leaving date, issued-on date,
   conduct, destination school and reason.
2. `StudentLifecycleService::issueTc()`:
   - Closes the active session: `status = transferred`, `left_on = transferred_on`.
   - Sets student `status = transferred`.
   - Creates a `StudentTransfer` row (`transfer_type = tc`, `status = issued`).
   - Auto-generates `TC-<year>-NNNN` when `tc_no` is blank (skips existing numbers).
   - Sends an in-app notification to guardians.
   - Records an activity-log entry.
3. The response returns `transfer_id` and the generated `tc_no`.
4. The lifecycle table shows the TC row with a **Print TC** action rendering the
   printable certificate (`tc-print.blade.php`).

## Files Added

| File | Reason |
| --- | --- |
| `tests/Feature/StudentLifecycleTcTest.php` | TC-specific feature tests (none existed previously). |

## Test Results

Command: `php artisan test tests/Feature/StudentLifecycleTcTest.php`

| Test | Result |
| --- | --- |
| school admin can issue tc for student | PASS |
| tc auto generates unique number when not provided | PASS |
| tc closes active session and records left on | PASS |
| tc requires valid student | PASS |
| tc print page renders | PASS |

**5 passed, 18 assertions.**

## Notes

- No migration was created; all TC fields already exist on `student_transfers`.
- Promotion and Transfer workflows were untouched and their code paths were not changed.
