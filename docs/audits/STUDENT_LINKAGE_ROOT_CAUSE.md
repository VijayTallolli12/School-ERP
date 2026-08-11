# Student Linkage — Root Cause Analysis

> Status: Root cause identified
> Scope: School ERP backend only (no mobile app changes)

---

## 1. Symptom

A Student App user logs in successfully, but the app sometimes displays:

> **"No student linked to this account."**

This is a backend-side linkage failure, not a frontend defect.

---

## 2. Authentication Flow (as implemented)

```
POST /api/v1/student/login
   └─ StudentAppController::login()
        ├─ authenticate User by email/password            (works)
        ├─ $user->student  (hasOne)                        ← THE FAILURE POINT
        │     └─ if null → 404 "Student profile not found."
        ├─ resolve school_id from SchoolContext            (was NULL — fixed previously)
        ├─ create Sanctum token + permission abilities
        └─ return token + user + student + school_id
```

Every authenticated student endpoint then calls `StudentAppController::resolveStudent()`:

```php
private function resolveStudent(): Student
{
    $student = request()->user()->student;       // hasOne, not school-scoped
    if (! $student) {
        abort($this->notFound('Student profile not found.')->getStatusCode());  // message LOST
    }
    return $student;
}
```

---

## 3. Root Causes

### RCA-1 — `$user->student` returns `null` when the linkage is broken (primary cause)

`User::student()` is a plain `hasOne(Student::class)` keyed purely on `students.user_id`. It returns `null` whenever:

- `students.user_id` is `NULL` (student record exists but was never linked to a User), or
- `students.user_id` points to a different / non-existent / soft-deleted User.

**There is no fallback, no school-scoped lookup, and no self-healing.** When this happens the API cannot tell the app *who* the student is, and the app renders "No student linked to this account."

### RCA-2 — The 404 message is silently discarded

`resolveStudent()` calls `abort($this->notFound(...)->getStatusCode())`, i.e. `abort(404)`. The **message argument is never passed**, so Laravel emits a bare HTTP 404 (`{"message":"Not Found"}`). A client that needs to distinguish "no student" from "unknown route" sees an opaque error — hence a generic "No student linked" string on the client side.

### RCA-3 — Soft-deleted / inactive Student records are not distinguished

`hasOne` respects soft deletes, so a soft-deleted `students` row also yields `null` with no explanation. An `inactive` student is likewise not surfaced with a role-appropriate error.

### RCA-4 — Missing `school_user` pivot rows (data model gap)

The demo/golden seeder creates student Users and assigns the `Student` role, but **never attaches the User to the school via the `school_user` pivot**. The audit confirms zero pivot rows for all 12 student users. School context therefore depends entirely on `users.current_school_id` + `model_has_roles`. If `current_school_id` is ever `NULL`, resolution can fail and `SetSchoolContext` aborts with `403 "Unable to resolve your school context."` — another confusing, non-specific failure.

### RCA-5 — Student lookup is not school-scoped

`$user->student` ignores school. If a user were ever linked to a Student row in the wrong school, the mismatch would not be detected or corrected.

### RCA-6 — Login response omits role / academic context / permissions / branding

The login payload returns only `token`, `user`, `student`, `school_id`. A client cannot tell which academic year / class / section the student is in, what permissions the token carries, or what branding to apply — without making additional API calls.

---

## 4. Live Data Audit Summary (MySQL demo dataset)

| Check | Result |
|-------|--------|
| Schools | 1 (DEMO, active) |
| Active academic year | 1 (`2026-2027`, `is_active=true`) |
| Student-role users | 12 (ids 7–18, all active) |
| Student records | 12 (ids 1–12, all `user_id` populated, none soft-deleted) |
| Student sessions | 12 (all `status=active`, correct school/year) |
| Guardian–student pivot rows | 4 (parents ↔ students 1–4) |
| Orphan student records (`user_id` null) | **0** |
| Broken FK (missing user) | **0** |
| Duplicate `user_id` links | **0** |
| Orphan student users (role, no student) | **0** |
| Student records whose user lacks the role | **0** |
| Cross-school mismatches | **0** |
| `school_user` pivot rows for student users | **0** ← data gap |

The live dataset is currently consistent, but nothing *enforces or repairs* it — any of the above can regress without a mechanism to detect/fix it.

---

## 5. Fix Strategy

1. **`StudentAuthService`** — single, school-scoped resolver that walks
   `User → Student → Active Session → Class → Section → School → Permissions`
   and **self-heals** broken `students.user_id` linkage and missing `school_user` pivot rows when a repair is unambiguous.
2. **`StudentLinkageException`** — typed exception carrying a meaningful message + HTTP status (404 for missing linkage, 403 for inactive).
3. **`EnsureStudentLinked` middleware** — guards all `student/*` routes and returns a **JSON 404 with a meaningful message** (never a bare 404, never a 500).
4. **Login response** — includes `role`, `permissions`, `student_uuid`, `student_id`, `school_id`, `academic_year`, `class`, `section`, `branding`, and the student profile, so no extra API call is needed to identify the logged-in student.
5. **`BrandingService`** — extracted from `BrandingController` (keeps the same safe, never-localhost URL behavior) so the login response can include branding.
6. **Seeder repair** — attach student/teacher/parent users to the `school_user` pivot in the golden seeder.
7. **Tests + reports** — cover missing, broken, wrong-school, inactive, no-session and orphan scenarios.

---

## 6. Files Touched

| File | Change |
|------|--------|
| `app/Modules/Students/Services/StudentAuthService.php` | **New** — resolver + self-healing |
| `app/Modules/Students/Exceptions/StudentLinkageException.php` | **New** — typed exception |
| `app/Http/Middleware/EnsureStudentLinked.php` | **New** — 404 guard for student routes |
| `app/Modules/Settings/Services/BrandingService.php` | **New** — branding payload builder (URL-safe) |
| `app/Http/Controllers/Api/V1/StudentAppController.php` | Use service; full login response |
| `app/Http/Controllers/Api/V1/BrandingController.php` | Delegate to `BrandingService` (behavior unchanged) |
| `routes/modules/api/student-app.php` | Add `student.linked` middleware to group |
| `routes/modules/api.php` | Register middleware alias if needed |
| `bootstrap/app.php` | Register `student.linked` alias |
| `database/seeders/Golden/GoldenSchoolSeeder.php` | Attach users to `school_user` pivot |
| `tests/Feature/StudentLinkageApiTest.php` | **New** — linkage test matrix |
