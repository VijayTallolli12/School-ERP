# API / Data Synchronization Audit — School ERP

> Generated: 2026-08-11
> Scope: Laravel ERP backend (`/api/v1`) → Mobile app (`mobile/`) end-to-end data chain.
> Verdict: **NOT PRODUCTION READY** — see §20 Remaining Issues (blocked by live-DB verification and app-completeness, not by the fixes in this pass).

---

## 1. Executive Summary

The School ERP backend exposes a complete, well-structured `/api/v1` surface
(Sanctum auth, Spatie team-scoped permissions, tenant school context, unified
`{ success, message, data }` envelope) for all four roles: Student, Parent,
Teacher and Driver. The automated test suite now passes **243/243 tests**.

The **mobile side of this repository is a skeleton app** (`mobile/`) — the four
role-specific apps documented in `docs/architecture` are not present in this
repository. The skeleton had three critical, app-breaking defects:

1. **Hardcoded API base URL** — `http://localhost:8000/api/v1/branding` baked
   into `BrandingService.ts` (CONFIG_FAIL).
2. **No authentication at all** — the Login button had no submit handler, no
   token storage, no `/me` call (AUTH_FAIL).
3. **Placeholder data** — Profile screen showed hardcoded "Profile Name"/"Role";
   the app could not render any real data (MOCK/PLACEHOLDER FAIL).

Additionally, the mobile app **never compiled**: missing `@react-navigation/native-stack`
dependency, missing `App.tsx` root entry, missing `Image` imports in two screens,
and `drawerContent` placed inside `screenOptions` (ignored at runtime).

Backend-side, the recent "Data Seeder" rewrite changed the canonical admin
credentials from `admin@example.com` / `superadmin@example.com` to
`@school.com`, which contradicted all documentation and tests and broke **6
tests**; on a live database `updateOrCreate` keyed on email would have created
duplicate admin users.

This pass fixed all of the above at the root cause, added a cross-role mobile
contract test, and validated typecheck / lint / Metro bundle / web build.

## 2. System Architecture

```
MySQL (school_erp)
   ↓  Eloquent models, BelongsToSchool tenant scope, SchoolContext
Laravel 12 API  /api/v1
   ↓  Sanctum Bearer tokens, SetSchoolContext (X-School-Id header/param/session/user chain)
   ↓  Spatie roles & permissions (team-scoped by school_id)
Role-aware controllers: StudentAppController, TeacherAppController,
ParentApiController, DriverApiController, ApiAuthController
   ↓  ApiBaseController envelope { success, message, data, meta, links, errors }
Mobile app (Expo / React Native)
   ↓  src/api/client.ts (axios, Bearer + X-School-Id interceptors)
   ↓  src/api/auth.ts, src/api/dashboard.ts (typed, canonical endpoints)
   ↓  src/auth/AuthContext.tsx (session state, AsyncStorage persistence)
   ↓  Screens: LoginScreen → HomeScreen (role dashboard) → ProfileScreen
```

## 3. API Inventory

| Surface | Count |
|---|---|
| Registered `/api/v1` routes | 162 (incl. GET\|HEAD pairs) |
| Auth | login, me (GET/PUT), change-password, refresh, logout |
| Student app | login, logout, profile, dashboard, attendance(+monthly/summary), fees, homework, assignments, timetable, exams, exam-schedule, results, report-card, calendar, documents, transport, leave-requests (CRUD), library (books/history/fines), notifications, circulars |
| Parent app | dashboard, children, child attendance/fees/exams/timetable/homework/calendar/documents/transport/leave, circulars, profile, change-password |
| Teacher app | login, logout, profile, dashboard, classes, students, timetable, attendance (classes/students/mark), homework (CRUD), exams (+marks), leave, notifications |
| Driver app | login, me, logout, dashboard, profile, routes (today/show/stops/students), trips (start/end/complete/current/today/history/show/students/eta), attendance (mark/update/pickup/drop/missed), stop flow (arrive/leave), notifications, location, SOS |
| Shared | branding (public), dashboard stats/activity/notifications, transport live tracking, notifications (admin) |

## 4. Endpoint Matrix (mobile app ↔ backend)

