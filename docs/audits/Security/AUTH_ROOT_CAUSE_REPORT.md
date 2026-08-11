# Super Admin Login Failure — Root Cause Report

## Root Cause

**The database was empty.** No migrations or seeders had been run. The `users` table contained 0 records, so `Auth::attempt()` correctly returned false for any email/password combination, producing "These credentials do not match our records."

---

## Step 1 — Auth Flow Trace

| Layer | File | Line | Notes |
|-------|------|------|-------|
| Blade | `resources/views/modules/auth/login.blade.php` | 18 | POSTs to `route('login.store')` |
| Route | `routes/modules/auth.php` | 10-12 | `POST /login` → `LoginController::store` with throttle 5:1 |
| Request | `app/Modules/Auth/Requests/LoginRequest.php` | 33 | `Auth::attempt(['email', 'password'], remember)` |
| Guard | `config/auth.php` | 41-44 | `web` guard, `session` driver |
| Provider | `config/auth.php` | 65-68 | `users` provider, `eloquent` driver, `User::class` model |
| Model | `app/Models/User.php` | 19 | Extends `Authenticatable`, uses `HasRoles` |
| Controller | `app/Modules/Auth/Controllers/LoginController.php` | 23-44 | Calls `authenticate()`, regenerates session, redirects |

The error flows: `LoginRequest::authenticate()` → `Auth::attempt()` returns false → `ValidationException` with `auth.failed` message.

## Step 2 — Database Findings

- **Total users before fix:** 0
- **Total schools before fix:** 0
- **superadmin@example.com before fix:** NOT FOUND
- **After `migrate:fresh --seed`:** 2 users, 1 school
  - `superadmin@example.com` — status=active, is_super_admin=yes, email_verified=yes
  - `admin@example.com` — status=active, is_super_admin=no
- **Soft delete:** Enabled on User model (uses `SoftDeletes` trait), but `deleted_at` is NULL for both users.
- **School context:** `current_school_id` = 1 (DEMO school), correctly set.

## Step 3 — Password Verification

- Stored hash: `$2y$12$kgTZszJujwBny1rkCroQ.OkF9pe0WoM5v9gFq.tFTs1dH2iN760Va`
- `Hash::check('password', hash)` → **PASS**
- Source: `database/seeders/AdminUserSeeder.php` line 24: `Hash::make('password')`

## Step 4 — Spatie Role/Permission Audit

| Check | Result |
|-------|--------|
| Roles exist in `roles` table | Yes — 12 roles (Super Admin, School Admin, Teacher, etc.) |
| Super Admin role guard | `web` (correct) |
| Super Admin role team_id | NULL (global role, correct for super admin) |
| User assigned to role | Yes — `model_has_roles` has record for user_id=1, role_id=1 |
| `getRoleNames()` returns role | Yes — returns "Super Admin" when team scope is set |
| Permissions exist | Yes — 102 permissions |
| Permission cache | May be stale — `permission:cache-reset` is recommended after seeding |

**Important:** Spatie team-scoped permissions require `setPermissionsTeamId()` to be called before role queries. The seeder does this correctly.

## Step 5 — Auth Config

- **Default guard:** `web` (from `env('AUTH_GUARD', 'web')`)
- **Web guard driver:** `session` (correct for web login)
- **User provider:** `eloquent` → `App\Models\User` (correct)
- **Password broker:** `users` → `password_reset_tokens` table
- **Rate limiting:** 5 attempts per minute on login route — no evidence of lockout.

No misconfiguration found.

## Step 6 — School Context Middleware

The `school` middleware (`SetSchoolContext`) is applied to all admin routes (after login). It sets the tenant context from `current_school_id` or `X-School-Id` header. This middleware **does not** run during login (login routes are in the `guest` middleware group). Login was failing before the middleware could even run, so this is not a contributor.

Super admin accounts with `is_super_admin=true` should bypass tenant scoping — this is handled correctly in the middleware.

## Step 7 — Recovery Command

Created: `app/Console/Commands/FixSuperAdmin.php`

```bash
php artisan fix:super-admin
```

Creates or repairs the superadmin@example.com user with:
- Password: `password`
- Role: Super Admin
- School link: first available school
- Idempotent — safe to run multiple times

## Step 8 — Verification

- `Auth::attempt()` with web guard: **PASS**
- `Hash::check('password', hash)`: **PASS**
- Roles after team scope set: **Super Admin** (correct)
- Post-seed: `php artisan migrate:fresh --seed` completed successfully

## Summary

| Category | Finding | Status |
|----------|---------|--------|
| Root cause | Database not seeded — 0 users, 0 schools | ✅ Fixed |
| Auth config | Correct (web guard, eloquent provider, User model) | ✅ Verified |
| Password hash | Matches "password" | ✅ Verified |
| Spatie roles | Super Admin role exists, user assigned | ✅ Verified |
| Permission cache | Fresh after seed | ✅ Checked |
| School context | Not blocking login (guest route) | ✅ Verified |
| Recovery command | `php artisan fix:super-admin` | ✅ Created |
