# Push Notification Infrastructure — Audit

## Architecture

```
┌─────────────┐     ┌──────────────────┐     ┌──────────────────┐
│  App Event  │────▶│  EventService    │────▶│  Listeners       │
│  Dispatched │     │  Provider        │     │                  │
└─────────────┘     └──────────────────┘     ├─ CreateDBNotif   │
                                              ├─ SendPushNotif   │
                                              └─ LogActivity     │
                                                    │
                    ┌───────────────────────────────┘
                    ▼
          ┌─────────────────────┐
          │ PushNotification    │
          │ Service             │
          │                     │
          │ sendToUser()        │
          │ sendToUsers()       │
          │ sendToTopic()       │
          └────────┬────────────┘
                   │
                   ▼
          ┌─────────────────────┐
          │ Firebase Cloud      │
          │ Messaging (FCM)     │
          └─────────────────────┘
```

## Events

| Event | Payload | Purpose |
|-------|---------|---------|
| `AttendanceMarked` | `schoolId`, `studentId`, `status`, `date`, `extra` | Student attendance recorded |
| `HomeworkAssigned` | `homeworkId`, `classSectionId`, `title`, `dueDate`, `studentIds`, `extra` | New homework created |
| `ExamPublished` | `examId`, `examName`, `classSectionId`, `studentIds`, `extra` | Exam results published |
| `FeeReminderGenerated` | `studentFeeId`, `studentId`, `parentUserId`, `amountDue`, `dueDate`, `extra` | Fee payment reminder |
| `AgentExecutionCompleted` | `executionId`, `agentName`, `status`, `summary`, `extra` | AI agent task finished |

## Listeners

| Listener | Responsibility |
|----------|---------------|
| `CreateDatabaseNotification` | Creates a `notifications` DB record and attaches target users via `notification_user` pivot |
| `SendPushNotification` | Resolves user IDs from event payload, queries `user_devices` for FCM tokens, sends via `PushNotificationService` |
| `LogNotificationActivity` | Writes structured log entries via `Log::info()` |

## Device Lifecycle

### Registration
```
POST /api/v1/devices/register
Authorization: Bearer <token>
Body: { device_type, platform, device_token }
```
- Upserts by `(user_id, device_token)` — idempotent
- Updates `last_seen_at` on each registration
- No duplicate tokens per user

### Unregistration
```
POST /api/v1/devices/unregister
Authorization: Bearer <token>
Body: { device_token }
```
- Deletes matching record
- Returns 404 if token not found

### Storage
- Table: `user_devices`
- Indexed on `(user_id, device_token)` (unique), `(user_id, last_seen_at)`, `device_token`

## PushNotificationService

| Method | Description |
|--------|-------------|
| `sendToUser(userId, title, body, data)` | Sends push to all devices of a single user |
| `sendToUsers(userIds, title, body, data)` | Sends push to all devices of multiple users (batched) |
| `sendToTopic(topic, title, body, data)` | Sends push to a Firebase topic |

### FCM Integration
- Uses legacy HTTP API (`fcm.googleapis.com/fcm/send`) with server key auth
- Configured via `config/services.php` → `services.fcm.*`
- Environment variables: `FCM_SERVER_KEY`, `FCM_ENABLED`, `FCM_TIMEOUT`
- Disabled by default (`FCM_ENABLED=false`)
- Non-blocking: failures are logged, never thrown
- Timeout: 10s (configurable via `FCM_TIMEOUT`)

## Security

| Concern | Implementation |
|---------|---------------|
| Device token auth | All device endpoints require `auth:sanctum` |
| Token ownership | Scoped to `request()->user()->id` — cannot register/remove other users' devices |
| No key in code | FCM server key from `env()`, never hardcoded |
| Disabled by default | `FCM_ENABLED=false` — safe for local/test |
| Fail silent | Service returns `bool`, exceptions caught and logged |

## Coverage

| Test | File | Status |
|------|------|--------|
| Device register (new) | RealTimeInfrastructureTest | ✅ |
| Device register (update existing) | RealTimeInfrastructureTest | ✅ |
| Device unregister | RealTimeInfrastructureTest | ✅ |
| Device unregister (unknown token) | RealTimeInfrastructureTest | ✅ |
| Multiple device tokens | RealTimeInfrastructureTest | ✅ |
| Unread count endpoint | RealTimeInfrastructureTest | ✅ |
| Unauthenticated access (all 3 endpoints) | RealTimeInfrastructureTest | ✅ |
| AttendanceMarked event dispatched | RealTimeInfrastructureTest | ✅ |
| HomeworkAssigned event dispatched | RealTimeInfrastructureTest | ✅ |
| ExamPublished event dispatched | RealTimeInfrastructureTest | ✅ |
| FeeReminderGenerated event dispatched | RealTimeInfrastructureTest | ✅ |
| Attendance event creates DB notification | RealTimeInfrastructureTest | ✅ |

**Total: 12 tests**

## Implementation Score

| Criteria | Status |
|----------|--------|
| No breaking changes | ✅ — New files only, no existing code modified (except `config/services.php` + route file) |
| FCM integrated | ✅ — Config-driven, disabled by default |
| Device registration working | ✅ — Register, unregister, upsert, multi-token |
| Push service operational | ✅ — `sendToUser`, `sendToUsers`, `sendToTopic` |
| Ready for Live Attendance | ✅ — `AttendanceMarked` event wired |
| Ready for Live Transportation | ✅ — Event pattern extensible to `LocationUpdated` / `TripEvent` |
| Ready for Teacher App | ✅ — Homework/Exam events notify class-section students |
| Ready for Student App | ✅ — Attendance/Fee/Exam events notify student/parent users |

## Future Enhancements

- Replace legacy FCM HTTP API with HTTP v1 (OAuth2)
- Add `ShouldQueue` to listeners for async dispatch
- Add retry + dead-letter for failed FCM sends
- Add webhook delivery channel
- Add rate limiting to device endpoints
- Expire stale device tokens (no `last_seen_at` > 90 days)
