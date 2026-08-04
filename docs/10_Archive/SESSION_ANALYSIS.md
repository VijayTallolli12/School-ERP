# SESSION ANALYSIS

**Phase:** 2B — Authentication & Authorization Stabilization  
**Date:** 2026-07-06  

---

## Session Configuration

| Setting | Value | Notes |
|---------|-------|-------|
| Driver | `database` | Sessions stored in SQLite `sessions` table |
| Lifetime | 120 minutes | |
| Expire on close | `false` | |
| Encrypt | `false` | |
| Cookie | `school-laravel-session` | Based on APP_NAME |
| Path | `/` | |
| Domain | `null` | Uses request host |
| Secure | `null` | Auto-detected from HTTPS |
| HTTP Only | `true` | |
| SameSite | `lax` | |

---

## Session Schema

```sql
CREATE TABLE "sessions" (
    "id" varchar not null,
    "user_id" integer,
    "ip_address" varchar,
    "user_agent" text,
    "payload" text not null,
    "last_activity" integer not null,
    primary key ("id")
)
```

---

## Session Lifecycle in Auth Flow

### Step 1: Login POST Request

1. **Before request:** Browser sends session cookie (or no cookie for first visit)
2. **StartSession middleware:** Loads session from database (or creates empty)
3. **LoginRequest::authenticate():** `Auth::attempt()` — stores user ID in session
4. **LoginController::store():**
   - `$request->session()->regenerate()` — migrates data to new ID, deletes old session
   - `SetSchoolContext::applySchoolContext($user)` — sets `session('school_id')` to resolved school ID
5. **Response sent:** `Set-Cookie` header with new session ID
6. **Session saved to database:** Session row written with `user_id`, `school_id`, payload

### Step 2: Dashboard GET (Post-Login Redirect)

1. Browser sends new session cookie
2. **StartSession middleware:** Loads session with `school_id = 1`
3. **SetSchoolContext middleware:**
   - `session('school_id')` = 1 → used as resolution (step 2 in resolveFromUser)
   - `setPermissionsTeamId(1)` → team ID set for Spatie
4. **Permission check:** `can('dashboard.view')` → YES

### Step 3: Refresh (Subsequent GET)

1. Same session cookie as Step 2
2. **StartSession middleware:** Same session loaded from database
3. **Same flow as Step 2 → works correctly**

---

## Session Data Verified

Query of `sessions` table confirmed:

```json
{"school_id": 1, ...}
```

- Authenticated sessions contain `user_id` (the authenticated user's ID)
- Authenticated sessions contain `school_id` (set by middleware)
- Guest sessions have `user_id: NULL`

---

## Potential Issues Identified

### 1. Session Cookie Domain

**Current:** `SESSION_DOMAIN=null` — cookie domain matches the request host.  
**Risk:** If the application is accessed via multiple domains (e.g., `localhost` and `127.0.0.1`), the session cookie from one won't apply to the other. This can cause the "desktop fails, mobile works" symptom if different devices use different hostnames.

**Recommendation:** Ensure all users access the application via a single, consistent hostname (e.g., `school.test` or the production domain). Do NOT mix `localhost`, `127.0.0.1`, and LAN IP addresses.

### 2. Session Driver

**Current:** `database` using SQLite.  
**Risk:** SQLite does not handle concurrent writes well. Under heavy load, session writes may fail. This is not a concern for development but should be reviewed for production.

**Recommendation:** Use `redis` or `memcached` for session storage in production.

### 3. SameSite Cookie

**Current:** `lax`  
**Risk:** `SameSite=lax` prevents cookies on cross-site requests. This is correct for standard usage. If the application is embedded in an iframe on another site, it would need `SameSite=none` with `Secure=true`.

**Recommendation:** Keep `lax` for standard deployment.

---

## Summary

| Aspect | Status |
|--------|--------|
| Session persistence | ✅ Verified (school_id stored in database) |
| Session regeneration | ✅ Correct (after login) |
| School ID in session | ✅ Set by middleware |
| Cookie configuration | ✅ Standard |
| Cross-request consistency | ✅ Verified |
| Guest session handling | ✅ OK |
