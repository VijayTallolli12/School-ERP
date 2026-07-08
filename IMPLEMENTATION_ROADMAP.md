# IMPLEMENTATION ROADMAP

**Document:** IMPLEMENTATION_ROADMAP.md
**Date:** 2026-07-07

---

## Phase Overview

| Phase | Name | Duration | Gaps Addressed | Dependencies |
|-------|------|----------|----------------|-------------|
| 1 | **Foundation & Fixes** | 1 week | 1, 2, 3, 10, 11, 12, 13, 14, 16, 17, 22 | None |
| 2 | **Dashboard Builders** | 2 weeks | 4, 5, 6, 7, 8, 9 | Phase 1 (foundation) |
| 3 | **Business Workflows** | 4 weeks | 25, 26, 27, 34 | Phase 1 |
| 4 | **Finance & Payments** | 3 weeks | 15, 28 | Phases 1, 3 |
| 5 | **Communication & Library** | 2 weeks | 30, 35 | Phase 1 |
| 6 | **Transport & Reports** | 3 weeks | 29, 31, 33 | Phases 1, 3 |
| 7 | **AI & Security** | 2 weeks | 19, 20, 21, 23, 24 | Phase 1 |
| 8 | **Cleanup & Polish** | 1 week | 18 | All phases |

---

## Phase 1: Foundation & Fixes

**Duration:** 1 week
**Priority:** Critical
**Gaps:** 1, 2, 3, 10, 11, 12, 13, 14, 16, 17, 22

### Tasks

#### Day 1-2: Authorization Fixes
| Task | Effort | Files Affected | Description |
|------|--------|----------------|-------------|
| Add missing permission definitions | Low | PermissionSeeder.php | Add `teachers.attendance.*`, `teachers.leave.*` permission strings |
| Fix lowercase role names in ContextBuilder | Low | ContextBuilder.php | Change `hasRole('admin')` → `hasRole('School Admin')`, etc. |
| Fix Principal missing permissions | Low | PermissionSeeder.php | Add `leave_management.approve`, `leave_management.view`, `teachers.reports` to Principal |
| Fix Teacher missing permissions | Low | PermissionSeeder.php | Add `leave_management.create`, `leave_management.view` (self), `teachers.view` (self), `fees.view` to Teacher |

#### Day 2-3: Authorization Fixes (cont.)
| Task | Effort | Files Affected | Description |
|------|--------|----------------|-------------|
| Fix Parent leave permissions | Low | PermissionSeeder.php | Add `leave_management.view` (own), `leave_management.delete` (own pending) |
| Add AI Agent route middleware | Low | routes/modules/ai_agents.php | Add `permission:ai_agents.view` or role middleware |
| Add AI Assistant route middleware | Low | routes/modules/ai_assistant.php | Move inside admin group or add permission middleware |
| Add sidebar AI permission gates | Low | resources/views/layouts/partials/sidebar.blade.php | Add `@can` directives to AI Workspace section |

#### Day 3-4: Code Quality Fixes
| Task | Effort | Files Affected | Description |
|------|--------|----------------|-------------|
| Replace hardcoded hasRole() in API controllers | Medium | 11+ API controllers | Replace with `can()` or `hasPermission()` |
| Fix TeacherDashboard homework scope | Low | TeacherDashboardBuilder.php | Scope by assigned classes/subjects |
| Add leave_management.view permission check in sidebar | Low | sidebar.blade.php | Add `@can('leave_management.view')` |

#### Day 4-5: Introduction of New Permissions
| Task | Effort | Files Affected | Description |
|------|--------|----------------|-------------|
| Create `ai_agents.view` permission | Low | PermissionSeeder.php | New permission for AI Agents access |
| Create `ai_assistant.view` permission | Low | PermissionSeeder.php | New permission for AI Assistant access |
| Assign new permissions to roles | Low | PermissionSeeder.php | As per AI_ROLE_MATRIX.md |

#### Day 5: Testing & Verification
| Task | Effort | Description |
|------|--------|-------------|
| Verify all roles can login | Low | Test login for all 12 roles |
| Verify AI route access control | Low | Test AI routes with different roles |
| Verify permission checks | Low | Spot-check permission gates |

### Complexity: Low
### Dependencies: None

---

