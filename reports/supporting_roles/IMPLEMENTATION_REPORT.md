# Phase 07 — Supporting Roles

## Phase Name
Supporting Roles (Phase 07)

## Objective
Create dedicated dashboard builders and sidebar sections for the Accountant, Librarian, Receptionist, and Staff roles. Each supporting role receives a focused dashboard showing only the stats and actions relevant to their domain, and a minimal sidebar that surfaces only the modules they need access to.

## New Files

| File | Path |
|------|------|
| AccountantDashboardBuilder | `app/Modules/Dashboard/Services/Builders/AccountantDashboardBuilder.php` |
| LibrarianDashboardBuilder | `app/Modules/Dashboard/Services/Builders/LibrarianDashboardBuilder.php` |
| ReceptionistDashboardBuilder | `app/Modules/Dashboard/Services/Builders/ReceptionistDashboardBuilder.php` |

## Files Modified

| File | Changes |
|------|---------|
| `DashboardFactory.php` | Added `Accountant` → `AccountantDashboardBuilder`, `Librarian` → `LibrarianDashboardBuilder`, `Receptionist` → `ReceptionistDashboardBuilder` entries to `ROLE_PRIORITY` map |
| `SidebarBuilder.php` | Added `buildForAccountant()`, `buildForLibrarian()`, `buildForReceptionist()`, `buildForStaff()` methods + early-return `if` blocks in `build()` |
| `sidebar.blade.php` | Added `@elseif(auth()->user()->hasRole('Accountant'))`, `@elseif(auth()->user()->hasRole('Librarian'))`, `@elseif(auth()->user()->hasRole('Receptionist'))`, `@elseif(auth()->user()->hasRole('Staff'))` blade sections with role-specific nav items |

## Database Changes
None.

## Architecture Decisions
- Each supporting role dashboard extends `BaseDashboardBuilder` and overrides `buildStatCards()`, `buildQuickActions()`, `buildWidgets()`, and `buildCharts()` as needed.
- Dashboards show **only** domain-relevant stats — no extraneous modules.
- Sidebar methods each return a single-section array with a role-specific header and a small set of navigation items gated by permission checks.
- `DashboardFactory` uses the same role-priority pattern as existing roles; no new routing or middleware required.
- Staff dashboard was pre-existing (unchanged in this phase); included here for role coverage completeness.
