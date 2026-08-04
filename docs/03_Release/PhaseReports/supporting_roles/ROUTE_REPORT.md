# Route Report — Supporting Roles (Phase 07)

## New Routes
None. Phase 07 does not introduce any new routes.

## Reused Routes

| Route Name | Module | Used By |
|------------|--------|---------|
| `admin.dashboard` | Dashboard | Accountant, Librarian, Receptionist, Staff |
| `admin.fees.index` | Fees | Accountant (stat card link + quick action) |
| `admin.fees.reports` | Fees | Accountant (quick action) |
| `admin.library.index` | Library | Librarian (stat card link + quick actions) |
| `admin.students.index` | Students | Receptionist (stat card link + quick action) |
| `admin.parents.index` | Parents | Receptionist (quick action) |
| `admin.attendance.index` | Attendance | Staff (quick action) |
| `admin.leave-requests.index` | Leave | Staff (quick action) |
| `admin.timetable.index` | Timetable | Staff (quick action) |
| `admin.notifications.index` | Notifications | Accountant, Librarian, Receptionist, Staff (sidebar nav) |
| `admin.transport.index` | Transport | Accountant (sidebar nav) |
| `reports.fees.index` | Reports | Accountant (sidebar nav) |
| `reports.attendance.index` | Reports | Librarian (sidebar nav) |

## Role-Based Access
All reused routes are protected by the existing `admin.*` route group middleware (typically `auth` + `role` or `permission` middleware). The sidebar Blade templates and `SidebarBuilder` add an additional presentation-layer gate via `@can`/permission checks, but the definitive access control remains at the route/controller level.
