# PHASE 5 — API ROADMAP

> Mobile API Foundation for Teacher App, Student App & Transport

---

## 0. Development Order

```
Phase 5.0 — Auth improvements ────────────────────────────── Week 1
Phase 5.1 — Transport API (new module, no existing API) ───── Week 2-3
Phase 5.2 — Teacher App APIs (read + write) ───────────────── Week 4-5
Phase 5.3 — Student App APIs (read + minor write) ─────────── Week 6-7
Phase 5.4 — Notification enhancements ─────────────────────── Week 8
Phase 5.5 — Testing + Documentation ───────────────────────── Week 9-10
```

---

## 1. Authentication Improvements (Week 1)

### Priority: High
### Dependencies: None
### Effort: 3 days

| Task | Endpoint | Method | Description |
|------|----------|--------|-------------|
| Token expiry | `.env` | Config | Set `SANCTUM_EXPIRATION=525600` (1 year) |
| Device tracking | `POST /api/v1/auth/login` | Enhancement | Store `device_name`, `device_os`, `fcm_token` |
| Teacher login response | `POST /api/v1/auth/login` | Enhancement | Return `teacher_uuid`, `classes[]` |
| Student login response | `POST /api/v1/auth/login` | Enhancement | Return `student_uuid`, `class`, `section` |
| Device list | `GET /api/v1/auth/devices` | New | List active sessions |
| Device revoke | `DELETE /api/v1/auth/devices/{tokenId}` | New | Remote logout |

### Files to create:
- `app/Http/Controllers/Api/V1/DeviceController.php`
- `app/Http/Resources/Api/V1/DeviceResource.php`

### Files to modify:
- `app/Modules/Auth/Controllers/ApiAuthController.php`
- `config/sanctum.php`

---

## 2. Transport API (Weeks 2-3)

### Priority: High
### Dependencies: Auth improvements
### Effort: 8 days

### 2.1 Routes & Stops

| Endpoint | Method | Permission | Description |
|----------|--------|------------|-------------|
| `/api/v1/transport/routes` | GET | `transport.view` | List active routes with vehicle + driver |
| `/api/v1/transport/routes/{id}` | GET | `transport.view` | Route detail with stops |
| `/api/v1/transport/routes/{id}/stops` | GET | `transport.view` | Stops ordered by sequence with times |

### 2.2 Vehicles

| Endpoint | Method | Permission | Description |
|----------|--------|------------|-------------|
| `/api/v1/transport/vehicles` | GET | `transport.view` | Vehicle list with driver |
| `/api/v1/transport/vehicles/{id}` | GET | `transport.view` | Vehicle detail + occupancy |

### 2.3 Drivers

| Endpoint | Method | Permission | Description |
|----------|--------|------------|-------------|
| `/api/v1/transport/drivers` | GET | `transport.view` | Driver list |
| `/api/v1/transport/drivers/{id}` | GET | `transport.view` | Driver detail |

### 2.4 Student Assignment

| Endpoint | Method | Permission | Description |
|----------|--------|------------|-------------|
| `/api/v1/student/transport` | GET | Self | Current student's route, stop, vehicle, timings |
| `/api/v1/students/{uuid}/transport` | GET | `transport.view` | Any student's transport (admin/parent) |

