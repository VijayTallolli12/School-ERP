# GAP ANALYSIS — Current Implementation vs Ideal Business Workflow

**Document:** GAP_ANALYSIS.md
**Date:** 2026-07-07

---

## Gap Severity Legend

| Priority | Definition | Resolution Timeline |
|----------|-----------|-------------------|
| Critical | System crash, data loss, security breach, or workflow completely broken | Immediate / 24 hours |
| High | Major feature missing, incorrect business logic, or significant data integrity risk | 1 week |
| Medium | Missing convenience features, non-critical workflow gaps, or UI improvements | 1 month |
| Low | Cosmetic, nice-to-have, or edge cases | 3 months |

---

## Gap 1: Dashboard Factory — Unmapped Roles

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-001 |
| **Priority** | **Critical** |
| **Module** | Dashboard |
| **Current Behavior** | `DashboardFactory.php` maps only 6 of 12 roles to dashboard builders. If a user's highest-priority role is Student, Accountant, Librarian, Payroll Manager, Receptionist, or HR, `DashboardFactory::make()` iterates `ROLE_PRIORITY`, finds no match, and calls `abort(403)`. |
| **Expected Behavior** | Every role must have a dashboard builder, OR a graceful fallback (redirect to a generic landing page) must exist for unmapped roles. |
| **Business Impact** | Users with unmapped roles cannot access the system after login — they receive a 403 error. This affects 6 out of 12 roles (50% of role types). |
| **Recommendation** | Add dashboard builders for all 6 unmapped roles. At minimum, create simple builders that display role-appropriate stat cards and quick links. |
| **Implementation Effort** | Medium (6 new builder classes + DTOs) |

---

## Gap 2: AI Agent Routes — No Authorization

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-002 |
| **Priority** | **Critical** |
| **Module** | AI Agents |
| **Current Behavior** | All 6 routes in `routes/modules/ai_agents.php` have NO permission middleware. Any authenticated user can access agent execution, history, and preview. |
| **Expected Behavior** | AI Agent routes should be restricted to Super Admin and School Admin roles only. At minimum, a `permission:ai_agents.view` or role-based middleware. |
| **Business Impact** | Any teacher, student, parent, or staff user can execute AI agents. This could lead to unauthorized system operations, data access, or resource abuse. |
| **Recommendation** | Add middleware: `permission:ai_agents.view` (new permission) or `middleware:role:Super Admin|School Admin`. |
| **Implementation Effort** | Low (add middleware to route group + seed permission) |

---

## Gap 3: AI Assistant Routes — Unguarded

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-003 |
| **Priority** | **Critical** |
| **Module** | AI Assistant |
| **Current Behavior** | Routes in `routes/modules/ai_assistant.php` are outside the `admin.*` group entirely. Only `auth` middleware — no permission check. Any authenticated user can access AI assistant. |
| **Expected Behavior** | AI Assistant should be inside the admin group with role-appropriate access control. At minimum, permission middleware should guard the route. |
| **Business Impact** | All authenticated users (including students and parents) have unrestricted access to the AI assistant. This could lead to unauthorized data queries. |
| **Recommendation** | Move routes inside the admin group or add explicit permission middleware. Implement role-scoped query limits (see AI_ROLE_MATRIX.md). |
| **Implementation Effort** | Low (move route registration + add middleware) |

---

## Gap 4: Unmapped Role — Student Dashboard

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-004 |
| **Priority** | **Critical** |
| **Module** | Dashboard |
| **Current Behavior** | Student role has `dashboard.view` permission but no dashboard builder. Login fails with 403. |
| **Expected Behavior** | Students should have a dashboard showing their timetable, attendance, homework, exams, and library status. |
| **Business Impact** | Students cannot use the web application. They are entirely locked out of the system. |
| **Recommendation** | Create `StudentDashboardBuilder` with personal schedule, attendance status, pending homework, upcoming exams, and library info. |
| **Implementation Effort** | Medium |

---

## Gap 5: Unmapped Role — Accountant Dashboard

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-005 |
| **Priority** | **Critical** |
| **Module** | Dashboard |
| **Current Behavior** | Accountant role has `dashboard.view` permission but no dashboard builder. Login fails with 403. |
| **Expected Behavior** | Accountants should have a finance-focused dashboard showing collections, dues, defaulters, and fee reports. |
| **Business Impact** | Accountants cannot use the web application. |
| **Recommendation** | Create `AccountantDashboardBuilder` with fee collection KPIs, defaulter list, and daily collection trends. |
| **Implementation Effort** | Medium |

