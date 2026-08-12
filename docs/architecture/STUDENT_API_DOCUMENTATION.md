# STUDENT MOBILE API DOCUMENTATION

Version: v1.0
Base URL: `http://{host}:8000/api/v1`
Content-Type: `application/json` (multipart for file uploads)
Authentication: `Authorization: Bearer <token>`

---

## Response Envelope

All endpoints return a consistent JSON envelope:

### Success
```json
{
  "success": true,
  "message": "Human readable message",
  "data": { }
}
```

### Paginated Success
```json
{
  "success": true,
  "message": "...",
  "data": [ ],
  "meta": { "current_page": 1, "last_page": 2, "per_page": 15, "total": 30, "from": 1, "to": 15 },
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." }
}
```

### Validation Error (422)
```json
{
  "message": "The given data was invalid.",
  "errors": { "email": ["The email field is required."] }
}
```

### General Error
```json
{ "success": false, "message": "Error description", "errors": null }
```

| Status | Meaning |
|---|---|
| 200 | OK |
| 201 | Created |
| 401 | Unauthenticated |
| 403 | Forbidden / Inactive student / School context unresolved |
| 404 | Not found / No student linked / Ambiguous linkage / Archived student |
| 409 | Conflict (email already in use) |
| 422 | Validation error / Wrong current password |
| 429 | Rate limited |

---

## AUTH

### A1. `POST /auth/login` — Authenticate (any role)

**Headers:** `Content-Type: application/json`
**Auth:** Public (throttle `5,1`)

**Request Body:**
```json
{
  "email": "student.arjun.verma@example.com",
  "password": "password",
  "device_name": "student-mobile-android"
}
```

**Success Response (200)** — student users:
```json
{
  "success": true,
  "message": "Logged in successfully.",
  "data": {
    "token": "1|abc123...",
    "token_type": "Bearer",
    "user": { "id": 4, "name": "Arjun Verma", "email": "student.arjun.verma@example.com", "phone": null, "avatar_url": null, "status": "active" },
    "school_id": 1,
    "student": {
      "uuid": "0f6b...", "name": "Arjun Verma", "admission_no": "STU-0001",
      "class": "10", "section": "A", "roll_number": "1",
      "academic_year": "2026-27", "photo": "http://.../storage/students/arjun.jpg"
    }
  }
}
```

**Errors:** 401/422 invalid credentials, 403 inactive account, 429 throttled.

**curl:**
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"student.arjun.verma@example.com","password":"password"}'
```

---

### A2. `POST /student/login` — Authenticate student (Student App login)

**Headers:** `Content-Type: application/json`
**Auth:** Public (throttle `5,1`)

**Request Body:**
```json
{ "email": "student.arjun.verma@example.com", "password": "password", "device_name": "student-mobile" }
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Student logged in successfully.",
  "data": {
    "token": "1|abc...",
    "token_type": "Bearer",
    "role": "Student",
    "permissions": ["dashboard.view", "attendance.view", "fees.view", "exams.view", "notifications.view"],
    "user": { "id": 4, "name": "Arjun Verma", "email": "...", "phone": null, "avatar_url": null },
    "student": { "id": 3, "uuid": "...", "admission_no": "STU-0001", "full_name": "Arjun Verma", "photo_url": null },
    "student_id": 3,
    "student_uuid": "0f6b...",
    "school_id": 1,
    "academic_year": { "id": 2, "name": "2026-27" },
    "class": "10",
    "section": "A",
    "branding": { "school_name": "...", "school_logo": "...", "favicon": "...", "primary_color": "#2563EB", "secondary_color": "#1E40AF", "school_website": "...", "school_address": "...", "school_phone": "...", "app_name": "..." }
  }
}
```

**Errors:** 401/422 invalid credentials, 403 inactive, 404 no student linked, 429.

---

### A3. `GET /me` — Current user + student context

**Auth:** Bearer
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "user": { "id": 4, "name": "Arjun Verma", "email": "...", "phone": null, "avatar_url": null },
    "roles": ["Student"],
    "permissions": ["dashboard.view", "attendance.view", "fees.view", "exams.view", "notifications.view"],
    "student": { "uuid": "...", "name": "Arjun Verma", "admission_no": "STU-0001", "class": "10", "section": "A", "roll_number": "1", "academic_year": "2026-27", "photo": null }
  }
}
```

---

### A4. `PUT /me` — Update profile

