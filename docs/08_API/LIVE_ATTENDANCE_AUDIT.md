# Live Attendance — Audit

## Overview

Phase 5.4B integrates the existing Push Notification Infrastructure (Phase 5.4A) with the attendance module to deliver real-time attendance notifications to students, parents, and teachers.

No attendance logic was duplicated. All marking goes through `AttendanceService::markAttendance()`. The event system dispatches `AttendanceMarked` after each write.

## Workflow

```
Teacher marks attendance (TeacherAppController::markAttendance)
        │
        ▼
AttendanceService::markAttendance()
  └─ Upserts attendance record (student_id + date)
  └─ Logs activity via spatie/activitylog
        │
        ▼
AttendanceMarked event dispatched
  └─ schoolId, studentId, status, date, studentName, markedAt
        │
        ├──▶ CreateDatabaseNotification
        │     └─ Creates notification with message "Ananya Sharma marked PRESENT at 09:02 AM."
        │     └─ Attaches to student user + parent guardian users
        │
        ├──▶ SendPushNotification
        │     └─ Sends FCM push to all devices of student + parent users
        │
        └──▶ LogNotificationActivity
              └─ Writes structured log entry
```

## Events

| Event | When | Target Users |
|-------|------|--------------|
| `AttendanceMarked` | Student attendance recorded via Teacher App API | Student's own user account + parent guardian users |
| `TeacherAttendanceMarked` | Teacher attendance recorded (admin panel) | Teacher's own user account |

## Notification Flow

### Student Attendance (`AttendanceMarked`)

**Target user resolution** (`resolveStudentAndParentUserIds`):
1. Get the `Student` record with eager-loaded `parents.user`
2. Add the student's own `user_id` (if set)
3. Add each guardian's `user_id` (if set)
4. Return unique, non-empty array

**Message format**: `Ananya Sharma marked PRESENT at 09:02 AM.`

### Teacher Attendance (`TeacherAttendanceMarked`)

**Target user resolution** (`resolveTeacherUserIds`):
1. Query `User` where `teacher.id` matches
2. Return teacher's user ID

**Message format**: `Test Teacher marked PRESENT at 08:30 AM.`

## API

### `GET /api/v1/attendance/realtime-status`

**Auth**: Sanctum (any authenticated user)

**Query params**: `date` (optional, defaults to today)

**Response**:
```json
{
  "success": true,
  "data": {
    "date": "2026-06-23",
    "student_attendance": {
      "total": 45,
      "summary": { "present": 40, "absent": 3, "late": 2, "half_day": 0, "excused": 0 }
    },
    "teacher_attendance": {
      "total": 12,
      "summary": { "present": 11, "absent": 1, "late": 0, "half_day": 0, "excused": 0 }
    },
    "recent_activity": [
      { "id": 1, "student_name": "Ananya Sharma", "status": "present",
        "status_label": "Present", "marked_by": "Test Teacher",
        "marked_at": "09:02 AM", "date": "2026-06-23" }
    ]
  }
}
```

## Modified Files

| File | Change |
|------|--------|
| `app/Events/AttendanceMarked.php` | Added `studentName`, `markedAt` fields |
| `app/Events/TeacherAttendanceMarked.php` | New event |
| `app/Listeners/CreateDatabaseNotification.php` | Added `TeacherAttendanceMarked` support; improved message format; parent+student resolution |
| `app/Listeners/SendPushNotification.php` | Same improvements |
| `app/Listeners/LogNotificationActivity.php` | Added `TeacherAttendanceMarked` handling |
| `app/Providers/EventServiceProvider.php` | Registered `TeacherAttendanceMarked` |
| `app/Http/Controllers/Api/V1/TeacherAppController.php` | Dispatch `AttendanceMarked` after each attendance record |
| `app/Http/Controllers/Api/V1/AttendanceRealtimeController.php` | New: `GET /attendance/realtime-status` |
| `routes/modules/api/attendance.php` | Added `realtime-status` route |

## Security

| Concern | Implementation |
|---------|---------------|
| Event dispatch | Dispatched server-side, no user input in event payload (student name loaded from DB) |
| Realtime status | Requires `auth:sanctum` + `school` middleware |
| School isolation | SchoolContext scopes all attendance queries |
| Parent notification | Only parents linked via `parent_student` pivot receive notifications |

## Coverage

| Test | Status |
|------|--------|
| `AttendanceMarked` dispatched with correct fields | ✅ |
| `TeacherAttendanceMarked` dispatched | ✅ |
| Attendance event creates notification for student + parent | ✅ |
| Teacher attendance creates notification for teacher | ✅ |
| Teacher mark attendance API dispatches event | ✅ |
| Realtime status returns correct summary | ✅ |
| Realtime status empty day | ✅ |
| Notification message matches format | ✅ |
| Unauthenticated access fails | ✅ |

**Total: 9 tests**

## Implementation Score

| Criteria | Status |
|----------|--------|
| No duplicate attendance logic | ✅ — Event dispatched after `AttendanceService::markAttendance()` |
| Uses existing event system | ✅ — Reuses `AttendanceMarked` event, all 3 existing listeners |
| Uses existing FCM service | ✅ — `SendPushNotification` uses `PushNotificationService` |
| Push notifications delivered | ✅ — Student + parent users notified |
| Ready for Teacher App | ✅ — Dispatch integrated in `TeacherAppController::markAttendance()` |
| Ready for Student App | ✅ — Student's own user notified if exists |
| Ready for Parent App | ✅ — Parent guardian users notified via `Guardian.user_id` |
| Supports teacher attendance | ✅ — `TeacherAttendanceMarked` event + realtime status |

## Future Enhancements

- Add `ShouldQueue` to listeners for async notification dispatch
- Add `marked_at` timestamp column to `attendances` table for precise time tracking
- Add webhook delivery for third-party integrations
- Add daily attendance summary digest (scheduled job)
- Add rate limiting to realtime-status endpoint
