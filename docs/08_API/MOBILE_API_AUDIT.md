# MOBILE API AUDIT

> Generated: 2026-06-23
> Scope: All existing `/api/v1/` endpoints, authentication, security

---

## 1. Architecture Overview

| Aspect | Status | Detail |
|--------|--------|--------|
| **API Base** | ✅ `/api/v1/` | All routes under `prefix('v1')` |
| **Auth** | ✅ Sanctum | Bearer token, never-expiring (`expiration => null`) |
| **School Tenant** | ✅ `SetSchoolContext` | Resolves via `X-School-Id` header → param → session → user chain |
| **Permissions** | ✅ Spatie v6 | Team-scoped by `school_id`, per-route permission gates |
| **Rate Limiting** | ⚠️ Partial | Login: 5/min, General: 60/min — hardcoded in route middleware |
| **Response Format** | ✅ Unified | `{ success, message, data, meta, links, errors }` |
| **API Docs** | ❌ Missing | No OpenAPI/Swagger/Postman collection |
| **API Tests** | ❌ Missing | No dedicated API test files |
| **Exception Handler** | ⚠️ Minimal | Only `AuthenticationException` rendered as JSON |
| **CSRF** | ✅ Exempted | `api/*` excluded from CSRF |

---

## 2. Complete Endpoint Inventory (58 routes)

### 2.1 Authentication (4 routes)

| Method | URI | Auth | Permission | Response Data |
|--------|-----|------|------------|---------------|
| POST | `/api/v1/auth/login` | Public (throttled 5/min) | — | `token`, `token_type`, `user`, `school_id`, `students[]` (if Parent), `parent_uuid` (if Parent) |
| GET | `/api/v1/me` | Sanctum | — | `user`, `roles`, `permissions`, `students[]` (if Parent), `parent_uuid` (if Parent) |
| POST | `/api/v1/auth/refresh` | Sanctum | — | New `token`, `token_type` |
| POST | `/api/v1/auth/logout` | Sanctum | — | Success message |

**Security Gaps:**
- [ ] Tokens never expire (`expiration => null`)
- [ ] No refresh token rotation (old token deleted, not rotated)
- [ ] No device fingerprinting
- [ ] No brute-force protection beyond rate limiting

### 2.2 Dashboard (3 routes)

| Method | URI | Auth | Permission |
|--------|-----|------|------------|
| GET | `/api/v1/dashboard/stats` | Sanctum | `dashboard.view` |
| GET | `/api/v1/dashboard/activity` | Sanctum | `dashboard.view` |
| GET | `/api/v1/dashboard/notifications` | Sanctum | `dashboard.view` |

### 2.3 Students (6 routes)

| Method | URI | Parameters | Notes |
|--------|-----|------------|-------|
| GET | `/api/v1/students` | `search`, `status`, `class_section_id`, `per_page` | Paginated list |
| GET | `/api/v1/students/{uuid}` | — | Student detail with guardian/documents |
| GET | `/api/v1/students/{uuid}/attendance` | `month`, `year` | Monthly summary |
| GET | `/api/v1/students/{uuid}/fees` | — | Fee records + categories |
| GET | `/api/v1/students/{uuid}/exams` | — | Results grouped by academic year |
| GET | `/api/v1/students/{uuid}/timetable` | — | Weekly timetable |

All require `students.view` / `attendance.view` / `fees.view` / `exams.view` / `timetable.view`.

### 2.4 Parents (19 routes) ← Most extensive

