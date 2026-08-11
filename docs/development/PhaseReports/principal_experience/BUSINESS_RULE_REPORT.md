# Business Rule Report — Principal Experience (Phase 03)

## Business Rules Implemented

### BR-P01 — School-Wide Leave Visibility
> **Rule:** The Principal can view **all** leave requests in the school, regardless of which user or student submitted them.
- **Enforcement:** `leave_management.view` permission assigned to Principal role in `PermissionSeeder.php:98`
- **Implementation:** `LeaveController` index action is gated by `leave_management.view` middleware; no user/student filter is applied for Principal role
- **Sidebar:** "Leave Approvals" menu item in `buildForPrincipal()` (line 103) links to `admin.leave-requests.index`

### BR-P02 — Principal Can Approve/Reject Leave
> **Rule:** The Principal can approve or reject any pending leave request in the school.
- **Enforcement:** `leave_management.approve` permission assigned to Principal role in `PermissionSeeder.php:98`
- **Implementation:** `LeaveService::approve()` and `LeaveService::reject()` are called from `LeaveController` actions gated by `leave_management.approve` middleware
- **Policy:** `LeaveRequestPolicy::approve()` checks for `leave_management.approve` permission
- **UI:** "Approve Leave" quick action on dashboard (`PrincipalDashboardBuilder.php:130`) is gated by `leave_management.view`

### BR-P03 — Principal Notified on New Leave Requests
> **Rule:** When any user submits a new leave request, the Principal receives an in-app notification.
- **Enforcement:** `LeaveService::notifyAdmins()` (line 147–155) creates a notification with `target_type = 'principals'`
- **Implementation:** `NotificationService::resolveTargetUserIds()` resolves `'principals'` to all users with the Principal role (line 150)

### BR-P04 — School-Wide Dashboard Stats
> **Rule:** The Principal dashboard displays school-wide aggregate statistics, not personal data.
- **Implementation:** `PrincipalDashboardBuilder::buildStatCards()` queries:
  - Total students (school-wide via `StudentCollector::totalCount($this->schoolId)`)
  - Total teachers (school-wide via `TeacherCollector::totalCount($this->schoolId)`)
  - Today's attendance rate (school-wide via `AttendanceCollector::todayAttendanceRate($this->schoolId)`)
  - Pending leaves count (across all users in the school)

### BR-P05 — Dedicated Principal Sidebar
> **Rule:** The Principal sees a dedicated sidebar focused on oversight functions, distinct from the Admin sidebar.
- **Enforcement:** `SidebarBuilder::build()` checks `hasRole('Principal')` at line 15, before falling through to the generic Admin path
- **Implementation:** `buildForPrincipal()` (lines 87–108) returns a single "Principal" section with 12 oversight-oriented items
- **Blade:** `sidebar.blade.php` has an `@elseif(hasRole('Principal'))` block at line 121

### BR-P06 — Pending Leaves Count on Dashboard
> **Rule:** The Principal stat card shows the total count of all pending leave requests across the school.
- **Implementation:** `PrincipalDashboardBuilder.php:36` — `LeaveRequest::where('status', 'pending')->count()`
- **UI:** Displayed as the 4th stat card with a `calendar-clock` icon and "warning" color

### BR-P07 — Pending Leave Approvals Widget
> **Rule:** The Principal dashboard displays a widget listing the 5 most recent pending leave requests for quick action.
- **Implementation:** `PrincipalDashboardBuilder.php:81–93` — "Pending Leave Approvals" list widget, gated by `leave_management.approve`
- **Empty state:** Displays "No pending leave approvals" when no pending requests exist

## Business Rules Unchanged

| Rule ID | Description | Still Valid? |
|---------|-------------|-------------|
| BR-T01 | Teacher views only own leave requests | ✅ Yes — Teacher role unchanged |
| BR-T02 | Teacher receives notification on leave approval/rejection | ✅ Yes — `notifyUser()` in LeaveService |
| BR-A01 | Admin can manage all leave types | ✅ Yes — Admin sidebar untouched |
| BR-A02 | Admin receives new-leave notifications | ✅ Yes — `target_type = 'admins'` preserved |
