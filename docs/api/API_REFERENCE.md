# API Reference

Version: 1.0.0

Revision date: 2026-07-08

This reference documents the current web routes and module route groups implemented in the repository. The primary entry points are the route files under routes/modules and the web and API route bootstraps.

## 1. Authentication

| Method | Route | Purpose | Auth | Notes |
| --- | --- | --- | --- | --- |
| GET | /login | Login page | No | Served by the auth module |
| POST | /login | Authenticate user | No | Validates credentials and records login activity |
| POST | /logout | Logout | Yes | Invalidates session |

## 2. Dashboard and Navigation

| Method | Route | Purpose | Auth | Notes |
| --- | --- | --- | --- | --- |
| GET | /admin/dashboard | Dashboard home | Yes | Role-based dashboard builder |
| GET | /admin/notifications/bell | Notification bell data | Yes | Lightweight notification endpoint |

## 3. Core Module Route Groups

| Module | Route Prefix | Notes |
| --- | --- | --- |
| Students | /admin/students | Student CRUD, search, data endpoints |
| Teachers | /admin/teachers | Teacher CRUD, attendance, leave, reports |
| Attendance | /admin/attendance | Attendance records and reporting |
| Homework | /admin/homework | Homework management |
| Exams | /admin/exams | Exams, schedules, marks, results |
| Fees | /admin/fees | Fee categories, structures, assignments, collections |
| Payroll | /admin/payroll | Payroll runs and payslips |
| Library | /admin/library | Books, categories, authors, publishers |
| Transport | /admin/transport | Vehicles, drivers, routes, assignments |
| HR | /admin/hr | Employee management and documents |
| Notifications | /admin/notifications | Notification management |
| Settings | /admin/settings | System settings |
| RBAC | /admin/roles, /admin/permissions | Roles and permissions |

## 4. Authentication and Permissions

Most update and create actions are protected with permission middleware such as:

- permission:students.create
- permission:teachers.update
- permission:fees.collect
- permission:transport.create
- permission:hr.create

## 5. Request and Response Notes

- Most list endpoints return data tables or JSON payloads for UI consumption.
- Many write actions return redirect responses or JSON-style payloads depending on the controller implementation.
- Route names are defined in the corresponding module route files.

## 6. Example Request

```http
GET /admin/students/data HTTP/1.1
Authorization: Bearer <token>
X-School-Id: 1
```

## 7. Example Response

```json
{
  "draw": 1,
  "recordsTotal": 120,
  "recordsFiltered": 120,
  "data": []
}
```

## 8. Error Handling

Common failure modes include:

- 403 for missing school context or missing permissions
- 404 for missing resource identifiers
- 500 for unexpected service failures

## 9. API Versioning

The current implementation is primarily web-first. API usage is available through the Laravel API routes bootstrap and module-specific endpoints where they are implemented.