---

## Gap 6: Unmapped Role — Librarian Dashboard

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-006 |
| **Priority** | **Critical** |
| **Module** | Dashboard |
| **Current Behavior** | Librarian role has `dashboard.view` permission but no dashboard builder. Login fails with 403. |
| **Expected Behavior** | Librarians should have a library operations dashboard showing issued books, overdue items, and catalog statistics. |
| **Business Impact** | Librarians cannot use the web application. |
| **Recommendation** | Create `LibrarianDashboardBuilder` with book issue KPIs, overdue alerts, and catalog stats. |
| **Implementation Effort** | Medium |

---

## Gap 7: Unmapped Role — Payroll Manager Dashboard

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-007 |
| **Priority** | **Critical** |
| **Module** | Dashboard |
| **Current Behavior** | Payroll Manager role has `dashboard.view` permission but no dashboard builder. Login fails with 403. |
| **Expected Behavior** | Payroll Managers should have a payroll operations dashboard showing pending runs, salary statistics, and processing status. |
| **Business Impact** | Payroll Managers cannot use the web application. |
| **Recommendation** | Create `PayrollManagerDashboardBuilder` with payroll KPIs, processing status, and salary trends. |
| **Implementation Effort** | Medium |

---

## Gap 8: Unmapped Role — Receptionist Dashboard

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-008 |
| **Priority** | **Critical** |
| **Module** | Dashboard |
| **Current Behavior** | Receptionist role has `dashboard.view` permission but no dashboard builder. Login fails with 403. |
| **Expected Behavior** | Receptionists should have a front-desk dashboard showing inquiries, visitors, and quick student lookup. |
| **Business Impact** | Receptionists cannot use the web application. |
| **Recommendation** | Create `ReceptionistDashboardBuilder` with visitor metrics, new inquiries, and quick access to student records. |
| **Implementation Effort** | Medium |

---

## Gap 9: Unmapped Role — HR Dashboard

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-009 |
| **Priority** | **Critical** |
| **Module** | Dashboard |
| **Current Behavior** | HR role has `dashboard.view` permission but no dashboard builder. Login fails with 403. |
| **Expected Behavior** | HR should have a teacher management dashboard showing teacher attendance, leave requests, and contract status. |
| **Business Impact** | HR cannot use the web application. |
| **Recommendation** | Create `HRDashboardBuilder` with teacher attendance KPIs, pending leaves, and contract alerts. |
| **Implementation Effort** | Medium |

---

## Gap 10: Permission Strings — Missing Definitions

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-010 |
| **Priority** | **High** |
| **Module** | Teachers (Attendance & Leave) |
| **Current Behavior** | Teacher FormRequests check `teachers.attendance.mark`, `teachers.attendance.view`, `teachers.attendance.update`, `teachers.leave.create`, `teachers.leave.update` — but these permission strings are NOT defined in `PermissionSeeder`. |
| **Expected Behavior** | All permission strings checked in FormRequests and controllers must be defined in the seeder. |
| **Business Impact** | Teacher attendance and leave management are effectively broken — `can()` checks always return false. Teachers cannot manage attendance or leave for their records. |
| **Recommendation** | Add missing permission strings to `PermissionSeeder` and assign to appropriate roles. |
| **Implementation Effort** | Low |

---

## Gap 11: Dashboard Builder — Unused Student Data

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-011 |
| **Priority** | **Medium** |
| **Module** | Teacher Dashboard |
| **Current Behavior** | `TeacherDashboardBuilder::buildStatCards()` counts Homework where `due_date >= now()` scoped by `created_by` only if the teacher record exists. A teacher with no `Teacher` model record sees ALL unexpired homework. |
| **Expected Behavior** | Homework count should be scoped to the subjects/classes assigned to the authenticated user, regardless of whether a Teacher model record exists. |
| **Business Impact** | Potential data leak — teacher without Teacher model record sees all school's homework counts. |
| **Recommendation** | Scope homework query by classes/subjects assigned to the user, not just `created_by`. |
| **Implementation Effort** | Low |