**Request Body:**
```json
{ "phone": "9876501234", "email": "arjun@example.com", "address": "42, Lake View, Block 7" }
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Profile updated successfully.",
  "data": {
    "user": { "id": 4, "name": "Arjun Verma", "email": "arjun@example.com", "phone": "9876501234", "avatar_url": null },
    "student": { "uuid": "...", "name": "Arjun Verma", "class": "10", "section": "A", "roll_number": "1", "admission_no": "STU-0001", "academic_year": "2026-27", "photo": null }
  }
}
```

**Errors:** 422 invalid fields; 409 email already in use.

---

### A5. `PUT /me/change-password` — Change password

**Request Body:**
```json
{ "current_password": "oldpass", "new_password": "newpass123", "confirm_password": "newpass123" }
```

**Success:** 200 `{ "success": true, "message": "Password changed successfully." }`
**Errors:** 422 wrong current password or validation.

---

### A6. `POST /auth/logout` — Logout

**Auth:** Bearer. Revokes the current token. Returns `{ "success": true, "message": "Logged out successfully." }`.

---

### A7. `POST /auth/refresh` — Refresh token

**Request Body:** `{ "refresh_token": "..." }`
**Response:** `{ "token": "2|...", "token_type": "Bearer" }`

---

### A8. `GET /branding` — School branding (public)

```json
{
  "success": true,
  "message": "Branding retrieved successfully.",
  "data": { "school_name": "Sunrise International School", "school_logo": "...", "favicon": "...", "primary_color": "#2563EB", "secondary_color": "#1E40AF", "school_website": "https://...", "school_address": "...", "school_phone": "...", "app_name": "School ERP" }
}
```

---

## DASHBOARD

### D1. `GET /student/dashboard`

**Auth:** Bearer + student.linked

**Response Fields (verified against mobile `DashboardData`):**

```json
{
  "success": true,
  "message": "Student dashboard retrieved.",
  "data": {
    "student": { "id": 3, "uuid": "...", "full_name": "Arjun Verma", "photo_url": null },
    "students": [
      { "id": 3, "uuid": "...", "admission_no": "STU-0001", "full_name": "Arjun Verma", "gender": "male", "status": "active", "photo_url": null, "class": "10", "section": "A", "roll_no": 1 }
    ],
    "current_session": { "class": "10", "section": "A", "roll_no": 1, "academic_year": "2026-27" },
    "attendance": { "total_days": 65, "present_days": 58, "percentage": 89.2 },
    "attendance_summary": { "present": 58, "absent": 7, "total": 65, "percentage": 89.2 },
    "fees_summary": { "total": 12000, "paid": 8000, "pending": 4000 },
    "exam_results_summary": { "average": 82.5, "subjects": 5, "total_marks": 500, "obtained_marks": 412.5 },
    "leave_summary": { "pending": 1, "approved": 2, "rejected": 0, "total": 3 },
    "pending_homework_count": 4,
    "upcoming_exams": [ { "id": 12, "exam_name": "Mid Term", "exam_type": "Quarterly", "exam_date": "2026-09-15", "subject": "Mathematics" } ],
    "issued_books_count": 2,
    "notifications": { "unread_count": 3 },
    "recent_notifications": [
      { "id": 55, "title": "Fee Reminder", "message": "Your tuition fee...", "type": "fee_reminder", "type_label": "Fee Reminder", "priority": "medium", "is_read": false, "sent_at": "2 days ago", "read_at": null }
    ]
  }
}
```

---

## ATTENDANCE

### AT1. `GET /student/attendance?month=8&year=2026`

**Auth:** Bearer + student.linked

```json
{
  "success": true,
  "message": "Attendance records retrieved.",
  "data": {
    "student": { "id": 3, "uuid": "...", "admission_no": "STU-0001", "full_name": "Arjun Verma", "class": "10", "section": "A", "roll_no": 1 },
    "month": 8,
    "year": 2026,
    "total_records": 22,
    "summary": { "total_days": 22, "counts": { "present": 19, "absent": 1, "late": 1, "half_day": 1, "excused": 0 } },
    "records": [
      { "id": 301, "student_id": 3, "date": "2026-08-03", "attendance_date": "2026-08-03", "status": "present", "status_label": "Present", "remark": null, "remarks": null }
    ]
  }
}
```

**Validation:** `month` 1-12, `year` 2000-2100. **422** on invalid.

---

### AT2. `GET /student/attendance/summary?academic_year_id=2`

