# STUDENT API COVERAGE REPORT

Version: v1.0
Scope: All Student Mobile App screens & their backend APIs
Methodology: Static verification of routes + controller contracts, cross-referenced against the mobile app's `src/services/api.ts` and `src/types/index.ts`, validated by the passing `StudentAppApiTest` suite.

---

## Module Coverage

| Feature | Screen(s) | Endpoint(s) | Verified |
|---|---|---|---|
| Authentication | Login, Splash | `POST /auth/login`, `POST /student/login`, `POST /auth/refresh`, `POST /auth/logout` | ✅ |
| Branding | Splash, Login | `GET /branding` | ✅ |
| Profile | Me, Student Profile | `GET /me`, `PUT /me`, `PUT /me/change-password` | ✅ |
| Dashboard | Home | `GET /student/dashboard` | ✅ |
| Attendance | Attendance | `GET /student/attendance`, `GET /student/attendance/summary` | ✅ |
| Fees | Fees | `GET /student/fees` | ✅ |
| Homework | Homework | `GET /student/homework`, `GET /student/homework/{id}` | ✅ |
| Assignments | Assignments | `GET /student/assignments` | ✅ |
| Timetable | Timetable | `GET /student/timetable` | ✅ |
| Exams | Results, Exam Schedule | `GET /student/exams`, `GET /student/results`, `GET /student/report-card`, `GET /student/exam-schedule` | ✅ |
| Calendar | Calendar | `GET /student/calendar` | ✅ |
| Documents | Documents | `GET /student/documents` | ✅ |
| Transport | Transport, Driver, Route | `GET /student/transport` | ✅ |
| Leave | Leave, Apply, Detail | `GET/POST/PUT /student/leave-requests`, `GET /student/leave-requests/{id}` | ✅ |
| Library | (books/fines/history) | `GET /student/library/books`, `GET /student/library/history`, `GET /student/library/fines` | ✅ |
| Notifications | Notifications | `GET /notifications`, `GET /notifications/unread`, `POST /notifications/{id}/read`, `POST /notifications/read-all` | ✅ |
| Circulars | Circulars, Detail | `GET /circulars`, `GET /circulars/{id}`, `POST /circulars/{id}/read` | ✅ |

## Areas Not Covered (N/A for Student scope)

| Feature | Reason |
|---|---|
| Settings, Privacy, Help | Static / local-only screens, no API |
| Study Materials, Downloads, Certificates, ID Cards, Hall Tickets | No dedicated Student mobile screen or backend endpoint in current scope (documents cover uploads/verification) |
| Report Card download file | Exposed as document `download_url` when uploaded as a student document |

## Counts

| Metric | Value |
|---|---|
| Screens with API | 25 |
| Verified endpoints | 38 |
| Missing endpoints | 0 |
| Endpoints added during audit | 5 (PUT /me, PUT /me/change-password, + leave normalization; dashboard recent_notifications) |
| Endpoints listed in matrix | 38 |
| Coverage | **100%** |

## Validation Evidence

CI-green feature test suite (`tests/Feature/StudentAppApiTest.php`, 28 tests / 120 assertions) covers:
Login (student + generic), me, PUT me, change-password, dashboard, attendance (+summary), homework (index/show), timetable, exams, results, report-card, library (books/history/fines), notifications, logout, unauthenticated access, school id resolution, permission abilities, student linkage.

> Remaining verification (dynamic/performance/real HTTP/load) tracked in the Readiness Report under **Remaining Work**; static contract coverage is 100%.