---

## Gap 12: Hardcoded Role Checks in API Controllers

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-012 |
| **Priority** | **High** |
| **Module** | API (Multiple) |
| **Current Behavior** | 11+ hardcoded `hasRole('School Admin')` and `hasRole('Accountant')` checks in API controllers. These bypass the permission system. |
| **Expected Behavior** | API authorization should be permission-based, not role-name-based. |
| **Business Impact** | If roles are renamed or new roles with same permissions are created, these checks block valid access. Brittle authorization that prevents flexible RBAC. |
| **Recommendation** | Replace `hasRole()` checks with `can()` or `hasPermission()` checks. |
| **Implementation Effort** | Medium (11+ controller files) |

---

## Gap 13: Lowercase Role Names in AI ContextBuilder

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-013 |
| **Priority** | **High** |
| **Module** | AI Assistant |
| **Current Behavior** | `ContextBuilder.php:133-139` checks `hasRole('admin')`, `hasRole('teacher')`, etc. (lowercase). Seeded roles use Title Case (`'Teacher'`, `'Parent'`, `'Student'`, `'Super Admin'`). |
| **Expected Behavior** | Role name comparisons must be case-insensitive or match the seeded names exactly. |
| **Business Impact** | AI context detection is broken for all roles. The AI cannot determine the user's role, so role-appropriate query scoping fails. This means all AI queries may return incorrect/over-permissive results. |
| **Recommendation** | Use `hasRole()` with correct Title Case names, or use case-insensitive comparison. |
| **Implementation Effort** | Low |

---

## Gap 14: Principal — Missing Core Permissions

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-014 |
| **Priority** | **High** |
| **Module** | Permissions |
| **Current Behavior** | Principal is not assigned: leave_management.approve, leave_management.view, teachers.reports, timetable.reports, library.view (for oversight). |
| **Expected Behavior** | As the academic head, the Principal must be able to: approve leave, view leave management, view teacher reports, view timetable reports, and have read-only oversight of library. |
| **Business Impact** | Principal cannot perform core approval duties. Cannot approve leave, cannot view teacher reports, cannot oversee library operations. |
| **Recommendation** | Add missing permissions to Principal role in seeder. |
| **Implementation Effort** | Low |

---

## Gap 15: Accountant — Missing Transport Fee Assignment

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-015 |
| **Priority** | **Medium** |
| **Module** | Fees / Transport |
| **Current Behavior** | Accountant has `transport.view` but no `transport.update` or `transport.create`. Transport fee assignment requires updating student transport records. |
| **Expected Behavior** | Accountant should be able to assign transport routes to students (for fee calculation purposes) without full transport management access. |
| **Business Impact** | Accountant cannot complete transport fee assignment workflow — must rely on School Admin for routine task. |
| **Recommendation** | Add granular transport permissions or create a dedicated `transport.assign` permission for Accountant. |
| **Implementation Effort** | Low |

---

## Gap 16: Teacher — Missing Self-Service Permissions

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-016 |
| **Priority** | **High** |
| **Module** | Permissions |
| **Current Behavior** | Teacher has no `leave_management` permissions, no `teachers` permissions (for own profile), and no `fees.view` (for student fee status lookups). |
| **Expected Behavior** | Teacher should be able to: apply for leave, view own leave balance, view own profile, and view student fee status (read-only). |
| **Business Impact** | Teachers cannot apply for leave through the system. Cannot view own profile. Cannot check student fee status during parent meetings. |
| **Recommendation** | Add `leave_management.create`, `leave_management.view` (self), `teachers.view` (self), `fees.view` (students in own classes) to Teacher role. |
| **Implementation Effort** | Low |

---

## Gap 17: Parent — Incomplete Leave Management

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-017 |
| **Priority** | **Medium** |
| **Module** | Permissions |
| **Current Behavior** | Parent has `leave_management.create` but no `leave_management.view` (to check status) and no `leave_management.update` (to cancel before approval). |
| **Expected Behavior** | Parent should be able to: apply leave for child, view leave status, and cancel pending leave requests. |
| **Business Impact** | Parents can submit leave requests but cannot see whether they were approved. Cannot cancel a mistaken request. |
| **Recommendation** | Add `leave_management.view` (own) and `leave_management.delete` (own, pending only) to Parent role. |
| **Implementation Effort** | Low |

