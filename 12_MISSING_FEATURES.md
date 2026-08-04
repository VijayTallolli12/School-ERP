# Missing Features

## Not Started

- Assignments module.
- Hostel module.
- General inventory module.
- Visitor management.
- Front office.
- Downloads.
- News.
- SMS gateway integration.
- Student promotion workflow.
- Alumni module.

## Partially Implemented

- Admissions: student admission fields/reports exist, but no admissions pipeline.
- Certificates: teacher certificate upload exists, but no certificate generation module.
- Events: calendar events exist, but no standalone events/news publishing module.
- Email: SMTP settings exist, but no communication/email campaign workflow verified.
- Backup: docs exist, but no runnable backup job/UI verified.
- Analytics: charts/reports exist, but no dedicated analytics module.
- Audit logs: activitylog exists, but no management UI verified.
- AI features: services exist, but AI query log migration pending.

## Production-Blocking Missing Work

1. Fix route/container bootstrap.
2. Apply pending migrations.
3. Fix failing attendance realtime test.
4. Add missing production environment validation.
5. Add deployment smoke tests for routes, auth, migrations, dashboard, fees, attendance, reports and APIs.