| Method | URI | Notes |
|--------|-----|-------|
| GET | `/api/v1/parents` | Paginated list |
| GET | `/api/v1/parents/{uuid}` | Detail with children |
| GET | `/api/v1/parents/{uuid}/dashboard` | Aggregated (attendance, fees, exams, homework, notifications) |
| GET | `/api/v1/parents/{uuid}/children` | All linked students |
| GET | `/api/v1/parents/{uuid}/children/{childUuid}/attendance` | Monthly attendance |
| GET | `/api/v1/parents/{uuid}/children/{childUuid}/fees` | Fee records |
| GET | `/api/v1/parents/{uuid}/children/{childUuid}/exams` | Exam results |
| GET | `/api/v1/parents/{uuid}/children/{childUuid}/timetable` | Weekly timetable |
| GET | `/api/v1/parents/{uuid}/children/{childUuid}/homework` | Active homework |
| GET | `/api/v1/parents/{uuid}/children/{childUuid}/calendar` | Calendar events |
| GET | `/api/v1/parents/{uuid}/children/{childUuid}/documents` | Student documents |
| GET | `/api/v1/parents/{uuid}/children/{childUuid}/leave-requests` | List leave requests |
| POST | `/api/v1/parents/{uuid}/children/{childUuid}/leave-requests` | Create leave request |
| GET | `/api/v1/parents/{uuid}/children/{childUuid}/leave-requests/{id}` | View leave detail |
| PUT | `/api/v1/parents/{uuid}/children/{childUuid}/leave-requests/{id}` | Update pending leave |
| GET | `/api/v1/parents/{uuid}/circulars` | Paginated announcements |
| GET | `/api/v1/parents/{uuid}/circulars/{id}` | Circular detail |
| POST | `/api/v1/parents/{uuid}/circulars/{id}/read` | Mark read |
| PUT | `/api/v1/parents/{uuid}` | Update profile |
| PUT | `/api/v1/parents/{uuid}/change-password` | Change password |

**Note:** Parent endpoints are **over-permissioned** — they require admin-level permissions (`parents.view`, `attendance.view`, etc.) which means a parent user also needs these Spatie permissions assigned. This works currently because parent users are assigned these permissions via roles, but it creates tight coupling between the admin permission system and mobile app access.

### 2.5 Teachers (6 routes)

| Method | URI | Notes |
|--------|-----|-------|
| GET | `/api/v1/teachers` | Paginated list |
| GET | `/api/v1/teachers/{uuid}` | Detail with IDOR check |
| GET | `/api/v1/teachers/{uuid}/timetable` | Weekly timetable |
| GET | `/api/v1/teachers/{uuid}/attendance` | Monthly attendance |
| GET | `/api/v1/teachers/{uuid}/classes` | Assigned class sections |
| GET | `/api/v1/teachers/{uuid}/subjects` | Assigned subjects |

**Gap:** No teacher self-service endpoints (mark attendance, record homework, submit marks, apply leave).

### 2.6 Attendance (4 routes)

| Method | URI | Parameters |
|--------|-----|------------|
| GET | `/api/v1/attendance` | `date`, `class_section_id`, `student_id`, `status` |
| GET | `/api/v1/attendance/daily` | `date` (req), `class_section_id` (req) |
| GET | `/api/v1/attendance/monthly` | `class_section_id` (req), `month` (req), `year` (req) |
| GET | `/api/v1/attendance/statistics` | `from`, `to`, `class_section_id` |

**Gap:** No POST/PUT for marking attendance — this is admin-web only.

### 2.7 Fees (5 routes)

| Method | URI | Notes |
|--------|-----|-------|
| GET | `/api/v1/fees` | Student fee records |
| GET | `/api/v1/fees/pending` | Pending fees |
| GET | `/api/v1/fees/payments` | Payment history |
| GET | `/api/v1/fees/payments/{paymentId}/receipt` | Payment receipt |
| GET | `/api/v1/fees/dashboard-stats` | Aggregated stats |

**Gap:** No online payment initiation or payment callback webhook.

### 2.8 Exams (5 routes)

| Method | URI | Notes |
|--------|-----|-------|
| GET | `/api/v1/exams` | Filtered list |
| GET | `/api/v1/exams/{id}` | Single exam |
| GET | `/api/v1/exams/{examId}/results` | Results with summary |
| GET | `/api/v1/exams/{examId}/results/{resultId}` | Single result |
| GET | `/api/v1/exams/{examId}/report-card` | Full report card |

### 2.9 Notifications (5 routes)

| Method | URI | Notes |
|--------|-----|-------|
| GET | `/api/v1/notifications` | Paginated |
| GET | `/api/v1/notifications/unread` | Up to 50 unread |
| POST | `/api/v1/notifications/{id}/read` | Mark read |
| POST | `/api/v1/notifications/read-all` | Mark all read |
| GET | `/api/v1/notifications/announcements` | Banner + list |

