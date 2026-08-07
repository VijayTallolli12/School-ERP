# Student Linkage Repair Report

> Self-healing applied to broken student linkage — School ERP backend only

---

## 1. What Was Repaired

### A. Broken `students.user_id` linkage (runtime, automatic)

**Problem:** `$user->student` returned `null` whenever `students.user_id` was NULL or pointed elsewhere. The app then displayed "No student linked to this account."

**Fix:** `App\Modules\Students\Services\StudentAuthService` now:
- Resolves the student through a deterministic chain (direct link → safe self-heal).
- **Self-heals** when a user holds the `Student` role and exactly **one** active, unclaimed student record in the same school matches the user's full name (case-insensitive). The `user_id` is restored and the repair is logged.
- **Does NOT auto-claim** when zero or multiple candidates match (ambiguity is logged, never guessed).
- Detects and reports soft-deleted (`archived`) and `inactive` student records explicitly.

### B. Missing `school_user` pivot rows (runtime + seeder + one-time repair)

**Problem:** Seeded student users had no `school_user` pivot row.

**Fix:**
- `StudentAuthService::repairSchoolPivot()` restores the pivot on login/request when absent.
- `GoldenSchoolSeeder` now attaches every user it creates to the school pivot.
- One-time live repair attached 15 missing pivot rows for the demo school.

---

## 2. Repair Decision Rules (safe by design)

| Scenario | Action |
|----------|--------|
| Exactly 1 unclaimed student matching name + role + school | ✅ Auto-repair `user_id` |
| 0 candidates | ❌ No repair → HTTP 404 "No student is linked to this account." |
| 2+ candidates | ❌ No repair (ambiguous) → HTTP 404, ambiguous message + log |
| Student soft-deleted | ❌ No repair → HTTP 404 "archived" message |
| Student `status != active` | ❌ No repair → HTTP 403 "not active" message |
| Missing `school_user` pivot | ✅ Auto-repair (unambiguous by construction) |

Every automatic repair is written to the application log with the affected `user_id`, `student_id`, and `school_id`.

---

## 3. Live Repairs Applied

| Item | Count |
|------|-------|
| `school_user` pivot rows attached (one-time script) | 15 |
| `school_user` pivot rows attached via runtime self-heal (user 7 login) | 1 |
| `students.user_id` repairs (live) | 0 (live data was consistent) — mechanism covered by tests |

---

## 4. Code Added for Repair

| File | Responsibility |
|------|----------------|
| `app/Modules/Students/Services/StudentAuthService.php` | Resolution + self-healing (linkage + pivot) |
| `app/Modules/Students/Exceptions/StudentLinkageException.php` | Typed errors (404/403) with client-safe messages |
| `app/Http/Middleware/EnsureStudentLinked.php` | Guards student routes; returns JSON 404/403 |
| `app/Http/Controllers/Api/V1/StudentAppController.php` | Uses the service in `login()` and `resolveStudent()` |
| `database/seeders/Golden/GoldenSchoolSeeder.php` | Creates `school_user` pivot rows for all users |

---

## 5. Tests Proving Self-Healing

| Test | Result |
|------|--------|
| Broken `user_id` link self-healed on login | ✅ |
| Broken `user_id` link self-healed by middleware on an authenticated request | ✅ |
| Missing `school_user` pivot self-healed on login | ✅ |
| Ambiguous linkage returns 404 (no guess) | ✅ |
| No candidate returns 404 "No student is linked to this account." | ✅ |
| Inactive student returns 403 | ✅ |
| Soft-deleted student returns 404 (archived) | ✅ |

All 199 tests pass (720 assertions).
