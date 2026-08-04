# STUDENT PROMOTION AUDIT REPORT

**Workflow:** 2 — Student Promotion (`BUSINESS_WORKFLOWS.md`)
**Module:** `app/Modules/Lifecycle`
**Date:** 2026-08-04
**Scope:** Full pipeline audit + critical-gap fixes only (per approved scope: no new schema, no redesign, minimal patches reusing existing architecture).

---

## Summary

The core promotion execution pipeline existed and was transaction-safe. A full audit of the
Workflow 2 chain found the execution step solid but several **critical guards and one
carry-forward (fee structure) were missing**: the target academic year was never validated
(archived/locked or already-ended years were accepted), duplicate roll numbers surfaced as raw
DB constraint errors, non-active students were promotable, and a promoted student's fee
structure for the new academic year was never assigned automatically.

All critical gaps were fixed with minimal patches. **27 tests / 88 assertions pass**
(13 new promotion tests + existing TC/Transfer/Alumni suites), and Pint is clean.

---

## 1. Existing Features

| # | Feature | Location |
| --- | --- | --- |
| 1 | Promotion execution pipeline (transaction-wrapped): school-context assert, duplicate-year-session guard, closes old active session (`status=promoted`, `left_on=today`), creates new active session, keeps `students.status=active`, persists `StudentTransfer` (`transfer_type=promotion`, `status=issued`) | `app/Modules/Lifecycle/Services/StudentLifecycleService.php:20` (`promote()`) |
| 2 | Bulk promotion with per-student partial success (each student in its own transaction; failures collected in `skipped[]`) | `StudentLifecycleService::bulkPromote()` |
| 3 | Single-active-session integrity — DB unique `['school_id','academic_year_id','student_id']` prevents duplicate sessions; promote flips the only active session before creating the new one | `database/migrations/2024_01_03_000040_create_student_sessions_table.php:24` |
| 4 | Roll number handling — manual entry + "prefix to current roll" UI helper | `resources/views/modules/lifecycle/promote.blade.php:29,88-103` |
| 5 | Roll-number uniqueness enforced at DB level `['school_id','academic_year_id','class_section_id','roll_no']` | `student_sessions` migration `:25` |
| 6 | Promotion UI — single-page candidate table, select-all, target year/class dropdowns, per-student roll input, confirm dialog, AJAX submit | `resources/views/modules/lifecycle/promote.blade.php` |
| 7 | Security — routes gated by `permission:student_lifecycle.view` and `permission:student_lifecycle.promote`; request-level `authorize()` re-checks the permission; school scoping via `SchoolContext` on every `Rule::exists(...)->where('school_id', ...)` | `routes/modules/lifecycle.php:9-13`, `app/Modules/Lifecycle/Requests/PromoteStudentsRequest.php:11-14` |
| 8 | Lifecycle register — DataTables list of all transfer types with student/class/TC columns and filters | `StudentLifecycleController::data()` |
| 9 | Parent notification (in-app to linked guardian users) + activity-log event on promotion | `StudentLifecycleService::notifyParents()`, `::log()` |
| 10 | "Do NOT carry" items correctly untouched — attendance, homework, exam marks, leave, fee payments and timetable all remain keyed to their original session/year and are never copied by promotion | (verified: `promote()` touches only `student_sessions`, `students`, `student_transfers`; no `->replicate()` / copy calls) |

## 2. Missing Features (as of pre-fix audit)

