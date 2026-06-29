# TEACHER API RECONCILIATION

## Existing Backend Routes (24 authenticated + 1 public)

| # | Method | URI | Controller Method | Exists |
|---|--------|-----|-------------------|--------|
| 1 | POST | `/api/v1/teacher/login` | `login` | ✅ |
| 2 | POST | `/api/v1/teacher/logout` | `logout` | ✅ |
| 3 | GET | `/api/v1/teacher/profile` | `profile` | ✅ |
| 4 | PUT | `/api/v1/teacher/profile` | `updateProfile` | ✅ |
| 5 | PUT | `/api/v1/teacher/change-password` | `changePassword` | ✅ |
| 6 | GET | `/api/v1/teacher/dashboard` | `dashboard` | ✅ |
| 7 | GET | `/api/v1/teacher/classes` | `classes` | ✅ |
| 8 | GET | `/api/v1/teacher/timetable` | `timetable` | ✅ |
| 9 | GET | `/api/v1/teacher/attendance/classes` | `attendanceClasses` | ✅ |
| 10 | GET | `/api/v1/teacher/attendance/students/{classSectionId}` | `attendanceStudents` | ✅ |
| 11 | POST | `/api/v1/teacher/attendance/mark` | `markAttendance` | ✅ |
| 12 | GET | `/api/v1/teacher/homework` | `homeworkIndex` | ✅ |
| 13 | POST | `/api/v1/teacher/homework` | `homeworkStore` | ✅ |
| 14 | GET | `/api/v1/teacher/homework/{id}` | `homeworkShow` | ✅ |
| 15 | PUT | `/api/v1/teacher/homework/{id}` | `homeworkUpdate` | ✅ |
| 16 | GET | `/api/v1/teacher/exams` | `examsIndex` | ✅ |
| 17 | GET | `/api/v1/teacher/exams/{id}` | `examsShow` | ✅ |
| 18 | POST | `/api/v1/teacher/exams/{id}/marks` | `examsStoreMarks` | ✅ |
| 19 | GET | `/api/v1/teacher/leave` | `leaveIndex` | ✅ |
| 20 | POST | `/api/v1/teacher/leave` | `leaveStore` | ✅ |
| 21 | GET | `/api/v1/teacher/leave-types` | `leaveTypes` | ✅ |
| 22 | GET | `/api/v1/teacher/notifications` | `notificationsIndex` | ✅ |
| 23 | POST | `/api/v1/teacher/notifications/{id}/read` | `notificationsRead` | ✅ |
| 24 | POST | `/api/v1/teacher/notifications/read-all` | `notificationsReadAll` | ✅ |

**Controller:** `app/Http/Controllers/Api/V1/TeacherAppController.php`
**Routes file:** `routes/modules/api/teacher-app.php`

---

## Frontend Calls vs Actual Routes

### Student Directory — MISSING ENDPOINTS