```json
{
  "success": true,
  "message": "Attendance summary retrieved.",
  "data": {
    "academic_year_id": 2,
    "total_days": 130, "present_days": 116, "present_count": 116, "percentage": 89.2,
    "breakdown": { "present": { "count": 116, "label": "Present" }, "absent": { "count": 14, "label": "Absent" }, "late": { "count": 0, "label": "Late" }, "half_day": { "count": 0, "label": "Half Day" }, "excused": { "count": 0, "label": "Excused" } }
  }
}
```

---

## FEES

### F1. `GET /student/fees?academic_year_id=2`

**Auth:** Bearer + student.linked

```json
{
  "success": true,
  "message": "Student fees retrieved.",
  "data": [
    {
      "id": 9, "student_id": 3, "student_name": "Arjun Verma", "admission_no": "STU-0001",
      "academic_year_id": 2, "academic_year": "2026-27", "fee_structure_id": 4,
      "status": "partial", "assigned_at": "2026-04-01T00:00:00.000000Z",
      "total_amount": 12000, "total_paid": 8000, "total_balance": 4000,
      "items": [
        { "id": 41, "fee_category_id": 1, "fee_category": "Tuition", "amount": 8000, "due_date": "2026-05-10", "paid": 8000, "balance": 0, "status": "paid" },
        { "id": 42, "fee_category_id": 5, "fee_category": "Miscellaneous", "amount": 4000, "due_date": "2026-08-10", "paid": 0, "balance": 4000, "status": "pending" }
      ]
    }
  ]
}
```

---

## HOMEWORK & ASSIGNMENTS

### HW1. `GET /student/homework`

```json
{
  "success": true,
  "message": "Homework list retrieved.",
  "data": {
    "student": { "id": 3, "uuid": "...", "full_name": "Arjun Verma", "class": "10", "section": "A", "roll_no": 1 },
    "homework": [
      { "id": 21, "subject_name": "Mathematics", "title": "Solve Ex 12.1", "description": "...", "assigned_date": "2026-08-10", "due_date": "2026-08-14", "attachment_url": "http://.../storage/homework/file.pdf", "status": "active" }
    ]
  }
}
```

### AS1. `GET /student/assignments`

```json
{ "success": true, "message": "Assignments retrieved.", "data": { "assignments": [
  { "id": 21, "title": "Solve Ex 12.1", "description": "...", "subject_name": "Mathematics", "assigned_date": "2026-08-10", "due_date": "2026-08-14", "status": "active", "attachment_url": null }
] } }
```

---

## TIMETABLE

### T1. `GET /student/timetable`

```json
{
  "success": true,
  "message": "Timetable retrieved.",
  "data": { "timetable": [
    { "day_of_week": 1, "day_name": "Monday", "slots": [
      { "id": 11, "period_label": "1", "start_time": "08:00", "end_time": "08:45", "subject": { "id": 2, "name": "Mathematics", "code": "MATH101" }, "teacher": { "id": 9, "name": "Mrs. Sharma" }, "room": "Room 12" }
    ] }
  ] }
}
```

`day_of_week`: 1=Monday … 6=Saturday. Mobile maps via `DAY_NAMES`.

---

## EXAMS, RESULTS, REPORT CARD

### E1. `GET /student/exams` (used by Results screen)

```json
{
  "success": true,
  "message": "Exam results retrieved.",
  "data": {
    "student": { "id": 3, "uuid": "...", "full_name": "Arjun Verma", "class": "10", "section": "A", "roll_no": 1 },
    "results_by_academic_year": [
      { "academic_year_id": 2, "results": [
        { "id": 88, "exam_id": 12, "exam_name": "Mid Term", "exam_type": "Quarterly", "exam_date": "2026-07-20", "subject_name": "Mathematics", "subject": "Mathematics", "maximum_marks": 100, "pass_marks": 33, "student_id": 3, "student_name": "Arjun Verma", "admission_no": "STU-0001", "marks_obtained": 82, "grade": "A", "remarks": null, "status": "Completed", "percentage": 82 }
      ] }
    ]
  }
}
```

> **Note:** This endpoint is the one the mobile Results screen calls (`fetchExamResults`). It returns results, **not** the exam list.

### E2. `GET /student/results`

```json
{
  "success": true,
  "message": "Exam results retrieved.",
  "data": {
    "student": { "id": 3, "uuid": "...", "full_name": "Arjun Verma" },
    "results_by_academic_year": [
      { "academic_year_id": 2, "results": [
        { "id": 88, "exam_name": "Mid Term", "exam_type": "Quarterly", "exam_date": "2026-07-20", "subject": "Mathematics", "maximum_marks": 100, "pass_marks": 33, "marks_obtained": 82, "grade": "A", "status": "completed", "status_label": "Completed", "remarks": null }
      ] }
    ]
  }
}
```