---

## Gap 18: Dead Permissions

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-018 |
| **Priority** | **Low** |
| **Module** | Permissions |
| **Current Behavior** | `students.export`, `reports.export`, `payroll.payslip.view`, and `payroll.payslip.export` are defined in seeder but never checked anywhere in the codebase. |
| **Expected Behavior** | Permissions should either be used (checked in routes/controllers/views) or removed. |
| **Business Impact** | Dead permissions create confusion during role configuration. Admins may think features are protected when they aren't. |
| **Recommendation** | Either add usage for these permissions or remove from seeder. |
| **Implementation Effort** | Low |

---

## Gap 19: Users Module — No Policy

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-019 |
| **Priority** | **Medium** |
| **Module** | Users |
| **Current Behavior** | Users module relies entirely on route middleware (`permission:users.view`) and FormRequest `authorize()` methods. No Laravel policy exists. No `$this->authorize()` calls in controller. |
| **Expected Behavior** | Users module should have a policy defining authorization rules for view, create, update, delete, toggle-status, reset-password. |
| **Business Impact** | Thin authorization layer. While still protected by middleware, the lack of policies makes it harder to enforce granular rules (e.g., only School Admin can toggle status). |
| **Recommendation** | Create `UserPolicy` with appropriate gates. |
| **Implementation Effort** | Medium |

---

## Gap 20: Settings Module — No Policy

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-020 |
| **Priority** | **Low** |
| **Module** | Settings |
| **Current Behavior** | Settings module has no policy. Route middleware is the only protection. |
| **Expected Behavior** | Settings should have a policy ensuring only School Admin can update settings, with audit logging. |
| **Business Impact** | Minor — route middleware already restricts access. But no audit trail for setting changes. |
| **Recommendation** | Create `SettingsPolicy` with update gate and audit logging. |
| **Implementation Effort** | Low |

---

## Gap 21: Notification Module — Missing FormRequest Authorization

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-021 |
| **Priority** | **Low** |
| **Module** | Notifications |
| **Current Behavior** | `StoreNotificationRequest` and `UpdateNotificationRequest` have no `authorize()` method — default to `true`. Any authenticated user can submit create/update requests. |
| **Expected Behavior** | FormRequests should check `notifications.create` or `notifications.update` permission. |
| **Business Impact** | While controller's `$this->authorize('send')` provides a gate, the request validation layer doesn't filter by permission — anyone can attempt to submit the form. |
| **Recommendation** | Add authorize() methods to Notification FormRequests. |
| **Implementation Effort** | Low |

---

## Gap 22: Sidebar — AI Workspace No Permission Gates

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-022 |
| **Priority** | **Critical** |
| **Module** | Sidebar / AI |
| **Current Behavior** | AI Workspace section in `sidebar.blade.php` has NO `@can` directives. Ask ERP, Executive Copilot, AI Agents, and Execution History are visible to ALL authenticated users. |
| **Expected Behavior** | AI sidebar items should be gated by role/permission. Only authorized roles should see each item. |
| **Business Impact** | All users see links to AI features they shouldn't access. Leads to 403 errors or unauthorized access attempts. |
| **Recommendation** | Add `@can` directives to all AI sidebar items matching the AI_ROLE_MATRIX.md. |
| **Implementation Effort** | Low |

---

## Gap 23: Report Module Permission — Route Registration

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-023 |
| **Priority** | **Medium** |
| **Module** | Reports |
| **Current Behavior** | Reports routes are loaded from `routes/modules/reports.php` which is `require`'d outside the `admin.*` group in `web.php`. However, the individual report route files (inside `app/Modules/Reports/routes.php`) are inside the admin group. |
| **Expected Behavior** | All report routes should consistently be inside the admin group with `permission:reports.view` middleware. |
| **Business Impact** | Potential inconsistency in authorization. Some report routes may have different middleware than expected. |
| **Recommendation** | Unify report route registration — keep all under the admin group with consistent middleware. |
| **Implementation Effort** | Low |

---