| App | Module | HTTP | Mobile Endpoint (canonical) | Laravel Route | Controller | Auth | Status |
|---|---|---|---|---|---|---|---|
| All | Login | POST | `/auth/login` | `api.v1.auth.login` | ApiAuthController@login | Public (5/min) | ✅ PASS |
| All | Profile | GET | `/me` | `api.v1.me` | ApiAuthController@me | Sanctum | ✅ PASS |
| All | Logout | POST | `/auth/logout` | `api.v1.auth.logout` | ApiAuthController@logout | Sanctum | ✅ PASS |
| Student | Dashboard | GET | `/student/dashboard` | `api.v1.student.dashboard` | StudentAppController@dashboard | Sanctum + student.linked | ✅ PASS |
| Student | Profile | GET | `/student/profile` | `api.v1.student.profile` | StudentAppController@profile | Sanctum + student.linked | ✅ PASS |
| Student | Attendance | GET | `/student/attendance` | `api.v1.student.attendance` | StudentAppController@attendance | Sanctum + student.linked | ✅ PASS |
| Student | Homework | GET | `/student/homework` | `api.v1.student.homework.index` | StudentAppController@homeworkIndex | Sanctum + student.linked | ✅ PASS |
| Student | Fees | GET | `/student/fees` | `api.v1.student.fees` | StudentAppController@fees | Sanctum + student.linked | ✅ PASS |
| Parent | Dashboard | GET | `/parents/{uuid}/dashboard` | `api.v1.parents.dashboard` | ParentApiController@dashboard | Sanctum | ✅ PASS |
| Parent | Children | GET | `/parents/{uuid}/children` | `api.v1.parents.children` | ParentApiController@children | Sanctum | ✅ PASS |
| Parent | Child fees | GET | `/parents/{uuid}/children/{child}/fees` | `api.v1.parents.child.fees` | ParentApiController@childFees | Sanctum | ✅ PASS |
| Teacher | Dashboard | GET | `/teacher/dashboard` | `api.v1.teacher.dashboard` | TeacherAppController@dashboard | Sanctum + role:Teacher | ✅ PASS |
| Teacher | Classes | GET | `/teacher/classes` | `api.v1.teacher.classes` | TeacherAppController@classes | Sanctum + role:Teacher | ✅ PASS |
| Teacher | Attendance | POST | `/teacher/attendance/mark` | `api.v1.teacher.attendance.mark` | TeacherAppController@markAttendance | Sanctum + role:Teacher | ✅ PASS |
| Driver | Dashboard | GET | `/driver/dashboard` | `api.v1.driver.dashboard` | DriverApiController@dashboard | Sanctum | ✅ PASS |
| Driver | Current trip | GET | `/driver/trips/current` | `api.v1.driver.trips.current` | DriverApiController@tripCurrent | Sanctum | ✅ PASS |
| Driver | Trips today | GET | `/driver/trips/today` | `api.v1.driver.trips.today` | DriverApiController@tripsToday | Sanctum | ✅ PASS |
| All | Branding | GET | `/branding` | `api.v1.branding.show` | BrandingController@show | Public | ✅ PASS |

Contract-pinned by `tests/Feature/MobileApiContractTest.php` (see §18).

## 5. Broken Endpoints

None found in the backend surface. Every role endpoint audited (route → controller
→ query → resource → JSON) matches the mobile contract defined in
`mobile/src/api/types.ts`.

## 6. Incorrect Routes

None found. Route names/methods match the canonical contract
(`/driver/trips/current` is the canonical name the docs and tests use; no
`trips/active` alias exists anywhere in the mobile app in this repo).

## 7. Incorrect Queries

None found in the audited role endpoints. Student/Teacher/Parent/Driver queries
are self-scoped (student.linked, role:Teacher, driver ownership checks,
guardian→child ownership), school-scoped via `BelongsToSchool` / `SchoolContext`,
and academic-year filtered where required.

## 8. School/Team Context Problems

**FOUND & FIXED (root cause):** `database/seeders/AdminUserSeeder.php` was
rewritten (commit `b82f516`) to use `admin@school.com` / `superadmin@school.com`
via `updateOrCreate`. All docs (`docs/development/TEST_USERS.md`,
`docs/audits/RBAC/PERMISSION_MATRIX.md`) and 14 test references use
`admin@example.com` / `superadmin@example.com`. Because `updateOrCreate` keys on
email, re-seeding a database that already contained the `@example.com` users
would **create duplicate admin users** while leaving the originals intact —
exactly the kind of "data exists but the app can't find the right user" failure
this audit targets.

Fix: restored the canonical `@example.com` emails in the seeder (with a comment
explaining why). This also un-broke the 6 failing tests.

`SetSchoolContext::resolveFromUser` correctly chains
param → header → session → current_school_id → guardian.school_id →
school_user pivot → model_has_roles, and `ApiAuthController` sets
`setPermissionsTeamId()` before role/permission resolution. Verified by existing
tests (`students school is authoritative for school context`, `missing school
pivot is self healed on login`).