### E3. `GET /student/report-card`

```json
{
  "success": true,
  "message": "Report card retrieved.",
  "data": {
    "student": { "id": 3, "uuid": "...", "full_name": "Arjun Verma" },
    "class_section": { "class": "10", "section": "A", "roll_no": 1 },
    "academic_year": "2026-27",
    "results_by_type": [
      { "exam_type": "Quarterly", "results": [
        { "exam_name": "Mid Term", "exam_date": "2026-07-20", "subject": "Mathematics", "maximum_marks": 100, "pass_marks": 33, "marks_obtained": 82, "grade": "A", "status": "completed" }
      ] }
    ]
  }
}
```

### ES1. `GET /student/exam-schedule`

```json
{ "success": true, "message": "Exam schedule retrieved.", "data": { "schedules": [
  { "id": 31, "exam_name": "Mid Term", "subject_name": "Mathematics", "exam_date": "2026-09-15", "start_time": "10:00", "end_time": "12:00", "room": "Hall A", "maximum_marks": 100, "pass_marks": 33 }
] } }
```

---

## CALENDAR

### C1. `GET /student/calendar?month=9&year=2026&type=holiday`

```json
{ "success": true, "message": "Event calendar retrieved.", "data": {
  "month": 9, "year": 2026, "events": [
    { "id": 5, "title": "Independence Day", "description": "...", "event_type": "holiday", "event_type_label": "Holiday", "start_date": "2026-09-02", "end_date": "2026-09-02", "is_published": true, "location": null, "audience": "all" }
  ]
} }
```

`type` filter: `holiday, exam, school_event, ptm, sports_day, annual_day, field_trip, workshop, other`.

---

## DOCUMENTS

### D1. `GET /student/documents`

```json
{ "success": true, "message": "Documents retrieved.", "data": {
  "student": { "id": 3, "uuid": "...", "full_name": "Arjun Verma", "class": "10", "section": "A", "roll_no": 1 },
  "documents": [
    { "id": 7, "document_type": "birth_certificate", "document_type_label": "Birth Certificate", "title": "Birth Cert", "file_name": "birth.pdf", "file_size": 245760, "file_size_formatted": "240 KB", "mime_type": "application/pdf", "issue_date": null, "expiry_date": null, "is_verified": true, "verification_status_label": "Verified", "remarks": null, "download_url": "http://.../admin/documents/7/download", "created_at": "2026-05-01T10:00:00.000000Z" }
  ]
} }
```

> `download_url` requires the student to be authenticated and the document to belong to the student.

---

## TRANSPORT

### TR1. `GET /student/transport`

```json
{
  "success": true,
  "message": "Transport details retrieved.",
  "data": {
    "transport": {
      "vehicle_number": "KA-01-AB-1234", "vehicle_name": "Bus A", "vehicle_type": "bus", "driver_name": "Ramesh", "driver_mobile": "9876543210", "driver_license": "DL-12345",
      "route_name": "Route 1", "route_start": "Main Gate", "route_end": "School", "pickup_stop": "Main Gate", "drop_stop": "School",
      "pickup_time": "07:15", "drop_time": "15:45", "status": "active", "monthly_fee": 1500
    },
    "stops": [
      { "id": 51, "stop_name": "Main Gate", "pickup_time": "07:15", "drop_time": null, "sequence": 1, "is_student_stop": true }
    ]
  }
}
```

No assignment → `{ "transport": null, "stops": [] }`.

---

## LEAVE REQUESTS

### L1. `GET /student/leave-requests`

```json
{ "success": true, "message": "Leave requests retrieved.", "data": { "leave_requests": [
  { "id": 9, "student_id": 3, "student_name": "Arjun Verma", "leave_type_id": 1, "leave_type": "Sick Leave", "from_date": "2026-09-10", "to_date": "2026-09-11", "days": 2, "reason": "Fever", "status": "pending", "status_label": "Pending", "attachment_url": null, "remarks": null, "created_at": "2026-09-09T08:00:00.000000Z" }
] } }
```

### L2. `GET /student/leave-requests/{id}`

Same fields plus `approved_by`, `approved_at`.

### L3. `POST /student/leave-requests`

**Body:**
```json
{ "leave_type": "Sick Leave", "from_date": "2026-09-20", "to_date": "2026-09-21", "reason": "Doctor appointment", "attachment": "<multipart file>" }
```
or with `leave_type_id`. Response **201** with `{ leave_request: {...} }` (mapped shape).

