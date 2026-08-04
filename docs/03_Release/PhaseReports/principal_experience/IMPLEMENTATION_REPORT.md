# Implementation Report — Principal Experience (Phase 03)

## Phase Name
Principal Experience (Phase 03)

## Objective
Transform the Principal Portal into a school-wide oversight and approval command center.

## Files Modified

| File | Change |
|------|--------|
| `database/seeders/PermissionSeeder.php` | Added `leave_management.view`, `leave_management.approve`, `leave_management.create` to the **Principal** role |
| `app/Modules/Dashboard/Services/Builders/PrincipalDashboardBuilder.php` | Replaced Teacher Attendance stat with **Pending Leaves** stat; added **Pending Leave Approvals** widget; gated **Approve Leave** quick action behind permission |
| `app/Modules/Dashboard/Services/SidebarBuilder.php` | Added `buildForPrincipal()` method returning 12 oversight-focused menu items; Principal is now checked **before** Admin in the `build()` dispatch |
| `resources/views/layouts/partials/sidebar.blade.php` | Added `@elseif(hasRole('Principal'))` Blade block with dedicated sidebar section including Leave Approvals, Reports, Executive Copilot |
| `app/Modules/Notifications/Services/NotificationService.php` | Added `'principals'` target type to `resolveTargetUserIds()` match block |
| `app/Modules/Leave/Services/LeaveService.php` | `notifyAdmins()` now dispatches a **second** notification with `target_type = 'principals'` alongside `'admins'` |

## New Files Created
None.

## Database Changes
None (new permissions are assigned via the existing `PermissionSeeder`; no migrations were required).

## Architecture Decisions

- **Dedicated Principal Sidebar**: Rather than reusing the Admin sidebar, Principal gets its own `@elseif` Blade block and `buildForPrincipal()` method. This ensures the menu is focused on oversight (Leave Approvals, Reports, Timetable, Fees, etc.) without cluttering it with Administration sub-menus.
- **Leave Approval Workflow**: Principal can view all pending leave requests (`leave_management.view`) and approve/reject them (`leave_management.approve`). The **Pending Leave Approvals** widget on the dashboard surfaces the 5 most recent pending requests.
- **School-Wide Dashboard Stats**: Unlike Teacher/Student/Parent dashboards (which are scoped to the individual), the Principal dashboard shows **school-wide** aggregates: total students, total teachers, today's attendance rate, pending leaves.
- **Notification Target Type**: A new `'principals'` target type was added to the notification resolver, enabling notifications to be sent exclusively to all Principal users.
- **Dual Notification on Leave**: When a leave is submitted, both admins and principals are notified (two separate notifications), ensuring either party can act first.

## Business Rules Implemented

1. A Principal can view **all** leave requests in the school (no teacher-style self-scoping).
2. A Principal can **approve** or **reject** any pending leave request.
3. Principals receive an in-app notification when a new leave request is submitted.
4. The Principal dashboard renders school-wide stats, not personal stats.
5. The Principal sidebar surfaces oversight-focused items: Attendance, Timetable, Exams, Students, Teachers, Homework, Calendar, Fees, Reports, Leave Approvals, Notifications, and AI Workspace.

## Completion Status

| Area | Status |
|------|--------|
| Dashboard (stat cards, widgets, quick actions, charts) | ✅ Complete |
| Sidebar (dedicated Principal section) | ✅ Complete |
| Leave Approval Workflow (view, approve, reject) | ✅ Complete |
| Notification Delivery (principals target) | ✅ Complete |
| Permission Seeding (Principal role) | ✅ Complete |
| Regression (Teacher, Admin, Parent roles unaffected) | ✅ Verified |
