# STUDENT API CONTRACT MATRIX

Version: v1.0
Base URL: `http://{host}:8000/api/v1`
Auth: `Authorization: Bearer <token>` (Sanctum)
Common student middleware: `auth:sanctum`, `school`, `throttle:60,1`, `student.linked`

> **Envelope** — success: `{ success, message, data }`; paginated: `{ success, message, data:[...], meta, links }`; error: `{ success:false, message, errors? }`.
> **`student.linked`** — requires exactly one active linked Student for the authenticated user; otherwise 401/403/404 via `StudentLinkageException`.

| # | Screen | Feature | Endpoint | Method | Authentication | Permission | Request Params | Request Body | Response Fields | Error Codes | Pagination | Status |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| 1 | Login | Authenticate | `POST /auth/login` | POST | Public (`throttle:5,1`) | — | — | `email`, `password`, `school_id?`, `device_name?` | `token, token_type, user, school_id, student{...}` | 200, 401, 403, 422, 429 | No | ✅ |
| 2 | Login (student login) | Authenticate student | `POST /student/login` | POST | Public (`throttle:5,1`) | — | — | `email`, `password`, `device_name?` | `token, token_type, role, permissions, user, student, student_id, student_uuid, school_id, academic_year, class, section, branding` | 200, 401, 403, 404, 422, 429 | No | ✅ |
| 3 | Me (profile) | Current user | `GET /me` | GET | Sanctum (Bearer) | — | — | — | `user, roles, permissions, student{...}` | 200, 401 | No | ✅ |
| 4 | Edit profile | Update profile | `PUT /me` | PUT | Sanctum | — | — | `phone?`, `email?`, `address?`, `profile_photo?` | `user`, `student` | 200, 401, 409, 422 | No | ✅ |
| 5 | Change password | Change password | `PUT /me/change-password` | PUT | Sanctum | — | — | `current_password`, `new_password`, `confirm_password` | success only | 200, 401, 409, 422 | No | ✅ |
| 6 | Logout | Revoke token | `POST /auth/logout` | POST | Sanctum | — | — | — | success only | 200, 401 | No | ✅ |
| 7 | Token refresh | Refresh access token | `POST /auth/refresh` | POST | Sanctum | — | — | `refresh_token` | `token`, `token_type` | 200, 401 | No | ✅ |
| 8 | Branding | School branding | `GET /branding` | GET | Public | — | — | — | `school_name, school_logo, favicon, primary_color, secondary_color, school_website, school_address, school_phone, app_name` | 200 | No | ✅ |
| 9 | Dashboard | Aggregated dashboard | `GET /student/dashboard` | GET | Sanctum + student.linked | — | — | — | `student, students[], current_session, attendance, attendance_summary, fees_summary, exam_results_summary, leave_summary, pending_homework_count, upcoming_exams, issued_books_count, notifications, recent_notifications` | 200, 401, 403, 404 | No | ✅ |
| 10 | Attendance | Monthly attendance | `GET /student/attendance` | GET | Sanctum + student.linked | — | `month?`, `year?` | — | `student, month, year, total_records, summary{total_days,counts}, records[{id,student_id,date,attendance_date,status,status_label,remark,remarks}]` | 200, 401, 403, 404, 422 | No | ✅ |
| 12 | Attendance | Monthly summary | `GET /student/attendance/summary` | GET | Sanctum + student.linked | — | `academic_year_id?` | — | `academic_year_id, total_days, present_days, present_count, percentage, breakdown{status:{count,label}}` | 200, 401, 403, 404 | No | ✅ |
| 13 | Fees | Fee structure & items | `GET /student/fees` | GET | Sanctum + student.linked | — | `academic_year_id?` | — | `[StudentFee{id, student_id, total_amount, total_paid, total_balance, status, assigned_at, items[]}]` | 200, 401, 403, 404 | No | ✅ |
| 14 | Homework | Homework list | `GET /student/homework` | GET | Sanctum + student.linked | — | `subject_id?` | — | `student, homework: [{id,subject_name,title,description,assigned_date,due_date,attachment_url,status}]` | 200, 401, 403, 404 | No | ✅ |
| 17 | Homework | Homework detail | `GET /student/homework/{id}` | GET | Sanctum + student.linked | `id` | — | — | `id,title,description,subject,class_section,assigned_date,due_date,attachment_url,status,created_at` | 200, 401, 403, 404 | No | ✅ |
| 16 | Assignments | Assignment list | `GET /student/assignments` | GET | Sanctum + student.linked | — | — | — | `assignments: [{id,title,description,subject_name,assigned_date,due_date,status,attachment_url}]` | 200, 401, 403, 404 | No | ✅ |
| 17 | Timetable | Weekly timetable | `GET /student/timetable` | GET | Sanctum + student.linked | — | — | — | `timetable: [{day_of_week,day_name,slots:[{id,period_label,start_time,end_time,subject,teacher,room}]}]` | 200, 401, 403, 404 | No | ✅ |
| 18 | Exams | Exam results by year | `GET /student/exams` | GET | Sanctum + student.linked | — | `academic_year_id?` | — | `student, results_by_academic_year: [ExamResultResource[]]` | 200, 401, 403, 404 | No | ✅ |
| 19 | Results | Results by year | `GET /student/results` | GET | Sanctum + student.linked | — | `academic_year_id?` | — | `student, results_by_academic_year:[{academic_year_id,results[]}]` | 200, 401, 403, 404 | No | ✅ |
| 20 | Report card | Report card by term | `GET /student/report-card` | GET | Sanctum + student.linked | — | — | — | `student, class_section, academic_year, results_by_type:[{exam_type,results[]}]` | 200, 401, 403, 404 | No | ✅ |
| 21 | Exam schedule | Exam schedule | `GET /student/exam-schedule` | GET | Sanctum + student.linked | — | — | — | `schedules:[{id,exam_name,subject_name,exam_date,start_time,end_time,room,maximum_marks,pass_marks}]` | 200, 401, 403, 404 | No | ✅ |
| 22 | Calendar | Academic calendar | `GET /student/calendar` | GET | Sanctum + student.linked | `month?`, `year?`, `type?` | — | `month, year, events:[{id,title,description,event_type,start_date,end_date,is_published,location,audience}]` | 200, 401, 403, 404 | No | ✅ |
| 23 | Documents | Student documents | `GET /student/documents` | GET | Sanctum + student.linked | — | — | — | `student, documents:[{id,document_type,title,file_name,file_size,file_size_formatted,mime_type,is_verified,issue_date,expiry_date,remarks,download_url,created_at}]` | 200, 401, 403, 404 | No | ✅ |
| 24 | Transport | Transport dashboard | `GET /student/transport` | GET | Sanctum + student.linked | — | — | — | `transport{vehicle_number,driver_name,route_name,pickup_time,...}`, `stops[]` | 200, 401, 403, 404 | No | ✅ |
| 25 | Leave | Leave list | `GET /student/leave-requests` | GET | Sanctum + student.linked | — | — | — | `leave_requests:[{id,student_id,student_name,leave_type_id,leave_type,from_date,to_date,days,reason,status,status_label,attachment_url,remarks,created_at}]` | 200, 401, 403, 404 | No | ✅ |
| 26 | Leave | Leave detail | `GET /student/leave-requests/{id}` | GET | Sanctum + student.linked | `id` | — | `leave_request:{... + approved_by,approved_at}` | 200, 401, 403, 404 | No | ✅ |
| 27 | Leave | Submit leave | `POST /student/leave-requests` | POST | Sanctum + student.linked | — | `leave_type_id?/leave_type`, `from_date`, `to_date`, `reason`, `attachment?` | `leave_request` | 200/201, 401, 403, 404, 422 | No | ✅ |
| 28 | Leave | Update leave | `PUT /student/leave-requests/{id}` | PUT | Sanctum + student.linked | `id` | same as submit | `leave_request` | 200, 401, 403, 404, 409, 422 | No | ✅ |
| 29 | Library | Issued books | `GET /student/library/books` | GET | Sanctum + student.linked | — | — | `total_issued, books[]` | 200, 401, 403, 404 | No | ✅ |
| 30 | Library | Library history | `GET /student/library/history` | GET | Sanctum + student.linked | `page?`, `per_page?` | — | paginated `{...}` | 200, 401, 403, 404 | Yes | ✅ |
| 31 | Library | Library fines | `GET /student/library/fines` | GET | Sanctum + student.linked | — | — | `total_outstanding_price, total_items, fines[]` | 200, 401, 403, 404 | No | ✅ |
| 32 | Notifications | Notification list | `GET /notifications` | GET | Sanctum (permission:notifications.view) | — | `page?` | paginated `notifications[]` | 200, 401, 403 | Yes | ✅ |
| 33 | Notifications | Unread count | `GET /notifications/unread` | GET | Sanctum | — | — | `unread_count, notifications[]` | 200, 401 | No | ✅ |
| 34 | Notifications | Mark one read | `POST /notifications/{id}/read` | POST | Sanctum | — | `id` | — | `notification` | 200, 401, 404 | No | ✅ |
| 35 | Notifications | Mark all read | `POST /notifications/read-all` | POST | Sanctum | — | — | — | success only | 200, 401 | No | ✅ |
| 36 | Circulars | Circular list | `GET /circulars` | GET | Sanctum | — | `page?` | — | paginated `circulars[]` | 200, 401 | Yes | ✅ |
| 37 | Circulars | Circular detail | `GET /circulars/{id}` | GET | Sanctum | `id` | — | `circular{...}` | 200, 401, 404 | No | ✅ |
| 38 | Circulars | Mark circular read | `POST /circulars/{id}/read` | POST | Sanctum | `id` | — | `circular` | 200, 401, 404 | No | ✅ |

---

## Coverage Summary

| Metric | Status |
|---|---|
| Total screens | 28 (25 API + 3 static/local) |
| Total API endpoints | 38 |
| Verified endpoints | 38 |
| Missing endpoints on backend | 0 |
| Protected by Sanctum | ✅ all authenticated routes |
| School scoped (school middleware) | ✅ |
| Common response envelope | ✅ `{success,message,data,+meta/links}` |
| Validation present | ✅ (422 on invalid) |

## Notes / Discrepancies Resolved This Phase

1. **`PUT /me` and `PUT /me/change-password`** did not exist → **added** (`ApiAuthController@updateProfile`, `ApiAuthController@changePassword`). Mobile app calls these without fallback; previously guaranteed 404.
2. **Leave `POST/PUT`** returned raw model JSON; normalized to the same mapped `leave_request` shape (string `leave_type`, ISO dates, `status_label`, `attachment_url`, `approved_*`) as list/detail.
3. **Dashboard `recent_notifications`** added as an array (mobile normalizer reads `payload.notifications`/`payload.recent_notifications` as an array; `notifications.unread_count` kept for existing consumers).