---

## 3. Reusable Endpoints (no changes needed)

These endpoints can serve all three mobile apps (Parent, Teacher, Student):

| Endpoint | Can Serve |
|----------|-----------|
| `POST /api/v1/auth/login` | All apps (role-aware) |
| `POST /api/v1/auth/refresh` | All apps |
| `POST /api/v1/auth/logout` | All apps |
| `GET /api/v1/me` | All apps (role-aware) |
| `GET /api/v1/notifications/*` | All apps |
| `GET /api/v1/exams/{id}/results` | All apps |
| `GET /api/v1/exams/{examId}/report-card` | All apps |
| `GET /api/v1/attendance/daily` | Teacher + Student |
| `GET /api/v1/attendance/monthly` | Teacher + Student |
| `GET /api/v1/fees/*` | Parent + Student |

---

## 4. Missing Endpoints (teacher app)

| Feature | Required Endpoints |
|---------|-------------------|
| **Dashboard** | `GET /api/v1/teacher/dashboard` — aggregated stats |
| **My Classes** | `GET /api/v1/teacher/classes/{id}/students` — student list |
| **Mark Attendance** | `POST /api/v1/teacher/classes/{id}/attendance` — bulk mark |
| **Homework CRUD** | `GET/POST /api/v1/teacher/homework`, `PUT/DELETE /api/v1/teacher/homework/{id}` |
| **Marks Entry** | `GET/POST /api/v1/teacher/exams/{id}/marks` — bulk entry |
| **Leave Apply** | `POST /api/v1/teacher/leave-requests` |
| **Leave List** | `GET /api/v1/teacher/leave-requests` |
| **Profile** | `PUT /api/v1/teacher/profile` |

## 5. Missing Endpoints (student app)

| Feature | Required Endpoints |
|---------|-------------------|
| **Dashboard** | `GET /api/v1/student/dashboard` |
| **My Attendance** | Already exists via `GET /api/v1/students/{uuid}/attendance` |
| **Homework** | `GET /api/v1/student/homework` |
| **Library** | `GET /api/v1/student/library/issued`, `GET /api/v1/student/library/history` |
| **Transport** | `GET /api/v1/student/transport` — route/stop/vehicle |
| **Timetable** | Already exists |
| **Profile** | `PUT /api/v1/student/profile` |

## 6. Missing Endpoints (transport)

| Endpoint | Purpose |
|----------|---------|
| `GET /api/v1/transport/routes` | Available routes |
| `GET /api/v1/transport/routes/{id}` | Route detail with stops |
| `GET /api/v1/transport/routes/{id}/stops` | Stop sequence with timings |
| `GET /api/v1/transport/vehicles` | Vehicle list |
| `GET /api/v1/transport/vehicles/{id}` | Vehicle detail |
| `GET /api/v1/transport/drivers` | Driver list |
| `GET /api/v1/transport/assignments/student/{studentId}` | Student's transport assignment |

## 7. Security Gaps Summary

| # | Issue | Severity | Recommendation |
|---|-------|----------|---------------|
| 1 | Tokens never expire | Medium | Set `expiration` in `config/sanctum.php` or implement manual expiry |
| 2 | No refresh token rotation | Medium | Implement token families or rotation |
| 3 | No device management | Low | Add `device_name` tracking, allow remote logout of devices |
| 4 | Parent APIs over-permissioned | Medium | Create dedicated teacher/student permissions scoped to self |
| 5 | No API input form requests | Low | Create FormRequest classes per endpoint |
| 6 | No API tests | Medium | Add feature tests for all API endpoints |
| 7 | Rate limiting hardcoded | Low | Move to `RateLimiter` facade for centralized config |
| 8 | No push notification support | Medium | Add FCM/APNs device token registration endpoints |

---

## 8. Audit Summary

| Metric | Count |
|--------|-------|
| Total API routes | 58 |
| Reusable as-is | ~20 |
| Parent-specific | ~19 |
| Teacher-specific | 6 (read-only) |
| Student-facing (via student endpoints) | 6 |
| Missing for Teacher App | ~12 |
| Missing for Student App | ~6 |
| Missing for Transport | ~6 |
| Missing for Notifications | 1 (push registration) |
