# AUTHENTICATION AUDIT

**Phase:** 2B — Authentication & Authorization Stabilization  
**Date:** 2026-07-06  
**Auditor:** Laravel Architect (Static Analysis + Runtime Diagnostics)

---

## 1. Login Flow

### Components Audited

| Component | Status | Findings |
|-----------|--------|----------|
| `LoginController::create()` | ✅ OK | Returns login view |
| `LoginController::store()` | ❌ FIXED | Called `hasRole('Parent')` before school context was set |
| `LoginRequest::authenticate()` | ✅ OK | Standard `Auth::attempt()` |
| `LoginRequest::rules()` | ✅ OK | Email, password, remember, school_id validation |
| Session regeneration | ✅ OK | `$request->session()->regenerate()` called after auth |
| Redirect logic | ❌ FIXED | Was non-role-aware, all non-Parent users went to admin.dashboard |

### Fixed: LoginController Flow

```
POST /login
  ├── LoginRequest::authenticate()     → Auth::attempt()
  ├── Session::regenerate()            → prevent session fixation
  ├── LoginActivityService::recordSuccess()
  ├── SetSchoolContext::applySchoolContext()  → *** FIX: sets team_id BEFORE hasRole() ***
  ├── hasRole('Parent')                → now works correctly
  ├── hasRole('Teacher')               → NEW: explicit check
  ├── hasRole('Principal')             → NEW: explicit check
  ├── hasRole('Staff')                 → NEW: explicit check
  ├── hasRole('School Admin')          → NEW: explicit check
  └── redirect()->intended(route(...))
```

---

## 2. Middleware Pipeline

### Route Middleware Stack (for `/admin/dashboard`)

```
1. EncryptCookies          (web group)
2. AddQueuedCookiesToResponse (web group)
3. StartSession            (web group)
4. ShareErrorsFromSession   (web group)
5. ValidateCsrfToken        (web group)
6. SubstituteBindings       (web group)
7. auth                    (route group)
8. school                  (route group)  ← SetSchoolContext
9. permission:dashboard.view (route-specific)
```

**Order verified:** The `school` middleware runs BEFORE `permission`, ensuring `setPermissionsTeamId()` is called before any permission check.

### Middleware Configuration

- `school` alias → `App\Http\Middleware\SetSchoolContext`
- `permission` alias → `Spatie\Permission\Middleware\PermissionMiddleware`
- Registered in `bootstrap/app.php`

---

## 3. Authentication Guard

| Setting | Value |
|---------|-------|
| Default guard | `web` |
| Driver | `session` |
| Provider | `users` (Eloquent, User model) |
| Password broker | `users` |

**Verified:** Single guard (`web`), no multi-guard conflicts.

---

## 4. Session Configuration

| Setting | Value |
|---------|-------|
| Driver | `database` |
| Lifetime | 120 minutes |
| Expire on close | false |
| Cookie | school-laravel-session |
| Path | `/` |
| Domain | null (uses request host) |
| Secure | null (auto based on HTTPS) |
| SameSite | `lax` |

**Verified:** Session state persisted correctly in `sessions` table. Session payload confirmed to contain `school_id` after middleware runs.

---

## 5. Gate Registration Order

```
1. AppServiceProvider::boot()
   └── Gate::before($user, $ability)       → Super Admin bypass: isSuperAdmin() ? true : null

2. PermissionServiceProvider::packageBooted()
   └── callAfterResolving(Gate::class, ...)
       └── PermissionRegistrar::registerPermissions($gate)
           └── Gate::before($user, $ability) → checkPermissionTo($ability)
```

**Verified:** Both callbacks are properly registered. The Spatie callback runs second via `callAfterResolving`. Super Admin check runs first and short-circuits if `isSuperAdmin()` returns true.

---

## 6. API Authentication (Sanctum)

**File:** `ApiAuthController.php`

- `login()` ✅ Already correctly resolves school context before role checks
- `me()` ✅ Updated to use `SetSchoolContext::resolveFromUser()` for centralized resolution

---

## Summary

| Auth Component | Status |
|---------------|--------|
| Login flow | ✅ FIXED (school context applied before role check) |
| Session persistence | ✅ Verified |
| School context resolution | ✅ FIXED (model_has_roles fallback added) |
| Middleware order | ✅ Correct |
| Gate registration | ✅ Correct |
| Role-aware redirect | ✅ FIXED (explicit role checks) |
| Guard configuration | ✅ Correct |
| API auth | ✅ Verified |
