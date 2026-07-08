# Policy Report — Principal Experience (Phase 03)

## Policies Referenced / Affected

### 1. `LeaveRequestPolicy::approve()`

**Status:** Referenced (no changes required)

**How it works:**
- The `approve()` policy gate is invoked automatically by Laravel's `Gate::allowIf()` or `$this->authorize('approve', $leaveRequest)` in the controller.
- Internally it checks whether the authenticated user has the `leave_management.approve` permission.
- Since **Principal** now possesses `leave_management.approve` (via `PermissionSeeder.php:98`), the policy grants access.

**Relevant permission seeds:**
```php
'Principal' => [
    // ...
    'leave_management.view',
    'leave_management.approve',
    'leave_management.create',
],
```

### 2. `LeaveRequestPolicy::viewAny()`

**Status:** Referenced (no changes required)

**How it works:**
- The `viewAny()` policy gate controls access to the leave requests index.
- It checks for `leave_management.view` permission.
- Since **Principal** now possesses `leave_management.view`, the policy grants access to the full list of all leave requests in the school.
- This policy replaces the Teacher-style self-scoping that normally filters `leave_requests.user_id = auth()->id()`.

### 3. Other Leave Policies (view, create, update, delete)

**Status:** Not directly affected by Phase 03

- **Principal** has `leave_management.create` (for creating leave on behalf of others).
- **Principal** does **not** have `leave_management.update` or `leave_management.delete` — these remain Admin-only.
- Principal can update the status of a leave (approve/reject) via the dedicated `approve()` and `reject()` methods in `LeaveService`, not via a generic `update()`.

## Permission-to-Policy Mapping

| Permission | Policy Method | Used By |
|-----------|--------------|---------|
| `leave_management.view` | `viewAny()`, `view()` | Principal, Admin |
| `leave_management.approve` | `approve()` | Principal, Admin |
| `leave_management.create` | `create()` | Principal, Teacher, Parent |
| `leave_management.update` | `update()` | Admin only |
| `leave_management.delete` | `delete()` | Admin only |

## Authorization Flow for Leave Approval

```
Request → Route (middleware: auth, permission:leave_management.approve)
        → Controller::approve()
        → $this->authorize('approve', $leaveRequest)
        → LeaveRequestPolicy::approve() ← checks permission
        → LeaveService::approve()        ← business logic
        → NotificationService::create()   ← notify user
```

No new policies were created. All Phase 03 changes reuse the existing `LeaveRequestPolicy` by granting the required permissions to the **Principal** role.