## Phase 2: Dashboard Builders

**Duration:** 2 weeks
**Priority:** Critical
**Gaps:** 4, 5, 6, 7, 8, 9

### Tasks

#### Week 1: Student & Parent Dashboards
| Task | Effort | Description |
|------|--------|-------------|
| Create `StudentDashboardBuilder` | Medium | Personal timetable, attendance, homework, exams, library status |
| Create student stat cards | Medium | Today's attendance, pending homework, upcoming exams, library books |
| Create student widgets | Medium | Today's schedule, pending homework list, upcoming exams, recent results |
| Create student charts | Medium | Attendance trend line, subject performance bar |
| Create student quick actions | Low | View timetable, check homework, view attendance, check results |
| Enhance `ParentDashboardBuilder` | Medium | Currently empty — needs full implementation per ROLE_DASHBOARD_DESIGN.md |

#### Week 2: Professional Role Dashboards
| Task | Effort | Description |
|------|--------|-------------|
| Create `AccountantDashboardBuilder` | Medium | Fee collection KPIs, defaulter list, daily collection trends |
| Create `PayrollManagerDashboardBuilder` | Medium | Payroll status, salary trends, processing timeline |
| Create `LibrarianDashboardBuilder` | Medium | Book issue KPIs, overdue alerts, catalog statistics |
| Create `ReceptionistDashboardBuilder` | Medium | Visitor metrics, new inquiries, quick student lookup |
| Create `HRDashboardBuilder` | Medium | Teacher attendance, leave requests, contract alerts |
| Create `StaffDashboardBuilder` improvement | Low | Enhance with better stat cards and widgets |
| Update `DashboardFactory` | Low | Add mappings for all new builders |
| Create data collectors | Medium | Fee, Payroll, Library, HR data collectors |

### Complexity: Medium-High
### Dependencies: Phase 1 (permission fixes ensure dashboard data access)

---

## Phase 3: Business Workflows

**Duration:** 4 weeks
**Priority:** High
**Gaps:** 25, 26, 27, 34

### Tasks

#### Week 1: Academic Year Lifecycle
| Task | Effort | Description |
|------|--------|-------------|
| Create Academic Year service | High | Year creation, activation, closure, archival |
| Implement year-end processing | High | Archive attendance, close fee books, generate year-end reports |
| Create year transition UI | Medium | Admin panel for year-end operations |
| Add academic year context to queries | Medium | All queries scoped to active year |
| Implement year rollover for fee structures | Medium | Copy/update fee structures for new year |

#### Week 2: Student Promotion Workflow
| Task | Effort | Description |
|------|--------|-------------|
| Create promotion batch selection | Medium | Select students, target classes, options |
| Implement promotion rules engine | Medium | Min attendance, min marks criteria |
| Create promotion preview | Medium | Show impact before execution |
| Implement promotion execution | Medium | Create new StudentSession, update roll numbers |
| Create promotion reports | Low | Promotion summary, retained students list |

#### Week 3: Attendance Unification
| Task | Effort | Description |
|------|--------|-------------|
| Refactor attendance module | High | Support multiple attendance types (Student, Teacher, Staff) |
| Migrate teacher attendance | Medium | Move TeacherAttendance logic into Attendance module |
| Create unified attendance reporting | Medium | Combined student+teacher attendance reports |
| Add attendance policies | Medium | Configurable attendance rules per type |

#### Week 4: Exam Marks Bulk Entry
| Task | Effort | Description |
|------|--------|-------------|
| Implement marks grid entry | Medium | Tab-based entry for all students at once |
| Implement CSV/Excel import | Medium | Bulk upload with validation |
| Implement marks verification workflow | Medium | Teacher enters → Review → Principal approves |
| Add exam result batch operations | Medium | Bulk publish, bulk print report cards |

### Complexity: High
### Dependencies: Phase 1

---

## Phase 4: Finance & Payments

**Duration:** 3 weeks
**Priority:** High
**Gaps:** 15, 28

### Tasks

#### Week 1: Online Payment Gateway
| Task | Effort | Description |
|------|--------|-------------|
| Research and select payment gateway | Low | Razorpay, Paytm, Cashfree, etc. |
| Integrate payment gateway SDK | High | Webhook, callback, order creation |
| Implement payment UI for parents | Medium | Pay fee online from parent portal |
| Implement payment confirmation flow | Medium | Webhook → Update fee ledger → Send receipt |
| Add payment method tracking | Low | Track payment method per transaction |