| Frontend Call | Backend Route | Status |
|---|---|---|
| `GET /teacher/students` | ❌ Does not exist | 404 |
| `GET /teacher/students/list` | ❌ Does not exist | 404 |
| `GET /teacher/my-students` | ❌ Does not exist | 404 |
| `GET /teacher/class/{id}/students` | ❌ Does not exist | 404 |
| `GET /teacher/classes` (returns teacher's classes only) | ✅ `classes` | Used for class picker only |

**Closest existing endpoint:** `GET /teacher/attendance/students/{classSectionId}` — returns students with attendance data. Can be reused for student directory if attendance field is ignored.

**Verdict:** A new backend endpoint IS required for Student Directory. No existing endpoint returns a plain student list without coupling to attendance data.

### Other Screens

| Screen | Expected Endpoint | Actual Backend Route | Status |
|--------|------------------|---------------------|--------|
| Dashboard | `GET /teacher/dashboard` | ✅ `dashboard` | ✅ |
| Attendance Classes | `GET /teacher/attendance/classes` | ✅ `attendance.classes` | ✅ |
| Attendance Students | `GET /teacher/attendance/students/{id}` | ✅ `attendance.students` | ✅ |
| Attendance Mark | `POST /teacher/attendance/mark` | ✅ `attendance.mark` | ✅ |
| Homework List | `GET /teacher/homework` | ✅ `homework.index` | ✅ |
| Homework Create | `POST /teacher/homework` | ✅ `homework.store` | ✅ |
| Homework Detail | `GET /teacher/homework/{id}` | ✅ `homework.show` | ✅ |
| Homework Update | `PUT /teacher/homework/{id}` | ✅ `homework.update` | ✅ |
| Exams List | `GET /teacher/exams` | ✅ `exams.index` | ✅ |
| Exams Detail | `GET /teacher/exams/{id}` | ✅ `exams.show` | ✅ |
| Exams Submit Marks | `POST /teacher/exams/{id}/marks` | ✅ `exams.marks` | ✅ |
| Leave List | `GET /teacher/leave` | ✅ `leave.index` | ✅ |
| Leave Create | `POST /teacher/leave` | ✅ `leave.store` | ✅ |
| Leave Types | `GET /teacher/leave-types` | ✅ `leave-types` | ✅ |
| Notifications | `GET /teacher/notifications` | ✅ `notifications.index` | ✅ |
| Timetable | `GET /teacher/timetable` | ✅ `timetable` | ✅ |
| Profile | `GET /teacher/profile` | ✅ `profile` | ✅ |
| Change Password | `PUT /teacher/change-password` | ✅ `change-password` | ✅ |
| Logout | `POST /teacher/logout` | ✅ `logout` | ✅ |

---

## Attendance Screen — API Response Structure

### Actual JSON from `GET /teacher/attendance/students/{classSectionId}`

```json
{
    "success": true,
    "data": {
        "students": [
            {
                "student_id": 1,
                "uuid": "a3403395-...",
                "admission_no": "ADM0001",
                "full_name": "Arjun Verma",
                "roll_no": "1",
                "photo_url": null,
                "attendance": null
            }
        ],
        "class_section": { "id": 1 },
        "date": "2026-06-24",
        "total_students": 2
    }
}
```

### Potential Frontend Mapping Mismatches

| API Field | Frontend Might Expect | Issue |
|-----------|----------------------|-------|
| `full_name` | `name` or `student_name` | Name blank — field name mismatch |
| `roll_no` (string) | `rollNumber` or numeric `roll_no` | Roll number blank — field name or type mismatch |
| `student_id` | `id` | React key prop — use `student_id` not index |
| `photo_url` | `photo` or `avatar` | Photo blank — field name mismatch |
| `attendance` (nested) | `attendance_status` (flat) | Selection state not mapping correctly |

### "Present selection affects multiple rows" — Root Cause

If the frontend uses array **index** as the React key instead of a unique `id`/`student_id`, toggling one row can cause React to reuse component state across rows. Fix: use `student_id` as the key prop.

---

## Recommended Fixes

### Student Directory (New Endpoint Required)

**New route:** `GET /api/v1/teacher/class/{classSectionId}/students`
**New method:** `classStudents(int $classSectionId)` in `TeacherAppController`

Returns students for a class section without attendance coupling. Reuses same query as `attendanceStudents` but omits the attendance lookup per student.

### Attendance Screen (Frontend Mapping)

Fix the following in the mobile app's `AttendanceStudent` interface / `StudentAttendanceCard` component:

```typescript
// Expected interface matching API response:
interface AttendanceStudent {
    student_id: number;       // ← use as React key
    uuid: string;
    admission_no: string;
    full_name: string;        // ← not "name"
    roll_no: string;          // ← not "rollNumber"
    photo_url: string | null; // ← not "photo"
    attendance: {
        id: number;
        status: string;
        status_label: string;
        remarks: string | null;
    } | null;
}
```

### Attendance State Bug

Ensure each `StudentAttendanceCard` uses `student_id` as the React key:

```tsx
{students.map(s => (
    <StudentAttendanceCard key={s.student_id} student={s} />
))}
```

Not:

```tsx
{students.map((s, i) => (
    <StudentAttendanceCard key={i} student={s} />  // ← BUG
))}
```

---

## Summary

| Category | Backend | Frontend | Action |
|----------|---------|----------|--------|
| Student Directory | Missing endpoint | Guessed 4 wrong URLs | **Create** `GET /teacher/class/{id}/students` |
| Attendance List | ✅ exists | Field mapping mismatch | **Fix** frontend interface to match API |
| Attendance State | N/A | Row key = index | **Fix** use `student_id` as React key |
| All other screens | ✅ 19 endpoints | ✅ Match | No action needed |