## Gap 24: Parent Portal — Dual Access Paths

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-024 |
| **Priority** | **Medium** |
| **Module** | Parents |
| **Current Behavior** | Parent role has access to BOTH `parent-portal.*` routes (via `role:Parent` middleware) AND `admin.*` routes (via `auth + school` middleware). Parents can access both `/parent-portal/` and `/admin/` URLs. |
| **Expected Behavior** | Parents should only access parent-portal routes. Admin routes should be restricted to staff roles. |
| **Business Impact** | Parents can navigate to admin URLs and see admin UI layout, which is confusing and inconsistent. May accidentally access inappropriate features. |
| **Recommendation** | Add a middleware or gate that redirects Parent role users from admin routes to parent-portal routes. Or use role-based layout switching. |
| **Implementation Effort** | Medium |

---

## Gap 25: Student Promotion — No Workflow

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-025 |
| **Priority** | **High** |
| **Module** | Students |
| **Current Behavior** | No promotion workflow exists. Students are created, updated, or deleted individually. There is no batch promotion mechanism. |
| **Expected Behavior** | A complete promotion workflow with batch operations, approval, and new session creation as described in BUSINESS_WORKFLOWS.md. |
| **Business Impact** | At end of academic year, schools cannot promote students to next class in bulk. Each student must be manually re-enrolled. |
| **Recommendation** | Implement promotion workflow with batch selection, approval flow, and StudentSession creation. |
| **Implementation Effort** | High |

---

## Gap 26: Teacher Attendance — Separate from Student Attendance

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-026 |
| **Priority** | **Medium** |
| **Module** | Teachers / Attendance |
| **Current Behavior** | Teacher attendance is managed inside the Teachers module (separate controller, model, requests) rather than a unified attendance system. |
| **Expected Behavior** | Teacher attendance should be part of the Attendance module with its own category/type, sharing the same architecture and reporting. |
| **Business Impact** | Two separate attendance systems create maintenance overhead, inconsistent reporting, and duplication of business logic. |
| **Recommendation** | Refactor attendance to support multiple attendance types (Student, Teacher, Staff) within a unified Attendance module. |
| **Implementation Effort** | High |

---

## Gap 27: Exam Marks — Bulk Upload/Entry Improvements

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-027 |
| **Priority** | **Medium** |
| **Module** | Exams |
| **Current Behavior** | Marks entry is per-student via form. No bulk CSV/Excel upload for marks entry. |
| **Expected Behavior** | Teachers should be able to enter marks via: (1) grid entry (all students at once), (2) CSV/Excel import, (3) individual entry. |
| **Business Impact** | Teachers with large classes spend excessive time entering marks one by one. Prone to data entry errors. |
| **Recommendation** | Implement bulk marks upload via CSV/Excel. Add grid-style entry where teacher can tab through students. |
| **Implementation Effort** | Medium |

---

## Gap 28: Fee Payment — Online Payment Integration

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-028 |
| **Priority** | **High** |
| **Module** | Fees |
| **Current Behavior** | No online payment gateway integration. All payments are recorded manually by Accountant. |
| **Expected Behavior** | Integration with at least one payment gateway (Razorpay, Paytm, etc.) for online fee collection. Parents can pay directly from portal. |
| **Business Impact** | Parents cannot pay fees online. Must visit school in person or do bank transfer. Manual reconciliation is time-consuming for Accountant. |
| **Recommendation** | Integrate payment gateway. Implement webhook for auto-reconciliation. Provide payment link in fee reminders. |
| **Implementation Effort** | High |

---

## Gap 29: Transport — Live Tracking

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-029 |
| **Priority** | **Medium** |
| **Module** | Transport |
| **Current Behavior** | Transport module has route, vehicle, driver management and assignments. No live tracking or real-time location features for the web app. Driver mobile endpoints exist (Phase 5.6) but no parent-facing live tracking UI. |
| **Expected Behavior** | Parents should be able to view bus location in real-time during commute hours on the parent portal. |
| **Business Impact** | Parents cannot track their child's bus. No real-time delay notifications. |
| **Recommendation** | Implement live tracking UI on parent portal using existing driver API endpoints. |
| **Implementation Effort** | Medium |

---