#### Week 2: Payment Features
| Task | Effort | Description |
|------|--------|-------------|
| Implement partial payment | Medium | Pay selected fee items only |
| Implement installment payment | Medium | Configure installment plans |
| Add auto-reconciliation | Medium | Match bank statement with system records |
| Implement payment retry/failure handling | Medium | Handle failed transactions gracefully |
| Add payment analytics | Low | Success rate, preferred payment methods |

#### Week 3: Accountant Transport Fee
| Task | Effort | Description |
|------|--------|-------------|
| Create `transport.assign` permission | Low | New permission for transport fee assignment |
| Add transport fee UI for Accountant | Medium | Assign routes to students with fee calculation |
| Update fee reports | Low | Include transport fees in reports |
| Test Accountant workflow | Low | End-to-end test of transport fee assignment |

### Complexity: High
### Dependencies: Phase 1, Phase 3 (academic year for fee structures)

---

## Phase 5: Communication & Library

**Duration:** 2 weeks
**Priority:** Medium
**Gaps:** 30, 35

### Tasks

#### Week 1: Notification Enhancement
| Task | Effort | Description |
|------|--------|-------------|
| Implement rich recipient targeting | Medium | By class, section, fee status, attendance, custom filters |
| Add notification templates | Medium | Pre-defined templates for common notifications |
| Implement notification scheduling | Medium | Schedule notifications for future delivery |
| Add delivery tracking | Medium | Read receipts, delivery status per channel |
| Enhance notification channels | Medium | SMS gateway integration, email queue |

#### Week 2: Library Membership
| Task | Effort | Description |
|------|--------|-------------|
| Create library membership system | Medium | Membership records, validity, status |
| Auto-sync membership with StudentSession | Medium | Active students = active members |
| Implement borrowing limits | Medium | Per member type (student: 3 books, teacher: 5 books) |
| Add membership expiry handling | Medium | Suspend expired members, auto-renew for active students |
| Enhance library reports | Low | Member-wise reports, borrowing trends |

### Complexity: Medium
### Dependencies: Phase 1

---

## Phase 6: Transport & Reports

**Duration:** 3 weeks
**Priority:** Medium
**Gaps:** 29, 31, 33

### Tasks

#### Week 1: Transport Live Tracking
| Task | Effort | Description |
|------|--------|-------------|
| Implement live tracking UI | Medium | Map view on parent portal |
| Integrate with driver location API | Medium | Consume existing driver API for real-time location |
| Add route delay notifications | Medium | Auto-notify parents when bus is delayed |
| Implement geofencing alerts | Low | Alert when bus enters/leaves school zone |
| Add transport history | Low | Trip history, route logs |

#### Week 2: Custom Report Builder
| Task | Effort | Description |
|------|--------|-------------|
| Design report builder architecture | High | Module, field, filter, layout system |
| Implement field selection UI | High | Drag-and-drop report builder |
| Implement filter system | Medium | Date range, class, section, status filters |
| Add export to PDF/Excel | Medium | Export reports in multiple formats |
| Create report templates | Low | Save and reuse custom report configurations |

#### Week 3: Bulk Operations
| Task | Effort | Description |
|------|--------|-------------|
| Implement bulk student import | Medium | CSV import with validation |
| Implement homework multi-class assignment | Medium | Assign same homework to multiple classes/sections |
| Implement bulk fee structure assignment | Medium | Assign fee structure to entire class |
| Implement bulk attendance correction | Medium | Correct attendance for multiple students at once |

### Complexity: High
### Dependencies: Phase 1, Phase 3

---

## Phase 7: AI & Security

**Duration:** 2 weeks
**Priority:** Medium
**Gaps:** 19, 20, 21, 23, 24

### Tasks

#### Week 1: Policies & Authorization
| Task | Effort | Description |
|------|--------|-------------|
| Create UserPolicy | Medium | View, create, update, delete, toggle-status, reset-password gates |
| Create SettingsPolicy | Low | View, update gates with audit logging |
| Add FormRequest authorization to Notifications | Low | Add authorize() methods to Store/Update requests |
| Audit and fix all authorization gaps | Medium | Comprehensive audit of all FormRequests |
| Standardize report route registration | Low | Move all report routes under admin group |