| # | Missing | Detail | Status after this pass |
| --- | --- | --- | --- |
| 1 | Promotion **eligibility engine** (Pass / Fail / Repeat) | Exam results carry per-subject `pass/fail` only; nothing aggregates them into a student-level promotion decision and no `promotion_status` is persisted. | **Open** — documented as recommendation |
| 2 | **7-step / multi-step wizard** (criteria → eligible list → teacher feedback → principal review → execution → notification) | Current UI is a single page; no criteria settings, no teacher recommendations, no principal approval, no retain/conditional states. | **Open** |
| 3 | **Current-year lock / close flow** | `academic_years.status` supports `archived` but nothing enforced it; no explicit "close current year" action. | **Partially fixed** — promotion target now rejects archived/ended years |
| 4 | **Carry-forward** (fee structure, transport, hostel, medical, emergency, custom fields) | Profile/parents/documents persist by `student_id` (no copy needed). Fee structure is keyed by `academic_year_id` and was **never** re-assigned. Transport/hostel/medical/emergency/custom-field tables do not exist. | **Partially fixed** — fee-structure auto-assignment added |
| 5 | **Whole-class / multi-class bulk mode** | Only manual multi-select of individual students. | **Open** |
| 6 | **Bulk progress + batch rollback** | Per-student partial success only; no progress UI, no all-or-nothing mode. | **Open** |
| 7 | **Promotion reports** | No summary/register/export/PDF for promotions. | **Open** |
| 8 | **9 validation gates** | No gate checklist exists in code or docs; current gates enumerated below (7 at service/request level). | **Open** (documented) |
| 9 | **Auto roll-number generation** | Only manual/prefix entry; no sequential auto-assign mode. | **Open** |
| 10 | Promotion **test coverage** | No `StudentLifecyclePromotionTest.php` existed. | **Fixed** — 13 tests added |

## 3. Bugs Found

1. **Target-year validation bypassed** — `PromoteStudentsRequest` only checked the year existed for the school; `StudentLifecycleService::promote()` never consulted `status`/dates, so promotion could target an archived (locked) or already-ended year.
2. **Duplicate roll numbers surfaced as DB errors** — `student_sessions_roll_unique` is the only guard; a duplicate roll in the same class+year threw a raw `QueryException` (unfriendly; single-promote callers had no clean message path).
3. **Non-active students promotable** — `promoteIndex()` listed every student with an active session regardless of `students.status`; the service would happily promote a `transferred`/`alumni`/`inactive` student and blindly reset status to `active` (silently corrupting lifecycle state).
4. **Fee structure not carried forward** — a promoted student kept their old-year `student_fees`; the new year's structure required a separate manual accountant step and was easy to miss.
5. **Dead field in request** — `PromoteStudentsRequest` declares a `roll_no` rule that the controller/service never read (only the `roll_numbers` map is used).

## 4. Bugs Fixed

| # | Fix | Location |
| --- | --- | --- |
| 1 | **Target-year lock** — service validates the year belongs to the school, is not `archived`, and has not already ended, throwing a clear `RuntimeException` (defense in depth); request additionally rejects archived years at validation (`whereNot('status', 'archived')`). | `StudentLifecycleService::assertTargetYearPromotable()`, `PromoteStudentsRequest.php:24` |
| 2 | **Friendly roll-duplicate guard** — pre-insert check (mirrors the DB constraint incl. soft-deleted rows) throws a per-student message so bulk promote skips gracefully with the student's name instead of a raw DB error. | `StudentLifecycleService::assertRollNoAvailable()` |
| 3 | **Eligibility guard** — only `active` students may be promoted (rejects transferred/alumni/inactive before any change); the promote candidate list is filtered to active students. | `StudentLifecycleService::assertPromotableStudent()`, `StudentLifecycleController::promoteIndex()` |
| 4 | **Fee-structure carry-forward** — on promotion, the new year's active fee structure for the target class is auto-assigned (with its items) when one exists and no assignment exists for that year; existing assignments are respected and skipped silently. Fee **payments are never copied**. | `StudentLifecycleService::assignFeeStructureForPromotion()` |

## 5. Files Changed

| File | Change |
| --- | --- |
| `app/Modules/Lifecycle/Services/StudentLifecycleService.php` | Added eligibility guard, target-year lock, roll-duplicate guard, fee-structure carry-forward (4 private helpers) + 4 call sites in `promote()` |
| `app/Modules/Lifecycle/Requests/PromoteStudentsRequest.php` | `to_academic_year_id` rule now excludes archived years |
| `app/Modules/Lifecycle/Controllers/StudentLifecycleController.php` | `promoteIndex()` restricts candidates to `status=active`; removed unused imports (Pint) |
| `tests/Feature/StudentLifecyclePromotionTest.php` | **New** — 13 promotion tests (see below) |