**Validation (422):** `leave_type` required if no `leave_type_id`; `from_date` required, date, after_or_equal today; `to_date` required, after or equal `from_date`; `reason` required max 500; `attachment` mimes pdf/jpg/jpeg/png/doc/docx max 10MB.

### L4. `PUT /student/leave-requests/{id}`

Same body/validation. **409/422** if the request is not `pending`.

---

## LIBRARY

### LB1. `GET /student/library/books`

```json
{ "success": true, "message": "Issued books retrieved.", "data": { "total_issued": 2, "books": [
  { "id": 101, "book": { "id": 5, "title": "Mathematics", "isbn": "978-...", "author": { "id": 1, "name": "R.D. Sharma" } }, "issue_date": "2026-08-01", "due_date": "2026-09-01", "fine_amount": 0, "fine_paid": false, "notes": null }
] } }
```

### LB2. `GET /student/library/history?page=1&per_page=15`

Paginated envelope. Items: `{ id, book{id,title,isbn,author}, issue_date, due_date, return_date, status, fine_amount, fine_paid }`.

### LB3. `GET /student/library/fines`

```json
{ "success": true, "message": "Library fines retrieved.", "data": { "total_outstanding_price": 40, "total_items": 1, "fines": [
  { "id": 101, "book": { "id": 5, "title": "Mathematics", "isbn": "..." }, "issue_date": "...", "due_date": "...", "return_date": null, "fine_amount": 40, "fine_paid": false }
] } }
```

---

## NOTIFICATIONS

### N1. `GET /notifications?page=1`

**Auth:** Bearer (+ `notifications.view` permission — granted to Student role).

```json
{
  "success": true,
  "message": "Notifications retrieved.",
  "data": [
    { "id": 55, "title": "Fee Reminder", "body": "Your tuition fee...", "type": "fee_reminder", "type_label": "Fee Reminder", "priority": "medium", "status": "sent", "channel": "in_app", "is_read": false, "read_at": null, "delivery_status": "pending", "created_at": "2026-09-05T08:00:00.000000Z" }
  ],
  "meta": { "current_page": 1, "last_page": 3, "per_page": 15, "total": 45, "from": 1, "to": 15 },
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." }
}
```

**Mobile note:** `body` and `message` both exposed; `type` is mapped via `NOTIFICATION_TYPE_MAP` (`attendance_alert→attendance`, `fee_reminder→fees`, `exam_result_alert→result`, `announcement→general`, `timetable_update→general`).

### N2. `GET /notifications/unread`

```json
{ "success": true, "message": "Unread notifications retrieved.", "data": { "unread_count": 3, "notifications": [...] } }
```

### N3. `POST /notifications/{id}/read`

**Response:** `{ "success": true, "message": "Notification marked as read.", "data": { "notification": {...} } }`
**Errors:** 404 if not owned by the user.

### N4. `POST /notifications/read-all`

**Response:** `{ "success": true, "message": "All notifications marked as read." }`

---

## CIRCULARS / ANNOUNCEMENTS

### CR1. `GET /circulars?page=1`

**Auth:** Bearer. Self-scoped; targets `students` + `announcement` type + `sent` status. Paginated envelope. Each item:
`{ id, title, body, message, type, type_label, priority, sent_at, created_at, is_read, read_at, created_by: {id,name} | null }`

### CR2. `GET /circulars/{id}`

**Response:** single circular object (same shape).
**Errors:** 404.

### CR3. `POST /circulars/{id}/read`

**Response:** `{ "success": true, "message": "Circular marked as read.", "data": { circular } }`
**Errors:** 404.

---

## Common Error Reference

| Endpoint | 401 | 403 | 404 | 409 | 422 |
|---|---|---|---|---|---|
| All Bearer routes | No/invalid token | Inactive student / school context unresolved | No linked/ambiguous/archived student | Email conflict (`PUT /me`) | Validation |
| `GET /student/leave-requests/{id}` | same | same | Not found | — | — |
| `PUT /student/leave-requests/{id}` | same | same | Not found | Non-pending request | Validation |
| `GET /circulars/{id}` | same | — | Not found | — | — |
| `POST /notifications/{id}/read` | same | — | Not owned | — | — |

All errors return JSON — never HTML.

---

## Rate Limiting

- Public login endpoints: `throttle:5,1` (5/min).
- Authenticated API group: `throttle:60,1` (60/min).
- On exceed: **429** `{ "message": "Too Many Attempts.", "errors": null }`.