#### Week 2: Role-Based Redirection
| Task | Effort | Description |
|------|--------|-------------|
| Implement admin-to-portal redirect for Parents | Medium | Middleware to redirect Parent from admin to parent-portal |
| Implement role-based layout switching | Medium | Admin layout for staff roles, portal layout for parents/students |
| Add role-specific login redirect | Medium | After login, redirect to role-appropriate dashboard |
| Add permission audit logs | Medium | Log all permission/role changes |

### Complexity: Medium
### Dependencies: Phase 1

---

## Phase 8: Cleanup & Polish

**Duration:** 1 week
**Priority:** Low
**Gaps:** 18

### Tasks

| Task | Effort | Description |
|------|--------|-------------|
| Audit dead permissions | Low | Identify unused permission strings |
| Remove dead permissions OR add usage | Low | For each dead permission, remove or implement |
| Code cleanup | Low | Remove unused imports, dead code |
| Test all role-permission combinations | Medium | Systematic test of each role's access |
| Documentation update | Low | Update any outdated documentation |
| Performance audit | Medium | N+1 queries, caching, response times |

### Complexity: Low
### Dependencies: All phases (cleanup should be last)

---

## Dependency Graph

```
Phase 1: Foundation & Fixes (1 week)
   │
   ├──► Phase 2: Dashboard Builders (2 weeks)
   │
   ├──► Phase 3: Business Workflows (4 weeks)
   │      │
   │      ├──► Phase 4: Finance & Payments (3 weeks)
   │      │
   │      └──► Phase 6: Transport & Reports (3 weeks)
   │
   ├──► Phase 5: Communication & Library (2 weeks)
   │
   ├──► Phase 7: AI & Security (2 weeks)
   │
   └──► Phase 8: Cleanup & Polish (1 week)
```

## Estimated Total Duration

| Phase | Duration | Parallel With |
|-------|----------|---------------|
| Phase 1 | 1 week | — |
| Phase 2 | 2 weeks | — |
| Phase 3 | 4 weeks | Phase 2, Phase 5 |
| Phase 4 | 3 weeks | Phase 5, Phase 6 |
| Phase 5 | 2 weeks | Phase 3, Phase 4 |
| Phase 6 | 3 weeks | Phase 4, Phase 5 |
| Phase 7 | 2 weeks | Phase 6 |
| Phase 8 | 1 week | — |

**Total Sequential Duration:** ~8 weeks (if fully parallelized: ~6 weeks)
**Total Team Size Recommended:** 2-3 developers

---

## Resource Estimation

| Phase | Backend (days) | Frontend (days) | Testing (days) | Total (days) |
|-------|---------------|-----------------|----------------|--------------|
| Phase 1 | 4 | 1 | 1 | 6 |
| Phase 2 | 6 | 4 | 2 | 12 |
| Phase 3 | 12 | 6 | 4 | 22 |
| Phase 4 | 8 | 6 | 3 | 17 |
| Phase 5 | 5 | 3 | 2 | 10 |
| Phase 6 | 8 | 6 | 3 | 17 |
| Phase 7 | 4 | 2 | 2 | 8 |
| Phase 8 | 2 | 1 | 2 | 5 |
| **Total** | **49** | **29** | **19** | **97** |

---

## Risk Assessment

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| Payment gateway integration delays | Medium | High | Start gateway research in Phase 1; have backup gateway option |
| Data migration for attendance unification | Medium | High | Backup all data before migration; run in parallel during transition |
| Scope creep in report builder | High | Medium | Define MVP features; defer advanced features to v2 |
| Performance issues with unified attendance | Low | High | Index key columns; implement caching; use read replicas |
| Third-party API rate limits (SMS, Email) | Medium | Low | Implement queue system; batch sends; use multiple providers |
| LLM cost overruns for AI features | Medium | Medium | Implement usage quotas per role; cache common queries; use cheaper models for Ask ERP |
| Browser compatibility | Low | Medium | Target modern browsers (last 2 versions); test on Chrome, Firefox, Edge |
