# UAT TEST PLAN

**Date:** 2026-07-08
**Status:** ⚠️ PLANNED — Execution blocked by Critical/High issues

---

## Test Scope

| Role | Test Scenarios | Priority | Status |
|------|---------------|----------|--------|
| Super Admin | Full access, all modules, bypass | P0 | ⏳ Blocked |
| School Admin | School-wide operations, settings, users | P0 | ⏳ Blocked |
| Principal | Oversight, leave approval, reports | P0 | ⏳ Blocked |
| Teacher | Attendance marking, homework, exam marks, leave | P0 | ⏳ Blocked |
| HR | Employee management, document verification | P0 | ⏳ Blocked |
| Accountant | Fee collection, reports, transport fees | P0 | ⏳ Blocked |
| Payroll Manager | Payroll processing, payslip generation | P0 | ⏳ Blocked |
| Librarian | Book issue/return, fine management | P0 | ⏳ Blocked |
| Receptionist | Student/parent lookup | P0 | ⏳ Blocked |
| Staff | Dashboard, timetable, attendance | P0 | ⏳ Blocked |
| Parent | Portal access, child data views | P0 | ⏳ Blocked |
| Student | Dashboard, attendance, timetable, exams | P0 | ⏳ Blocked |

---

## Prerequisites for UAT

Before UAT can begin:

1. ✅ All Critical bugs fixed (R01-R04)
2. ✅ All High bugs fixed (R05-R10)
3. ✅ Missing permission strings seeded
4. ✅ AI Agent routes authorized
5. ✅ AI sidebar items gated
6. ✅ Test data seeded for all modules
7. ✅ Deployment to UAT environment
8. ✅ Monitoring configured

---

## Test Categories

### 1. Login & Authentication
- Login with each role ✓
- Role-based redirect ✓
- Password reset flow ✓
- Session timeout handling ✓
- Multi-school isolation ✓

### 2. Dashboard
- Role-specific dashboard loads ✓
- Stat cards display correct data ✓
- Widgets functional ✓
- Quick actions work ✓
- Loading states shown ✓

### 3. Navigation
- Sidebar items for role ✓
- All routes resolve ✓
- No broken links ✓
- Mobile responsive ✓
- Breadcrumbs correct ✓

### 4. CRUD Operations
- List view with DataTable ✓
- Create form validation ✓
- Edit with pre-filled data ✓
- Delete with confirmation ✓
- Soft delete and restore ✓

### 5. Workflow Testing
- Student admission flow ✓
- Attendance marking ✓
- Leave application → approval ✓
- Exam marks entry → publication ✓
- Fee collection → receipt ✓
- Payroll processing ✓
- Book issue → return ✓
- Document upload → verification ✓

### 6. Reports
- All report types load with data ✓
- Filters work correctly ✓
- Export to PDF/Excel ✓
- Print functionality ✓
- Role-appropriate data visibility ✓

### 7. AI Features
- Ask ERP responds correctly ✓
- Role-appropriate scoping ✓
- Executive Copilot loads ✓
- AI Agents execute properly ✓
- Execution history tracks ✓

### 8. Security
- No unauthorized data access ✓
- Permission middleware works ✓
- CSRF protection active ✓
- API authentication works ✓
- School isolation maintained ✓

---

## Entry Criteria

- [ ] All Critical severity bugs fixed
- [ ] All High severity bugs fixed
- [ ] Test environment provisioned
- [ ] Test data seeded
- [ ] Test users created for all 12 roles
- [ ] UAT testers identified per role
- [ ] Test scripts prepared

## Exit Criteria

- [ ] 100% of P0 test cases pass
- [ ] 95%+ of P1 test cases pass
- [ ] Zero Critical or High open bugs
- [ ] All testers sign off
- [ ] UAT execution report generated

## Test Environment

- **URL:** https://uat.school-erp.com
- **Database:** MySQL 8.0, seeded with 200+ students, 30+ teachers, transactional data
- **Server:** 4 vCPU, 8GB RAM (minimum)
- **Browsers:** Chrome 120+, Firefox 120+, Edge 120+
- **Mobile:** iOS Safari, Android Chrome