## Gap 30: Library — Member Management

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-030 |
| **Priority** | **Medium** |
| **Module** | Library |
| **Current Behavior** | Library member management appears to rely on searching users/students. No dedicated library membership system with membership ID, validity dates, or membership status. |
| **Expected Behavior** | Library should have a membership system: register members, set validity dates, manage membership status (active/suspended/expired), track borrowing limits per member type. |
| **Business Impact** | No control over borrowing eligibility. Expired members or students who left school could still borrow books. |
| **Recommendation** | Implement library membership module with auto-sync from StudentSession and Teacher records. |
| **Implementation Effort** | Medium |

---

## Gap 31: Reports — No Report Builder

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-031 |
| **Priority** | **Low** |
| **Module** | Reports |
| **Current Behavior** | Reports are pre-defined per module. No custom report builder where School Admin can select fields, filters, and layout. |
| **Expected Behavior** | School Admin should be able to create custom reports by selecting modules, fields, filters, and export format. |
| **Business Impact** | Limited flexibility for ad-hoc reporting needs. May require developer intervention for unique report requests. |
| **Recommendation** | Implement drag-and-drop report builder with field selection, filters, sorting, and export to PDF/Excel. |
| **Implementation Effort** | High |

---

## Gap 32: No Student/Parent Self-Registration

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-032 |
| **Priority** | **Medium** |
| **Module** | Auth |
| **Current Behavior** | No self-registration flow. All accounts must be created by School Admin. |
| **Expected Behavior** | Students and Parents should be able to self-register using admission number/roll number and date of birth, or via an invitation link sent during admission. |
| **Business Impact** | School Admin must manually create accounts for every parent and student. Time-consuming for large schools. |
| **Recommendation** | Implement self-registration with verification (admission number + DOB). Add invitation-based registration during admission workflow. |
| **Implementation Effort** | Medium |

---

## Gap 33: No Bulk Operations in Multiple Modules

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-033 |
| **Priority** | **Medium** |
| **Module** | Multiple |
| **Current Behavior** | Bulk operations are missing in several modules where they would be beneficial: students (bulk import), attendance (bulk mark exists but limited), homework (bulk assign to multiple classes). |
| **Expected Behavior** | Where operationally appropriate, bulk operations should be available: import students from CSV, assign homework to multiple classes/sections simultaneously, bulk fee assignment. |
| **Business Impact** | Administrators spend excessive time on repetitive tasks. Data entry errors increase with manual repetition. |
| **Recommendation** | Implement bulk operations in priority order: student import, homework multi-class assignment, bulk fee structure assignment. |
| **Implementation Effort** | Medium |

---

## Gap 34: No Academic Year Management

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-034 |
| **Priority** | **High** |
| **Module** | Academics |
| **Current Behavior** | Academic years can be created and managed, but there is no clear mechanism to close an academic year and transition all data to the next year. Student promotion, fee structure migration, and archival are manual. |
| **Expected Behavior** | A complete Academic Year lifecycle: Create → Activate → Run → Close → Archive. Year-end process should handle promotion, fee transition, attendance archival, and report generation. |
| **Business Impact** | Year-end transition is entirely manual. Risk of data loss or misalignment between academic years. |
| **Recommendation** | Implement Academic Year lifecycle with year-end processing workflow. |
| **Implementation Effort** | High |

---

## Gap 35: Notification Module — Targeted Sending

| Field | Value |
|-------|-------|
| **Gap ID** | GAP-035 |
| **Priority** | **Medium** |
| **Module** | Notifications |
| **Current Behavior** | Notification sending UI exists but targeting options (send to specific class, specific section, specific parents, defaulters) may be limited. |
| **Expected Behavior** | Rich targeting options: by class, section, individual student, individual parent, by fee status, by attendance status, by custom filter. |
| **Business Impact** | Teachers/Admin cannot send targeted communications efficiently. Must send to all or manually select recipients. |
| **Recommendation** | Enhance notification targeting with pre-defined filters and custom recipient selection. |
| **Implementation Effort** | Medium |

---

## Gap Summary

| Priority | Count | Gap IDs |
|----------|-------|---------|
| Critical | 9 | 1, 2, 3, 4, 5, 6, 7, 8, 9, 22 |
| High | 8 | 10, 12, 13, 14, 16, 25, 28, 34 |
| Medium | 12 | 11, 15, 17, 19, 23, 24, 26, 27, 29, 30, 32, 33, 35 |
| Low | 4 | 18, 20, 21, 31 |

**Total Gaps Identified: 35**