## 9. Authentication Problems

**FOUND & FIXED (mobile):** The mobile app had **no authentication at all** —
`LoginScreen` rendered a button with no handler; no token was ever stored or
sent. New `AuthContext` implements login (`POST /auth/login`), token + school_id
persistence in AsyncStorage, session restore on launch via `GET /me`, role
detection, logout, and profile refresh. The axios client attaches
`Authorization: Bearer <token>` and `X-School-Id` on every request.

Backend auth was verified sound: 401 for unauthenticated access, 422 for bad
credentials, 403 for inactive/non-role accounts, 404 for student-login of a
non-student, tokens carry role-derived abilities, logout revokes the token.

## 10. Response Contract Problems

None found between the backend and the mobile contract. Verified by
`MobileApiContractTest`:
- `auth/login` → `data.{ token, token_type, user{id,name,email}, school_id, student{...} | students[] + parent_uuid }`
- `me` → `data.{ user, roles, permissions, students?, parent_uuid?, student? }`
- Student dashboard → `data.{ student, current_session{class,section,roll_no,academic_year}, attendance, fees_summary, pending_homework_count, notifications.unread_count }`
- Teacher dashboard → `data.{ teacher, today_classes, pending_homework_count, upcoming_exams, notifications }`
- Parent dashboard → `data.{ students, attendance_summary, fees_summary, exam_results_summary }`

## 11. Mobile Mapping Problems

**FOUND & FIXED:** `ProfileScreen` hardcoded "Profile Name" and "Role".
Replaced with real `/me`-driven data (name, email, role, phone, school, plus
student class/section or parent children when applicable). A new
`src/api/types.ts` defines the contract interfaces so future screens map against
typed shapes instead of `any`.

## 12. Cache/State Problems

**FOUND & FIXED:** Branding was cached for 30 min but fetched against a
hardcoded URL with no school context. Now: base URL from
`EXPO_PUBLIC_API_URL` config, and branding reloads bound to the authenticated
user's school (`BrandingProvider schoolId`). Auth state is restored once on
launch, refreshed via `/me` on profile pull-to-refresh.

## 13. Environment/Base URL Problems

**FOUND & FIXED:** `mobile/src/branding/BrandingService.ts` hardcoded
`http://localhost:8000/api/v1/branding`. Now all API access flows through
`src/config/api.ts` which reads `EXPO_PUBLIC_API_URL` (documented in
`mobile/.env.example`) and falls back to `localhost:8000/api/v1` only as a
local-dev default. A repo-wide search found no other stale/old API URLs
(`localhost`/`127.0.0.1` remain only in config defaults and Sanctum's dev
stateful-domains list).

## 14. Mock/Placeholder Data Found

| Location | Issue | Disposition |
|---|---|---|
| `mobile/src/screens/ProfileScreen.tsx` | "Profile Name" / "Role" hardcoded | ✅ Replaced with real `/me` data |
| `mobile/src/screens/LoginScreen.tsx` | No submit handler (login did nothing) | ✅ Wired to `POST /auth/login` |
| `mobile/src/screens/WelcomeScreen.tsx` | `onContinue` never wired (button dead) | ✅ Wired to navigate to Login |
| `mobile/src/navigation/AppNavigator.tsx` | No role-based home at all | ✅ Added Home dashboard per role |
| Backend seeders | Demo data (3 students, 2 parents, admins) | Kept — seeders are idempotent demo data, not app fallbacks |

## 15. Security Problems

- **FOUND & FIXED:** seeder email drift would duplicate admin users on live DBs (§8).
- No cross-school leakage found: parent/teacher/driver/student ownership checks
  are enforced and covered by tests (`guardian blocked from other parent profile`,
  `cross-school` linkage cases, tenant scoping tests).
- Tokens never expire (`SANCTUM_TOKEN_EXPIRATION` set to 10080 min in `.env`);
  documented in `docs/architecture/MOBILE_API_AUDIT.md` — recommendation only,
  not changed (expiry is a product decision and would break existing sessions).

## 16. Fixes Applied

1. Restored canonical admin credentials (`admin@example.com`,
   `superadmin@example.com`) in `AdminUserSeeder` — root cause of 6 test
   failures and potential live-DB duplicate admins.
2. Fixed stale tests to the current canonical seeders: `StudentLifecyclePromotionTest`
   (`john.doe@example.com` → `parent@school.com`), `StudentModuleTest`
   (robust datatable count instead of hardcoded 12).
