# API Review

API score: 76 / 100.

## Implemented API Areas

- Base prefix: `/api/v1`.
- Public auth endpoints: general login, teacher login, student login, driver login.
- Authenticated API middleware: Sanctum, school context, throttle.
- Modules included: auth, dashboard, students, parents, teachers, attendance, fees, exams, notifications, transport, teacher app, student app, driver app.
- API Resources exist for users, teachers, students, parents, notifications, homework, fees, attendance, exams, exam results.
- Tests cover driver, student app, teacher app, live transport, live attendance, and realtime infrastructure.

## Issues

| Severity | Issue | Evidence | Recommendation |
|---|---|---|---|
| High | One API/realtime attendance test fails. | `LiveAttendanceTest > realtime status endpoint`. | Fix attendance status calculation/scoping. |
| High | Parent/mobile APIs are coupled to admin permissions. | Existing mobile API audit docs note this. | Create explicit mobile scopes/policies. |
| Medium | API documentation is mostly markdown, not executable OpenAPI. | Docs exist under `docs/api`; no OpenAPI spec verified. | Generate OpenAPI from routes/resources/tests. |
| Medium | Pagination and response shape consistency not fully verified. | Mixed controllers/resources and direct JSON responses. | Standardize response envelopes and pagination. |
| Medium | Route list failure blocks complete route verification. | `php artisan route:list` fails. | Add route list CI gate. |

## Recommendations

1. Standardize all API responses through `ApiBaseController` or resources.
2. Add OpenAPI documentation and schema tests.
3. Add per-role authorization tests for every API group.
4. Add versioning/deprecation policy before mobile production release.

