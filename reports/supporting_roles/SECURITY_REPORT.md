# Security Report — Supporting Roles (Phase 07)

## Data Isolation
- **School-level isolation**: All dashboard builders operate on models that apply a `school_id` global scope (e.g., `FeePayment`, `StudentFee`, `Book`, `BookIssue`, `Student`). This ensures users from one school never see data from another school.
- **FeeCollector**: The `dashboardStats()` method in `FeeCollector` accepts `$schoolId` as a parameter, ensuring fee data is scoped to the correct school.

## Permission Gating

### Sidebar Level
- `SidebarBuilder::build()` uses early-return role checks (`hasRole`) before the generic permission-based fallback.
- Each `buildFor*()` method calls `$this->item()` which returns `null` when the user lacks the required permission, causing `array_filter` to remove the nav item.
- The sidebar Blade template enforces the same permission checks via `@can` directives.

### Dashboard Level
- Dashboard builder stat cards and quick actions are rendered for the assigned role, but the actual route/middleware layer enforces permissions at the controller level.
- No sensitive data is leaked through stat card values — all values are aggregate counts or sums, not individual records.

## Role Mapping
- `DashboardFactory::ROLE_PRIORITY` maps each role string to a concrete builder class.
- Only users with a matching `hasRole()` check will reach their respective dashboard builder.
- If a user has no recognized role, a `403` abort is thrown (fallback at the end of `make()`).

## Threat Mitigation

| Threat | Mitigation |
|--------|-----------|
| Cross-role data access | `$schoolId` scoping on all queries; role check in `DashboardFactory::make()` |
| Privilege escalation via sidebar | Each nav item permission-gated by `@can` + `$this->item()` permission param |
| Unauthorized route access | Route-level middleware (auth + role/permission) independent of UI |
| Information disclosure | Dashboard shows only aggregate counts/sums, never individual records |

## Summary
Phase 07 introduces no new attack surface. Existing `school_id` scoping, role-based dashboard resolution, and permission-gated sidebars provide adequate security for the four supporting roles.