3. Mobile: central `src/config/api.ts` with `EXPO_PUBLIC_API_URL`.
4. Mobile: typed axios client (`src/api/client.ts`) with Bearer + X-School-Id
   interceptors and `ApiError` (never hides failures).
5. Mobile: `AuthContext` — login, restore, logout, refresh, role detection.
6. Mobile: `LoginScreen` wired to real login with validation + error display.
7. Mobile: new `HomeScreen` role dashboard consuming the real role endpoints.
8. Mobile: `ProfileScreen` shows real data; placeholders removed.
9. Mobile: fixed the app that never compiled — added
   `@react-navigation/native-stack`, root `App.tsx` entry, `Image` imports,
   moved `drawerContent` to the Navigator prop, added `tsconfig.json` and a
   minimal ESLint config.
10. Backend: added `tests/Feature/MobileApiContractTest.php` (7 tests) pinning
    the exact JSON shapes the mobile app consumes.

## 17. Files Changed

Backend:
- `database/seeders/AdminUserSeeder.php`
- `tests/Feature/StudentAppApiTest.php` (comment clarification; assertion intact)
- `tests/Feature/StudentLifecyclePromotionTest.php`
- `tests/Feature/StudentModuleTest.php`
- `tests/Feature/MobileApiContractTest.php` (new)

Mobile:
- `mobile/src/config/api.ts` (new)
- `mobile/src/api/client.ts`, `auth.ts`, `dashboard.ts`, `types.ts` (new)
- `mobile/src/auth/AuthContext.tsx` (new)
- `mobile/src/screens/HomeScreen.tsx` (new)
- `mobile/App.tsx` (new — root entry)
- `mobile/tsconfig.json`, `mobile/.env.example`, `mobile/.eslintrc.cjs` (new)
- `mobile/src/App.tsx`, `mobile/src/navigation/AppNavigator.tsx`
- `mobile/src/screens/LoginScreen.tsx`, `ProfileScreen.tsx`, `AboutScreen.tsx`
- `mobile/src/components/LoadingScreen.tsx`
- `mobile/src/branding/BrandingService.ts`
- `mobile/package.json` (+`package-lock.json`)

## 18. Tests Added / Passed

| Suite | Result |
|---|---|
| Full backend suite (SQLite in-memory) | **243/243 passed** (965 assertions) |
| New: `MobileApiContractTest` | 7/7 passed (111 assertions) |
| Mobile typecheck (`tsc --noEmit`) | ✅ clean |
| Mobile ESLint | 0 errors (4 pre-existing unused-var warnings) |
| Mobile Metro bundle (`expo export --platform android`) | ✅ exported |
| ERP web build (`vite build`) | ✅ built |
| `php artisan route:list` | ✅ all mobile endpoints registered |

## 19. Tests Passed

See §18. Note: Playwright e2e and live-MySQL data tests were **not executed**
(no MySQL server in this environment; Playwright requires a running server).

## 20. Remaining Issues

1. **Live-database verification pending.** MySQL is not running in this
   environment, so Phases 4/22 (authenticate against the real production DB and
   verify each endpoint returns the admins' real records) could not be executed.
   The contract is verified via SQLite feature tests; the final step before
   production is to point the app at the live backend and re-run the
   screens with real records.
2. **Full role apps are not in this repository.** Only the `mobile/` skeleton
   exists here; the 28-screen Student/Parent/Teacher/Driver apps described in
   `docs/architecture/*` (e.g. `STUDENT_SCREEN_INVENTORY.md`) are not present.
   This pass delivers the full auth + role-aware dashboard + profile flow for
   all four roles; the remaining module screens (attendance lists, homework
   detail, fees, transport trip control, etc.) must be built against the
   (already contract-tested) endpoints, or the missing apps must be imported.
3. **E2E (Playwright) not run** — requires a live server + MySQL + browsers.
4. **Production base URL** must be supplied via `EXPO_PUBLIC_API_URL` in the
   EAS/preview/production profiles (`mobile/.env.example` documents this).

## 21. Recommendations

1. Run the app against the live backend once MySQL is up; verify one real
   record per module per role (use the `MobileApiContractTest` shapes as the
   checklist).
2. Set `EXPO_PUBLIC_API_URL` in each EAS build profile; never rely on the
   localhost fallback in production.
3. Keep the seeder emails as the documented contract (`@example.com`) or update
   docs + tests atomically if they ever change again.
4. When building the remaining role screens, consume only
   `src/api/*` services and `src/api/types.ts` — no raw axios calls in screens.
5. Consider token expiry/rotation for the live deployment (documented gap).
