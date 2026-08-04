# Policy Report — Supporting Roles (Phase 07)

## New Policies
None. Phase 07 does not introduce any new authorization policies.

## Existing Policies Applied

| Policy | Scope | Roles Affected |
|--------|-------|----------------|
| `dashboard.view` | Grants access to the dashboard route and stat card visibility | Accountant, Librarian, Receptionist, Staff |
| `fees.view` | Controls access to Fees module in sidebar | Accountant |
| `fees.reports` | Controls access to fee reports quick action | Accountant |
| `transport.view` | Controls Transport nav item visibility | Accountant |
| `library.view` | Controls Library module access | Librarian |
| `reports.view` | Controls Reports nav item visibility | Librarian |
| `students.view` | Controls Students module access | Receptionist |
| `parents.view` | Controls Parents module access | Receptionist |
| `timetable.view` | Controls Timetable nav item visibility | Staff |
| `attendance.view` | Controls Attendance nav item visibility | Staff |
| `notifications.view` | Controls Notifications nav item visibility | All four roles |

## Policy Enforcement Points
- **SidebarBuilder** — each `buildFor*()` method gates individual nav items via `$this->item()` with permission checks (returns `null` if permission denied).
- **sidebar.blade.php** — each `@elseif` block wraps nav items in `@can` directives.
- **DashboardBuilder** stat card routes and quick action routes are rendered unconditionally; actual access control is enforced at the route/middleware level by the existing permission system.

## Summary
No new policies required. All sidebar items are permission-gated using the existing application-wide permission system.
