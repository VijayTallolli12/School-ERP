# PARENT WORKFLOW — HIGH PRIORITY FIXES REPORT

Status: **Completed**
Scope: Only the critical-priority fixes from `PARENT_WORKFLOW_REVIEW.md` (Workflow 4 - Parent Workflow). Medium/low-priority items were **not** modified.

## Files Modified

| File | Change |
| --- | --- |
| `app/Http/Controllers/Api/V1/ParentApiController.php` | IDOR/ownership guard (`resolveParent`) applied to every `{uuid}` endpoint with admin bypass; `changeParentPassword` now revokes other tokens and clears `force_password_change`. |
| `routes/modules/api/parents.php` | Self-service routes re-gated from `permission:parents.view` to `permission:dashboard.view`; `parents.index` remains admin-only (`parents.view`). |
| `database/seeders/PermissionSeeder.php` | Removed `parents.view` from the `Parent` role. |
| `app/Modules/Parents/Controllers/ParentController.php` | `viewAny` authorization on `index()`/`data()`; school scoping on the admin `data()` query. |
| `app/Modules/Parents/Models/Guardian.php` | `notifications()` repointed from the never-written `parent_notifications` table to the generic `notifications` table via the `notification_user` pivot. |
| `app/Modules/Parents/Models/ParentNotification.php` | `parents()` all-targeting (`target_parents` empty) case fixed to return all school guardians. |
| `app/Modules/Parents/Services/ParentService.php` | Dashboard notifications feed filtered to parent-targeted, sent rows from the generic table. |
| `tests/Feature/ParentWorkflowApiTest.php` | **New** focused test suite (14 tests). |

No other files were modified.

## Fixes Implemented

### 1. IDOR / ownership guard — ParentApiController
Every `{uuid}` endpoint previously resolved the parent by UUID only (`Guardian::query()->where('uuid', $uuid)->first()`) and scoped children to that parent, **never verifying the caller owns it** — any authenticated user with a parent permission could read another parent's profile, children, attendance, fees, exams, etc.

- Added `resolveParent(string $uuid, array $with = [])` — resolves the parent record and returns it only when the caller owns it (`$parent->user_id === auth()->id()`) **or** holds an admin role (`isSuperAdmin` / School Admin / Principal / HR). Non-owned or non-existent records return `null` → the existing `notFound('Parent not found.')` response (anti-enumeration: no `403`/200 distinction).
- Applied to all 19 `{uuid}` endpoints: `show`, `children`, `dashboard`, `childAttendance`, `childFees`, `childExamResults`, `childTimetable`, `childHomework`, `childCalendar`, `childDocuments`, `childCirculars`, `childCircularDetail`, `markCircularRead`, `childLeaveRequests`, `storeLeaveRequest`, `updateLeaveRequest`, `showLeaveRequest`, `updateParentProfile`, `changeParentPassword`.
- `index()` (admin listing) is unchanged in behavior but now unreachable by Parent-role users (see #2).

### 2. Wrong permission grants — Parent role
`parents.view` was granted to the `Parent` role, which (a) made the admin `parents.index` + web CRUD enumerable by parents, and (b) gated parent self-service API routes.

- Removed `parents.view` from the `Parent` role in `PermissionSeeder` — parents keep only genuine self-service permissions (`dashboard.view`, `attendance.view`, `fees.view`, `exams.view`, `timetable.view`, `homework.view`, `academic_calendar.view`, `student_documents.view`, `notifications.view`, `leave_management.view/create`).
- Because the self-service API routes (`show`, `children`, `update`, `change-password`) were previously gated by `parents.view`, they were re-gated to `permission:dashboard.view` (held by the Parent role). Combined with `resolveParent`, a parent can now only reach their own record. `parents.index` remains `permission:parents.view` → admin-only.

### 3. School scoping + authorization — ParentController (web admin)
- `index()` and `data()` now call `$this->authorize('viewAny', Guardian::class)` (policy = `parents.view`), defense-in-depth on top of the route gate.
- `data()` now scopes the query to `auth()->user()->school_id` (matches `index()`'s existing student filter), preventing cross-school enumeration.

### 4. Password-change hardening — changeParentPassword
Mirrors the teacher-app pattern (`TeacherAppController::changePassword`):
- After saving the new password, revokes **all other** Sanctum tokens (`tokens()->where('id', '!=', $currentTokenId)->delete()`), keeping only the current session's token.
- Clears `force_password_change` (`User::force_password_change` boolean column).
- Token id read from `$request->user()` (the authenticated instance carrying the access token), not the freshly-loaded `$parent->user` instance.

### 5. Notification-source unification
The web portal (`ParentController::notifications`, `ParentService::getParentDashboardData`) read `Guardian::notifications()` → the `parent_notifications` table, which is **never written** (0 rows) — the portal was always empty while the parent app API correctly used the generic `notifications` table (`target_type = 'parents'`, pivot `notification_user`).

- `Guardian::notifications()` now returns a `BelongsToMany` to `Notification` via `notification_user` (using `user_id` as the local key) — the same feed the API surfaces.
- Both call sites filter `target_type = 'parents'` + `status = 'sent'`.

### 6. ParentNotification::parents() bug
The docblock states null `target_parents` = all guardians in the school, but the method returned `Guardian::query()->whereRaw('1 = 0')` (always empty). Now returns all school-scoped guardians when `target_parents` is empty; specific targets are additionally scoped by `school_id`.

## Tests Run (relevant only)

| Command | Result |
| --- | --- |
| `php artisan test --filter=ParentWorkflowApiTest` | **14 passed** (31 assertions) |
| `php artisan test --filter=FeeApiSmokeTest` | **4 passed** (9 assertions) — regression check on guardian fee flows |

Coverage highlights in the new suite:
- Guardian can view own profile; blocked (404) from another parent's profile/children/child-attendance.
- Admin (School Admin) can view any parent profile.
- Guardian blocked (403) from `parents.index`; admin can enumerate.
- `change-password` rejects wrong current password; on success revokes other tokens (deleted token → 401, current token → 200) and clears `force_password_change`.
- Parent-role user blocked (403) from web admin `parents.data`.
- Web portal notifications now render rows from the generic `notifications` table.

## Notes
- No migration required.
- `php -l` clean on all modified files.
- No debug scaffolding remains in the modified files.
- During test development it was confirmed that the token-revocation assertion needs `flushHeaders()` + guard/session reset between requests because the shared Laravel test app instance persists auth/session state across requests; each request now simulates an independent production request.