## 6. Test Results

Command: `php artisan test --filter=StudentLifecycle`

| Suite | Tests | Assertions | Result |
| --- | --- | --- | --- |
| `StudentLifecyclePromotionTest` (new) | 13 | — | PASS |
| `StudentLifecycleTransferTest` | 5 | — | PASS |
| `StudentLifecycleTcTest` | 5 | — | PASS |
| `StudentLifecycleAlumniTest` | 4 | — | PASS |
| **Total** | **27** | **88** | **PASS** |

New promotion coverage: page renders · list excludes non-active students · happy-path promote (session close + new session + transfer record + single active session) · `left_on` recorded · archived-year rejected (422) · past-year rejected (skipped) · duplicate roll skipped per-student · already-promoted-in-target-year skipped · transferred student ineligible · fee structure auto-assigned when available · fee assignment untouched when no structure exists · unauthenticated redirect · permission denied (403).

Lint: `vendor/bin/pint --test` on all changed files → **passed**.

## 7. Workflow Completeness

**55%**

| Chain item (audit spec) | Status |
| --- | --- |
| Old-session close + new-session create + single-active session | ✅ Implemented (pre-existing) |
| Roll manual + duplicate prevention | ✅ Implemented (dup guard hardened this pass) |
| Fee structure assignment (carry-forward) | ✅ Implemented (this pass) |
| Current-year lock (target-year validation) | ✅ Implemented (this pass) |
| Promotion eligibility (Transfer/TC/Alumni exclusion) | ✅ Implemented (this pass) |
| Promotion eligibility (Pass/Fail/Repeat from results) | ❌ Not implemented |
| 7-step wizard (criteria/teacher/principal review) | ❌ Not implemented |
| Carry-forward transport/hostel/medical/emergency/custom fields | ❌ Not implemented (no tables exist) |
| Bulk modes (whole class / progress / rollback) | ❌ Not implemented |
| Promotion reports | ❌ Not implemented |
| Admin-only security | ✅ Implemented (pre-existing) |

## 8. Production Readiness

**70%**

Strengths: core pipeline is transaction-safe and idempotent per student; school-scoped and permission-gated at route + request level; single-active-session and roll uniqueness enforced by DB constraints; parent notification and audit logging present; full lifecycle test suite green.

Limits: no eligibility engine, no approval workflow, no reports, and no year-close workflow mean the system cannot yet run an end-to-end promotion cycle strictly per `BUSINESS_WORKFLOWS.md` — an operator can execute promotions and the records are sound, but pre-review/approval and reporting must be done outside the system.

## 9. Remaining Recommendations

1. **Eligibility engine** — aggregate published `ExamResult.status` per student per class per year into a promotion decision (`Promote` / `Retain` / `Repeat` / `Conditional`) and persist it; drive the promote candidate list from it. Reuse `ExamReportRepository` pass/fail logic.
2. **Multi-step wizard** — mirror the existing stepper UI in `resources/views/modules/ai-agents/index.blade.php`: criteria → eligible list → teacher feedback → principal review/approval → execution → notification; add retain/conditional messaging to `notifyParents()`.
3. **Whole-class / multi-class bulk mode** with progress feedback and a batch summary; consider an all-or-nothing transaction mode in addition to the existing per-student partial-success mode.
4. **Promotion reports** — promoted/retained/repeated summary by class + year and a printable/exportable register via the existing Reports module.
5. **Academic-year close/lock workflow** — explicit "close current year" action (`status=closed/archived`) with single-open-year enforcement, applied across modules, not just promotion.
6. **Auto roll-number generation** — sequential assignment per class + year with an auto-assign mode alongside manual entry.
7. **Cleanup** — remove the dead `roll_no` field from `PromoteStudentsRequest` and unify single- vs bulk-promote entry points.
8. **Future carry-forward** — when transport/hostel/medical/emergency/custom-field tables are introduced, add per-year carry-forward; continue to **never** copy attendance, homework, exam marks, leave, fee payments, or timetable.
