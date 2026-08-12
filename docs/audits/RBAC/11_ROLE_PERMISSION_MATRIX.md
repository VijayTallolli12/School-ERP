# Role Permission Matrix

Permission source: `database/seeders/PermissionSeeder.php`.
Last audited: 2026-08-13. See
`docs/audits/RBAC/COMPLETE_STAFF_ROLE_ACCESS_AUDIT.md` for the full report.

| Role | Verified Permissions / Access |
|---|---|
| Super Admin | All permissions by seeder plus `Gate::before` super-admin bypass. |
| School Admin | All seeded permissions for each school. |
| Principal | Dashboard, students, teachers/reports, parents/reports, academics, attendance/reports, fees/view, fees/reports, exams/publish/reports, timetable/reports, notifications, calendar, documents, homework, transport, reports, leave approval/create, admissions, student lifecycle. No settings/users/roles. |
| Teacher | Dashboard, students view, academics view, attendance create/update/reports, exams create/update/reports, timetable/reports, calendar view, documents view, homework CRUD, own payslips, own leave. No fees/payroll/access-control/settings. |
| Student | External role — **no ERP web login**. Mobile app only. |
| Parent | External role — **no ERP web login**. Mobile app only. |
| Accountant | Dashboard, fees create/collect/update/reports, transport view, reports. No payroll/access-control/settings. |
| Librarian | Dashboard, library CRUD/export, reports. No fees/payroll/access-control/settings. |
| Payroll Manager | Dashboard, payroll CRUD/export/process/lock, payslip view/generate/export, reports. |
| Receptionist | Dashboard, students view/create, parents view/create, admissions view/create/update/verify. No payroll/fees/access-control. |
| HR | Dashboard, teachers view/create/update/reports, reports, HR CRUD/verify. No fees/payroll/access-control. |
| Staff | Dashboard only. |
| Driver | Dashboard, transport view/update/location.update, notifications. No students/payroll/fees/access-control. |

## Route Protection

- The entire `/admin/*` group is wrapped in `auth` + `school` + `staff`
  middleware (`routes/web.php:14`). `staff` blocks Parent/Student roles.
- All admin modules carry Spatie `permission:*` middleware (verified via
  `route:list --json`).
- `/reports/*` is staff-gated with `auth:sanctum, verified, school, staff,
  permission:reports.view` plus per-report abilities.
- AI Agents routes are role-gated to `Super Admin|School Admin|Principal|HR`.

## Verification

- `tests/Feature/StaffRoleAccessTest.php` (16 tests): login for every staff
  role, Parent/Student blocked from login + admin + reports, and module
  access boundaries for every role.
- `tests/Feature/AiSecurityTest.php`: AI tenant isolation + role authorization.

## Recommendations (historical)

1. ~~Add explicit Transport role~~ — resolved: the `Driver` role is the
   existing transport staff role; no duplicate was created.
2. Role/page matrix tests — resolved via `StaffRoleAccessTest`.
3. Separate mobile app permissions from admin permissions — mobile routes are
   under `/api/v1` with Sanctum; web ERP is staff-gated.
4. Verify every role against menus, route middleware, controller policies and
   API responses — resolved (see report).

