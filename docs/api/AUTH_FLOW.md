# AUTH FLOW

> Status: ✅ Sanctum token-based authentication
> Last Reviewed: 2026-06-23

---

## 1. Current Architecture

| Component | Implementation |
|-----------|---------------|
| **Package** | Laravel Sanctum (v4.x) |
| **Token Type** | Bearer (plainTextToken via `createToken()`) |
| **Token Expiry** | ❌ Never expires (`'expiration' => null`) |
| **Token Abilities** | User's Spatie permission names |
| **Auth Guard** | `web` (Sanctum uses `web` guard internally) |
| **School Tenant** | `SetSchoolContext` middleware + `SchoolContext` singleton |
| **Rate Limiting** | Login: 5/min, General: 60/min |

---

## 2. Authentication Flow

```
┌──────────┐     ┌──────────────────┐     ┌──────────────┐     ┌──────────┐
│  Mobile   │     │  POST /auth/login │     │  ApiAuth     │     │  Sanctum │
│  App      │────▶│  (email,password) │────▶│  Controller  │────▶│  Token   │
└──────────┘     └──────────────────┘     └──────────────┘     └──────────┘
                                                   │
                                                   ▼
                                          ┌──────────────────┐
                                          │  Resolve School  │
                                          │  1. school_id param │
                                          │  2. current_school_id │
                                          │  3. guardian.school_id │
                                          │  4. linked_student │
                                          │  5. schools() rel  │
                                          └──────────────────┘
                                                   │
                                                   ▼
                                          ┌──────────────────┐
                                          │  Set Permissions  │
                                          │  Team ID = school │
                                          └──────────────────┘
                                                   │
                                                   ▼
                                          ┌──────────────────┐
                                          │  Generate Token   │
                                          │  Abilities = all  │
                                          │  permissions      │
                                          └──────────────────┘
                                                   │
                                                   ▼
                                          ┌──────────────────┐
                                          │  Return Response  │
                                          │  token, user,     │
                                          │  school_id,       │
                                          │  [if Parent:      │
                                          │   students[],     │
                                          │   parent_uuid]    │
                                          └──────────────────┘
```

---

## 3. Login Request

**Endpoint:** `POST /api/v1/auth/login` (throttled: 5 requests/minute)

**Validation:**
```json
{
  "email": "required|email|max:255",
  "password": "required|string",
  "school_id": "nullable|integer|exists:schools,id",
  "device_name": "nullable|string|max:100"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Logged in successfully.",
  "data": {
    "token": "1|abc123...",
    "token_type": "Bearer",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "roles": ["Parent"],
      "avatar": null
    },
    "school_id": 1,
    "students": [
      {
        "id": 1,
        "uuid": "xxx",
        "name": "Child Name",
        "class": "10",
        "section": "A",
        "roll_number": "101",
        "admission_no": "ADM001",
        "photo": null
      }
    ],
    "parent_uuid": "parent-xxx"
  }
}
```

**Error Response (422):**
```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": { "email": ["These credentials do not match our records."] }
}
```

**Error Response (403):**
```json
{
  "success": false,
  "message": "This account is not active."
}
```

---

## 4. Authenticated Requests

**Header:** `Authorization: Bearer <token>`
**Optional:** `X-School-Id: <school_id>`

**Middleware stack on protected routes:**
```
auth:sanctum          → Validate Bearer token
school                → SetSchoolContext (resolve school_id)
throttle:60,1         → Rate limit 60 req/min
permission:<perm>     → Spatie permission gate
```

---

## 5. Token Management

### Token Abilities
Tokens are minted with the user's **full set of Spatie permission names** as abilities:
```php
$abilities = $user->getAllPermissions()->pluck('name')->values()->all();
$token = $user->createToken($deviceName, $abilities ?: ['dashboard.view']);
```

This means the token carries a flat list of every permission the user has across all scopes.

### Token Refresh
```http
POST /api/v1/auth/refresh
Authorization: Bearer <current_token>
```
- Revokes the current token
- Creates a new token with the same permission abilities
- Response contains the new `plainTextToken`

### Token Expiry
**Currently disabled** (`'expiration' => null`). Tokens persist until explicitly revoked.

---

## 6. Logout

```http
POST /api/v1/auth/logout
Authorization: Bearer <token>
```
- Deletes the current access token
- Records logout activity
- Returns `200 { success: true, message: "Logged out successfully." }`

---

## 7. Current User (Me)

```http
GET /api/v1/me
Authorization: Bearer <token>
```

Response includes:
- `user` — UserResource (id, name, email, avatar, roles)
- `roles` — Array of role names
- `permissions` — Array of permission names
- `students` — (if Parent role) linked students
- `parent_uuid` — (if Parent role) guardian UUID

---

## 8. Role-Based Behavior

The auth flow is **role-aware** only at the response level:

| Role | Login Response Includes |
|------|------------------------|
| Parent | `students[]`, `parent_uuid` |
| Teacher | Nothing extra |
| Student | Nothing extra |
| Admin | Nothing extra |

The token itself does not encode role — role-based behavior is determined by:
1. The Spatie permission abilities on the token
2. Runtime role checks via `$user->hasRole('Teacher')`

---

## 9. Unified Authentication Plan (Teacher + Student)

### Proposed Login Flow

The current login endpoint already supports all roles. No structural changes needed:

```php
// ApiAuthController@login already:
// 1. Authenticates any user by email/password
// 2. Resolves school context
// 3. Creates token with permission abilities
// 4. Returns role-appropriate response
```

### Teacher Login Response

Proposed additions to `/api/v1/me` and `/api/v1/auth/login`:
```json
{
  "teacher_uuid": "teacher-xxx",
  "classes": [
    { "id": 1, "class": "10", "section": "A", "is_class_teacher": true },
    { "id": 2, "class": "9", "section": "B", "is_class_teacher": false }
  ]
}
```

### Student Login Response

Proposed additions:
```json
{
  "student_uuid": "student-xxx",
  "class": "10",
  "section": "A",
  "academic_year": "2025-26"
}
```

---

## 10. Security Recommendations

| # | Gap | Fix | Effort |
|---|-----|-----|--------|
| 1 | Tokens never expire | Set `SANCTUM_EXPIRATION` in `.env` (e.g., 525600 = 1 year) | Small |
| 2 | No device tracking | Store device_name, last_ip, last_used_at on tokens | Medium |
| 3 | No remote logout | List devices endpoint + revoke by token ID | Medium |
| 4 | Flat permission abilities | Scope abilities by app context (teacher vs parent vs student) | Medium |
| 5 | No 2FA | Add optional TOTP for admin/parent sensitive actions | Large |

---

## 11. Implementation Order (Auth)

| Step | Task | Depends On |
|------|------|------------|
| 1 | Set token expiry in config | — |
| 2 | Add device tracking to login | — |
| 3 | Add teacher_uuid + classes to login response | — |
| 4 | Add student_uuid to login response | — |
| 5 | Create device management endpoints | Step 2 |
| 6 | Create dedicated mobile permission set | Step 3-4 |