### 2.5 Live Tracking (Future — Phase 5.6)

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/v1/transport/vehicles/{id}/location` | GET | Last known GPS coordinates |
| `WebSocket` | — | Real-time vehicle position stream |

### Files to create:
- `app/Http/Controllers/Api/V1/TransportApiController.php`
- `app/Http/Resources/Api/V1/TransportRouteResource.php`
- `app/Http/Resources/Api/V1/TransportVehicleResource.php`
- `app/Http/Resources/Api/V1/TransportDriverResource.php`
- `app/Http/Resources/Api/V1/TransportAssignmentResource.php`

### Route file update:
- `routes/modules/api.php` — add transport group under `prefix('v1')`

### Database: No new migrations needed (transport tables already exist)

---

## 3. Teacher App APIs (Weeks 4-5)

### Priority: High
### Dependencies: Auth improvements (teacher_uuid in login)
### Effort: 10 days

### 3.1 Dashboard

| Endpoint | Method | Permission | Description |
|----------|--------|------------|-------------|
| `/api/v1/teacher/dashboard` | GET | Self | My classes count, today's schedule, pending tasks, leave balance, recent activity |

### 3.2 My Classes

| Endpoint | Method | Permission | Description |
|----------|--------|------------|-------------|
| `/api/v1/teacher/classes` | GET | Self | Assigned class sections (reuses existing logic) |
| `/api/v1/teacher/classes/{classSectionId}/students` | GET | `attendance.view` | Student roster for a class |

### 3.3 Attendance

| Endpoint | Method | Permission | Description |
|----------|--------|------------|-------------|
| `/api/v1/teacher/classes/{classSectionId}/attendance/today` | GET | `attendance.view` | Today's attendance status per student |
| `/api/v1/teacher/classes/{classSectionId}/attendance` | POST | `attendance.create` | Bulk mark attendance (present/absent/late/leave per student) |

### 3.4 Homework

| Endpoint | Method | Permission | Description |
|----------|--------|------------|-------------|
| `/api/v1/teacher/homework` | GET | `homework.view` | My homework (filtered by my subjects) |
| `/api/v1/teacher/homework` | POST | `homework.create` | Create homework |
| `/api/v1/teacher/homework/{id}` | GET | `homework.view` | Homework detail |
| `/api/v1/teacher/homework/{id}` | PUT | `homework.update` | Update homework |
| `/api/v1/teacher/homework/{id}` | DELETE | `homework.delete` | Delete homework |

### 3.5 Timetable

| Endpoint | Method | Permission | Description |
|----------|--------|------------|-------------|
| `/api/v1/teacher/timetable` | GET | Self | My weekly timetable (exists but re-scoped) |

### 3.6 Exams & Marks

| Endpoint | Method | Permission | Description |
|----------|--------|------------|-------------|
| `/api/v1/teacher/exams` | GET | `exams.view` | Exams for my classes/subjects |
| `/api/v1/teacher/exams/{examId}/students` | GET | `exams.view` | Student list for marks entry |
| `/api/v1/teacher/exams/{examId}/marks` | POST | `exams.create` | Bulk marks entry |
| `/api/v1/teacher/exams/{examId}/marks/bulk` | POST | `exams.create` | Bulk save with auto publish option |

### 3.7 Leave Management

| Endpoint | Method | Permission | Description |
|----------|--------|------------|-------------|
| `/api/v1/teacher/leave-requests` | GET | Self | My leave requests |
| `/api/v1/teacher/leave-requests` | POST | `leave_management.create` | Apply for leave |
| `/api/v1/teacher/leave-requests/{id}` | GET | Self | Leave detail |
| `/api/v1/teacher/leave-requests/{id}` | PUT | Self | Update pending leave (same logic as parent) |
| `/api/v1/teacher/leave-types` | GET | Self | Available leave types |

### 3.8 Notifications

| Endpoint | Method | Permission | Description |
|----------|--------|------------|-------------|
| Reuse existing `/api/v1/notifications/*` | — | — | No new endpoints needed |

### 3.9 Profile

| Endpoint | Method | Permission | Description |
|----------|--------|------------|-------------|
| `/api/v1/teacher/profile` | GET | Self | My profile |
| `/api/v1/teacher/profile` | PUT | Self | Update profile (phone, address, etc.) |
| `/api/v1/teacher/change-password` | PUT | Self | Change password |

### Files to create:
- `app/Http/Controllers/Api/V1/TeacherAppController.php` (dashboard + self-service)
- `app/Http/Resources/Api/V1/TeacherProfileResource.php`
- `app/Http/Resources/Api/V1/TeacherDashboardResource.php`

### Files to reuse:
- Existing `TeacherApiController` (for read-only admin-facing endpoints)
- Existing `HomeworkController`, `ExamController`, `LeaveRequestController` business logic via services

---

## 4. Student App APIs (Weeks 6-7)

### Priority: High
### Dependencies: Auth improvements (student_uuid in login)
### Effort: 8 days

### 4.1 Dashboard

| Endpoint | Method | Permission | Description |
|----------|--------|------------|-------------|
| `/api/v1/student/dashboard` | GET | Self | Attendance %, upcoming exams, pending fees, recent homework, timetable today |

### 4.2 Attendance

| Endpoint | Method | Permission | Description |
|----------|--------|------------|-------------|
| `/api/v1/student/attendance` | GET | Self | Monthly attendance (reuses `StudentApiController@attendanceSummary` logic via UUID) |

### 4.3 Homework

| Endpoint | Method | Permission | Description |
|----------|--------|------------|-------------|
| `/api/v1/student/homework` | GET | Self | Homework for my class section (active, by subject) |
| `/api/v1/student/homework/{id}` | GET | Self | Homework detail |

### 4.4 Timetable

| Endpoint | Method | Permission | Description |
|----------|--------|------------|-------------|
| `/api/v1/student/timetable` | GET | Self | My weekly timetable (reuses existing logic) |

### 4.5 Exam Results

| Endpoint | Method | Permission | Description |
|----------|--------|------------|-------------|
| `/api/v1/student/exam-results` | GET | Self | Results grouped by academic year |
| `/api/v1/student/exam-results/{examId}` | GET | Self | Report card for specific exam |

### 4.6 Library

| Endpoint | Method | Permission | Description |
|----------|--------|------------|-------------|
| `/api/v1/student/library/issued` | GET | Self | Currently issued books |
| `/api/v1/student/library/history` | GET | Self | Past issue history |
| `/api/v1/student/library/fines` | GET | Self | Outstanding fines |

### 4.7 Transport

| Endpoint | Method | Permission | Description |
|----------|--------|------------|-------------|
| `/api/v1/student/transport` | GET | Self | My transport assignment (route, stop, timings, vehicle) |

### 4.8 Notifications

| Endpoint | Method | Permission | Description |
|----------|--------|------------|-------------|
| Reuse existing `/api/v1/notifications/*` | — | — | No new endpoints needed |

### 4.9 Profile

| Endpoint | Method | Permission | Description |
|----------|--------|------------|-------------|
| `/api/v1/student/profile` | GET | Self | My profile |
| `/api/v1/student/profile` | PUT | Self | Update profile (phone, address) |
| `/api/v1/student/change-password` | PUT | Self | Change password |

### Files to create:
- `app/Http/Controllers/Api/V1/StudentAppController.php`
- `app/Http/Resources/Api/V1/StudentProfileResource.php`
- `app/Http/Resources/Api/V1/StudentDashboardResource.php`
- `app/Http/Resources/Api/V1/StudentHomeworkResource.php`
- `app/Http/Resources/Api/V1/StudentLibraryResource.php`

---

## 5. Notification Enhancements (Week 8)

### Priority: Medium
### Dependencies: Auth improvements (device tracking for FCM)
### Effort: 5 days

### 5.1 Push Notification Support

| Endpoint | Method | Permission | Description |
|----------|--------|------------|-------------|
| `/api/v1/notifications/register-device` | POST | Self | Register FCM/APNs device token |
| `/api/v1/notifications/unregister-device` | POST | Self | Remove device token |

### 5.2 Notification Preferences

| Endpoint | Method | Permission | Description |
|----------|--------|------------|-------------|
| `/api/v1/notifications/preferences` | GET | Self | Current notification preferences |
| `/api/v1/notifications/preferences` | PUT | Self | Update notification preferences |

### 5.3 Push Delivery

| Task | Description |
|------|-------------|
| NotificationService enhancement | Add push channel using FCM/APNs |
| Queue job | `SendPushNotification` job for async delivery |
| Device token model | `notification_devices` table (user_id, token, platform, created_at) |

### Database migrations:
- `create_notification_devices_table`
- `create_notification_preferences_table`

### Files to create:
- `app/Modules/Notifications/Models/NotificationDevice.php`
- `app/Modules/Notifications/Models/NotificationPreference.php`
- `app/Jobs/SendPushNotification.php`
- `app/Http/Controllers/Api/V1/NotificationDeviceController.php`

---

## 6. Testing + Documentation (Weeks 9-10)

### Priority: High
### Dependencies: All API endpoints implemented
### Effort: 10 days

| Task | Description |
|------|-------------|
| API Feature Tests | PHPUnit tests for all new endpoints |
| OpenAPI Spec | Generate `openapi.yaml` documenting all endpoints |
| Postman Collection | Export ready-to-use Postman collection |
| Error Handling | Custom API exception renderers for common errors |
| Rate Limiting | Centralize in `AppServiceProvider` using `RateLimiter` facade |

---

## 7. Total Effort Summary

| Phase | Days | Endpoints |
|-------|------|-----------|
| 5.0 Auth Improvements | 3 | 3 new + 3 enhanced |
| 5.1 Transport API | 8 | 8 new |
| 5.2 Teacher App | 10 | ~18 new |
| 5.3 Student App | 8 | ~12 new |
| 5.4 Notifications | 5 | 2 new |
| 5.5 Testing + Docs | 10 | — |
| **Total** | **44 days** | **~43 new endpoints** |

---

## 8. Key Design Decisions

### 8.1 Route Structure
```
/api/v1/teacher/*      → Teacher app self-service
/api/v1/student/*      → Student app self-service
/api/v1/transport/*    → Transport module (admin + app)
```

Teacher and student routes use **self-scoping** — the authenticated user's identity determines the data returned, not a UUID parameter. This avoids UUID leakage and simplifies mobile app development.

### 8.2 Permission Scoping
- Teacher endpoints use `middleware('auth:sanctum', 'school')` + **role check** (not Spatie permission gates)
- Self-service endpoints check `$user->hasRole('Teacher')` at controller level
- Admin-overridable via `?admin=true` or admin permission fallback (future)

### 8.3 Existing Code Reuse
- **No controller rewrites.** New controllers call existing services/repositories:
  - `TransportApiController` → `TransportService` (existing)
  - `TeacherAppController` → `HomeworkService`, `ExamService`, `LeaveService` (existing)
  - `StudentAppController` → `StudentApiController` helpers (existing)

### 8.4 Self-Scoping Pattern
```php
// Teacher dashboard — no UUID in URL, resolved from auth
public function dashboard(Request $request): JsonResponse
{
    $teacher = $request->user()->teacher; // HasOne relationship
    // ... aggregate data via services
}
```

---

## 9. Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Permission overlap (admin vs mobile) | Medium | Create dedicated mobile permission set |
| Service method refactoring needed | Low | Services already return arrays/collections |
| Performance on large datasets | Low | Pagination pattern already established |
| Push notification delivery | Medium | Use Laravel notification channels + queue |
| Breaking existing parent app | High | Never modify existing parent endpoints; add parallel endpoints |
