# Role Permission Matrix

Permission source: `database/seeders/PermissionSeeder.php`.

| Role | Verified Permissions / Access |
|---|---|
| Super Admin | All permissions by seeder plus `Gate::before` super-admin bypass. |
| School Admin | All seeded permissions for each school. |
| Principal | Dashboard, students, teachers/reports, parents/reports, academics, attendance/reports, fees/reports, exams/publish/reports, timetable/reports, notifications, calendar, documents, homework, transport, reports, leave approval/create. |
| Teacher | Dashboard, students view, academics view, attendance create/update/reports, exams create/update/reports, timetable/reports, calendar view, documents view, homework CRUD. |
| Student | Dashboard, attendance view, fees view, exams view. |
| Parent | Dashboard, students, attendance, fees, exams, timetable, homework, calendar, documents, notifications, leave view/create, parents view. |
| Accountant | Dashboard, fees create/collect/update/reports, transport view, reports. |
| Librarian | Dashboard, library CRUD/export, reports. |
| Payroll Manager | Dashboard, payroll CRUD/export/process/lock, payslip view/generate/export, reports. |
| Receptionist | Dashboard, students view/create, parents view/create. |
| HR | Dashboard, teachers view/create/update/reports, reports, HR CRUD/verify. |
| Staff | Dashboard only. |
| Transport | Unable to Verify as a role. Permissions exist for transport module, but no dedicated Transport role was found in the seeder. |

## Route Protection

- Most admin route groups use `permission:*` middleware.
- `routes/web.php` wraps admin modules in `auth` and `school`.
- Reports route ownership is mixed and route listing currently fails, so complete route protection cannot be fully verified.

## Recommendations

1. Add explicit Transport role if required by product scope.
2. Add role/page matrix tests for all sidebar links and CRUD actions.
3. Separate mobile app permissions from admin permissions.
4. Verify every role against menus, route middleware, controller policies and API responses.

