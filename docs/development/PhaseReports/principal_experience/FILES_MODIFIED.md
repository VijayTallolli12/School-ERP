# Files Modified — Principal Experience (Phase 03)

## 1. `database/seeders/PermissionSeeder.php`

**Lines affected:** 87–99 (Principal role array)

**Change:** Added three permissions to the Principal role:

| Permission | Purpose |
|------------|---------|
| `leave_management.view` | View all leave requests in the school |
| `leave_management.approve` | Approve or reject leave requests |
| `leave_management.create` | Create leave requests on behalf of others |

These permissions are seeded per-school via the existing `School::each(...)` loop. No migration needed.

---

## 2. `app/Modules/Dashboard/Services/Builders/PrincipalDashboardBuilder.php`

**Lines affected:** 36, 43, 81–93, 130

| Change | Details |
|--------|---------|
| **Stat card replacement** (line 36) | `TeacherAttendance` query replaced with `LeaveRequest::where('status', 'pending')->count()` — 4th stat card now shows **Pending Leaves** instead of Teacher Attendance |
| **Pending Approvals widget** (lines 81–93) | New widget `pending_approvals` of type `list` renders the 5 most recent pending leave requests, gated behind `leave_management.approve` permission |
| **Quick action gating** (line 130) | **Approve Leave** quick action gated behind `leave_management.view` permission |

---

## 3. `app/Modules/Dashboard/Services/SidebarBuilder.php`

**Lines affected:** 15–16, 87–108

| Change | Details |
|--------|---------|
| **Dispatch order** (line 15–16) | `hasRole('Principal')` check added **before** the default Admin path in `build()`. Principal is now matched early and returned immediately — no fall-through to Admin logic |
| **New method `buildForPrincipal()`** (lines 87–108) | Returns an array with a single "Principal" section containing 12 menu items, each gated by `$this->item()` permission checks |

### Principal Sidebar Menu Items

| # | Label | Route | Permission |
|---|-------|-------|------------|
| 1 | Dashboard | `admin.dashboard` | `dashboard.view` |
| 2 | Attendance | `admin.attendance.index` | `attendance.view` |
| 3 | Timetable | `admin.timetable.index` | `timetable.view` |
| 4 | Exams | `admin.exams.index` | `exams.view` |
| 5 | Students | `admin.students.index` | `students.view` |
| 6 | Teachers | `admin.teachers.index` | `teachers.view` |
| 7 | Homework | `admin.homework.index` | `homework.view` |
| 8 | Calendar | `admin.calendar.index` | `academic_calendar.view` |
| 9 | Fees | `admin.fees.index` | `fees.view` |
| 10 | Reports | `reports.attendance.index` | `reports.view` |
| 11 | Leave Approvals | `admin.leave-requests.index` | `leave_management.view` |
| 12 | Notifications | `admin.notifications.index` | `notifications.view` |

---

## 4. `resources/views/layouts/partials/sidebar.blade.php`

**Lines affected:** 121–254 (new `@elseif` block)

**Change:** Inserted a dedicated Principal sidebar section between the Teacher (`@if`) and default Admin (`@else`) blocks. The block:

- Shows a "Principal" nav header
- Renders the same 12 items as `buildForPrincipal()`, each wrapped in `@can` directives
- Includes an **AI Workspace** sub-section with "Ask ERP" modal trigger and "Executive Copilot" link
- Uses `@elseif(auth()->user()->hasRole('Principal'))` so Principal is matched before the generic `@else` fallthrough

---

## 5. `app/Modules/Notifications/Services/NotificationService.php`

**Line affected:** 150

**Change:** Added a new match arm to `resolveTargetUserIds()`:

```php
'principals' => $query->whereHas('roles', fn ($q) => $q->where('name', 'Principal'))->pluck('id')->all(),
```

This enables sending notifications targeted specifically to all users with the **Principal** role.

---

## 6. `app/Modules/Leave/Services/LeaveService.php`

**Lines affected:** 147–155 (within `notifyAdmins()`)

**Change:** The `notifyAdmins()` method now creates **two** notifications instead of one:

| # | Target Type | Title | Priority |
|---|-------------|-------|----------|
| 1 | `admins` | New Leave Request | medium |
| 2 | `principals` | New Leave Request | medium |

Both carry the same message body. This ensures leave requests are surfaced to both school administrators and principals simultaneously.
