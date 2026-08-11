# ROLE SIDEBAR DESIGN

**Document:** ROLE_SIDEBAR_DESIGN.md
**Date:** 2026-07-07

---

## Design Principles

1. **Workflow-based grouping** — Menu items are organized by business flow, not alphabetically
2. **Role-appropriate defaults** — Each role sees only what they need
3. **Frequent items first** — Most-used actions appear at top
4. **Context-aware badges** — Show counts (pending approvals, unread notifications)
5. **Collapsible sections** — Expand/collapse to manage screen real estate

---

## 1. Super Admin Sidebar

```
┌─────────────────────────────────────┐
│  ☰ School ERP                       │  ← Logo + School Name
├─────────────────────────────────────┤
│  ◆ Dashboard                        │  ← Active state indicator
│                                      │
│  ► SCHOOLS                           │  ← Section header
│    ○ All Schools                     │
│    ○ Add New School                  │
│                                      │
│  ► ADMINISTRATION                    │
│    ○ Users (System-wide)          [3]│  ← Badge: pending users
│    ○ Roles & Permissions             │
│    ○ Global Settings                 │
│    ○ Audit Logs                      │
│                                      │
│  ► MONITORING                        │
│    ○ System Health                   │
│    ○ Support Tickets              [5]│  ← Badge: open tickets
│    ○ Error Logs                      │
│    ○ Usage Analytics                 │
│                                      │
│  ► AI WORKSPACE                      │
│    ○ Ask ERP                         │
│    ○ Executive Copilot               │
│    ○ AI Agents                       │
│    ○ Execution History               │
│                                      │
│  ─────────────────────────────────── │
│  🔍 Quick Search                     │  ← Global search bar
│  ⚙ Settings                          │  ← Account settings
│  🚪 Logout                           │
└─────────────────────────────────────┘
```

**Hidden Modules:** Student-level modules (attendance, homework, exams, etc.) — Super Admin works at system level

---

## 2. School Admin Sidebar

```
┌─────────────────────────────────────┐
│  ☰ School ERP                       │  ← Logo + School Name
├─────────────────────────────────────┤
│  ◆ Dashboard                        │
│                                      │
│  ► ACADEMICS                         │
│    ○ Students                     [ ]│  ← Badge: new admissions pending
│    ○ Teachers                     [ ]│
│    ○ Parents                         │
│    ○ Classes & Sections              │
│    ○ Subjects                        │
│    ○ Academic Calendar               │
│                                      │
│  ► OPERATIONS                        │
│    ○ Attendance                      │
│    ○ Timetable                       │
│    ○ Exams                           │
│    ○ Homework                        │
│    ○ Student Documents               │
│                                      │
│  ► FINANCE                           │
│    ○ Fees                        [ ]│  ← Badge: pending collections
│    ○ Payroll                     [ ]│  ← Badge: payroll pending
│                                      │
│  ► COMMUNICATION                     │
│    ○ Notifications                   │
│    ○ Send Announcement               │
│                                      │
│  ► ADMINISTRATION                    │
│    ○ Users                        [ ]│  ← Badge: pending users
│    ○ Roles & Permissions             │
│    ○ Leave Management            [ ]│  ← Badge: pending approvals
│    ○ Settings                        │
│                                      │
│  ► SERVICES                          │
│    ○ Library                         │
│    ○ Transport                       │
│                                      │
│  ► REPORTS                           │
│    ○ Student Report                  │
│    ○ Attendance Report               │
│    ○ Fee Report                      │
│    ○ Exam Report                     │
│    ○ Teacher Report                  │
│    ○ Custom Reports                  │
│                                      │
│  ► AI WORKSPACE                      │
│    ○ Ask ERP                         │
│    ○ Executive Copilot               │
│    ○ AI Agents                       │
│    ○ Execution History               │
│                                      │
│  ─────────────────────────────────── │
│  🔍 Quick Search                     │
│  ⚙ Settings                          │
│  🚪 Logout                           │
└─────────────────────────────────────┘
```

---

## 3. Principal Sidebar

```
┌─────────────────────────────────────┐
│  ☰ School ERP                       │
├─────────────────────────────────────┤
│  ◆ Dashboard                        │
│                                      │
│  ► ACADEMICS                         │
│    ○ Students                     [ ]│
│    ○ Teachers                        │
│    ○ Classes & Sections              │
│    ○ Subjects                        │
│    ○ Academic Calendar               │
│                                      │
│  ► OPERATIONS                        │
│    ○ Attendance (Student)            │
│    ○ Attendance (Teacher)            │
│    ○ Timetable                       │
│    ○ Exams                           │
│    ○ Homework                    [ ]│  ← Badge: pending review
│    ○ Student Documents           [ ]│  ← Badge: pending verification
│                                      │
│  ► COMMUNICATION                     │
│    ○ Notifications                   │
│    ○ Send Announcement               │
│                                      │
│  ► LEAVE MANAGEMENT              [ ]│  ← Badge: pending approvals
│    ○ Leave Requests                  │
│    ○ Leave Types                     │
│                                      │
│  ► REPORTS                           │
│    ○ Student Performance             │
│    ○ Attendance Report               │
│    ○ Exam Report                     │
│    ○ Teacher Report                  │
│                                      │
│  ► AI WORKSPACE                      │
│    ○ Ask ERP                         │
│    ○ Executive Copilot               │
│                                      │
│  ─────────────────────────────────── │
│  🔍 Quick Search                     │
│  ⚙ Profile                           │
│  🚪 Logout                           │
└─────────────────────────────────────┘
```

**Hidden:** Fees (overview only via reports), Payroll (no access), Library, Transport, Users, Roles, Settings

---

## 4. Teacher Sidebar

```
┌─────────────────────────────────────┐
│  ☰ School ERP                       │
├─────────────────────────────────────┤
│  ◆ Dashboard                        │
│                                      │
│  ► MY CLASSROOM                      │
│    ○ My Classes                      │
│    ○ My Timetable                    │
│    ○ My Students                     │
│                                      │
│  ► ACADEMICS                         │
│    ○ Attendance                  [ ]│  ← Badge: pending marking
│    ○ Exams                           │
│    ○ Homework                    [ ]│  ← Badge: pending review
│    ○ Marks Entry                     │
│    ○ Student Documents               │
│                                      │
│  ► COMMUNICATION                     │
│    ○ Notifications                   │
│                                      │
│  ► SELF SERVICE                      │
│    ○ My Attendance                   │
│    ○ Apply Leave                 [ ]│  ← Badge: leave balance
│    ○ My Profile                      │
│                                      │
│  ► ACADEMIC CALENDAR                 │
│                                      │
│  ► AI WORKSPACE                      │
│    ○ Ask ERP                         │
│                                      │
│  ─────────────────────────────────── │
│  🔍 Quick Search                     │
│  ⚙ Profile                           │
│  🚪 Logout                           │
└─────────────────────────────────────┘
```

**Hidden:** Parents (full listing), Fees, Payroll, Library, Transport, Users, Roles, Settings, RBAC, Reports (limited)

---

## 5. HR Sidebar

```
┌─────────────────────────────────────┐
│  ☰ School ERP                       │
├─────────────────────────────────────┤
│  ◆ Dashboard                        │
│                                      │
│  ► TEACHER MANAGEMENT                │
│    ○ All Teachers                    │
│    ○ Teacher Attendance          [ ]│  ← Badge: pending marking
│    ○ Teacher Leaves              [ ]│  ← Badge: pending requests
│    ○ Contracts & Documents       [ ]│  ← Badge: expiring soon
│    ○ New Joiner Onboarding           │
│                                      │
│  ► COMMUNICATION                     │
│    ○ Notifications                   │
│    ○ Send Announcement               │
│                                      │
│  ► REPORTS                           │
│    ○ Teacher Attendance Report       │
│    ○ Leave Utilization Report        │
│    ○ Teacher Strength Report         │
│                                      │
│  ► AI WORKSPACE                      │
│    ○ Ask ERP                         │
│                                      │
│  ─────────────────────────────────── │
│  🔍 Quick Search                     │
│  ⚙ Profile                           │
│  🚪 Logout                           │
└─────────────────────────────────────┘
```

**Hidden:** Students, Fees, Payroll, Library, Transport, Academics, Exams, Homework, Timetable, Settings, Users, RBAC

---

## 6. Accountant Sidebar

```
┌─────────────────────────────────────┐
│  ☰ School ERP                       │
├─────────────────────────────────────┤
│  ◆ Dashboard                        │
│                                      │
│  ► FEE MANAGEMENT                    │
│    ○ Fee Structures                  │
│    ○ Collect Payment                 │
│    ○ Fee Dues / Defaulters           │
│    ○ Concessions & Waivers           │
│    ○ Receipts                        │
│                                      │
│  ► TRANSPORT                         │
│    ○ Transport Fees                  │
│                                      │
│  ► REPORTS                           │
│    ○ Daily Collection Report         │
│    ○ Fee Due Report                  │
│    ○ Class-wise Collection           │
│    ○ Defaulter Report                │
│    ○ Receipt Register                │
│                                      │
│  ► COMMUNICATION                     │
│    ○ Notifications                   │
│                                      │
│  ► AI WORKSPACE                      │
│    ○ Ask ERP                         │
│                                      │
│  ─────────────────────────────────── │
│  🔍 Quick Search                     │
│  ⚙ Profile                           │
│  🚪 Logout                           │
└─────────────────────────────────────┘
```

**Hidden:** Students (profiles), Teachers, Academics, Exams, Homework, Library, Payroll, Settings, Users, RBAC, Attendance

---

## 7. Payroll Manager Sidebar

```
┌─────────────────────────────────────┐
│  ☰ School ERP                       │
├─────────────────────────────────────┤
│  ◆ Dashboard                        │
│                                      │
│  ► PAYROLL MANAGEMENT                │
│    ○ Salary Structures               │
│    ○ Pay Grades / Designations       │
│    ○ Departments                     │
│    ○ Salary Components               │
│    ○ Process Payroll             [ ]│  ← Badge: pending processing
│    ○ Payslips                        │
│    ○ Ad-hoc Payments                 │
│                                      │
│  ► REPORTS                           │
│    ○ Monthly Payroll Summary         │
│    ○ Department-wise Salary          │
│    ○ Deduction Report                │
│    ○ YTD Earnings Report             │
│                                      │
│  ► COMMUNICATION                     │
│    ○ Notifications                   │
│                                      │
│  ► AI WORKSPACE                      │
│    ○ Ask ERP                         │
│                                      │
│  ─────────────────────────────────── │
│  🔍 Quick Search                     │
│  ⚙ Profile                           │
│  🚪 Logout                           │
└─────────────────────────────────────┘
```

**Hidden:** Students, Teachers (profiles), Fees, Academics, Exams, Library, Transport, Settings, Users, RBAC

---

## 8. Librarian Sidebar

```
┌─────────────────────────────────────┐
│  ☰ School ERP                       │
├─────────────────────────────────────┤
│  ◆ Dashboard                        │
│                                      │
│  ► LIBRARY MANAGEMENT                │
│    ○ Book Catalog                    │
│    ○ Issue Book                      │
│    ○ Return Book                     │
│    ○ Overdue Books               [ ]│  ← Badge: overdue count
│    ○ Fine Collection                 │
│    ○ Members                         │
│    ○ Categories / Authors / Publishers│
│    ○ Fine Settings                   │
│                                      │
│  ► REPORTS                           │
│    ○ Issued Books Report             │
│    ○ Overdue Report                  │
│    ○ Popular Books                   │
│    ○ Stock Report                    │
│                                      │
│  ► COMMUNICATION                     │
│    ○ Notifications                   │
│                                      │
│  ► AI WORKSPACE                      │
│    ○ Ask ERP                         │
│                                      │
│  ─────────────────────────────────── │
│  🔍 Quick Search                     │
│  ⚙ Profile                           │
│  🚪 Logout                           │
└─────────────────────────────────────┘
```

**Hidden:** Students (profiles), Teachers, Fees, Payroll, Academics, Exams, Homework, Transport, Settings, Users, RBAC

---

## 9. Receptionist Sidebar

```
┌─────────────────────────────────────┐
│  ☰ School ERP                       │
├─────────────────────────────────────┤
│  ◆ Dashboard                        │
│                                      │
│  ► FRONT DESK                        │
│    ○ Student Lookup                  │
│    ○ New Inquiry / Registration      │
│    ○ Parents                         │
│    ○ Visitor Log                     │
│                                      │
│  ► COMMUNICATION                     │
│    ○ Notifications                   │
│    ○ Contact Teacher                 │
│                                      │
│  ► AI WORKSPACE                      │
│    ○ Ask ERP                         │
│                                      │
│  ─────────────────────────────────── │
│  🔍 Quick Search                     │
│  ⚙ Profile                           │
│  🚪 Logout                           │
└─────────────────────────────────────┘
```

**Hidden:** All academic, financial, and administrative modules beyond student/parent view

---

## 10. Staff Sidebar

```
┌─────────────────────────────────────┐
│  ☰ School ERP                       │
├─────────────────────────────────────┤
│  ◆ Dashboard                        │
│                                      │
│  ► MY TASKS                          │
│    ○ Today's Schedule                │
│    ○ My Tasks                        │
│                                      │
│  ► SELF SERVICE                      │
│    ○ My Attendance                   │
│    ○ Apply Leave                     │
│    ○ My Profile                      │
│                                      │
│  ► COMMUNICATION                     │
│    ○ Notifications                   │
│                                      │
│  ► AI WORKSPACE                      │
│    ○ Ask ERP                         │
│                                      │
│  ─────────────────────────────────── │
│  🔍 Quick Search                     │
│  ⚙ Profile                           │
│  🚪 Logout                           │
└─────────────────────────────────────┘
```

**Hidden:** Everything beyond personal dashboard, attendance, leave, and notifications

---

## 11. Parent Sidebar

```
┌─────────────────────────────────────┐
│  ☰ My Child's School                │
├─────────────────────────────────────┤
│  ◆ Dashboard                        │
│                                      │
│  ► MY CHILD (select child if multiple)│
│    ○ Attendance                  [ ]│  ← Badge: today's status
│    ○ Exam Results                    │
│    ○ Homework                    [ ]│  ← Badge: pending count
│    ○ Timetable                       │
│    ○ Documents                   [ ]│  ← Badge: new documents
│    ○ Library Books               [ ]│  ← Badge: overdue alert
│    ○ Transport                       │
│    ○ Fee Payment                 [ ]│  ← Badge: pending payment
│    ○ Leave Application               │
│                                      │
│  ► COMMUNICATION                     │
│    ○ Notifications               [ ]│  ← Badge: unread count
│    ○ Contact Teacher                 │
│    ○ School Announcements            │
│                                      │
│  ► ACADEMIC CALENDAR                 │
│                                      │
│  ► AI WORKSPACE                      │
│    ○ Ask ERP                         │
│                                      │
│  ─────────────────────────────────── │
│  🔍 Quick Search                     │
│  ⚙ Profile                           │
│  🚪 Logout                           │
└─────────────────────────────────────┘
```

**Hidden:** All administrative, financial (beyond own), academic (beyond own children), HR, and system modules

---

## 12. Student Sidebar

```
┌─────────────────────────────────────┐
│  ☰ My School                        │
├─────────────────────────────────────┤
│  ◆ Dashboard                        │
│                                      │
│  ► MY ACADEMICS                      │
│    ○ My Timetable                    │
│    ○ My Attendance               [ ]│  ← Badge: today's status
│    ○ My Homework                 [ ]│  ← Badge: pending count
│    ○ My Exams                        │
│    ○ My Results                      │
│                                      │
│  ► MY LIBRARY                        │
│    ○ My Books                    [ ]│  ← Badge: overdue alert
│    ○ Search Catalog                  │
│                                      │
│  ► MY DOCUMENTS                      │
│                                      │
│  ► COMMUNICATION                     │
│    ○ Notifications               [ ]│  ← Badge: unread count
│    ○ School Announcements            │
│                                      │
│  ► ACADEMIC CALENDAR                 │
│                                      │
│  ► SELF SERVICE                      │
│    ○ My Profile                      │
│    ○ Fee Status                      │
│                                      │
│  ► AI WORKSPACE                      │
│    ○ Ask ERP                         │
│                                      │
│  ─────────────────────────────────── │
│  🔍 Quick Search                     │
│  ⚙ Profile                           │
│  🚪 Logout                           │
└─────────────────────────────────────┘
```

**Hidden:** All administrative, financial (beyond own status), other students' data, HR, and system modules

---

## Sidebar Implementation Notes

### Badge Data Sources
| Badge | Data Source | Refresh |
|-------|------------|---------|
| Pending Approvals | LeaveRequest (status=pending) | Real-time |
| Unread Notifications | Notification (read_at=null) | Real-time |
| Overdue Books | BookIssue (due_date < today, returned_at=null) | Daily |
| Pending Attendance | Attendance (date=today, class in user's classes) | Real-time |
| Fee Due | StudentFee (due_amount > 0) | Daily |
| New Inquiries | Student inquiry (status=new) | Real-time |
| Contracts Expiring | Teacher (contract_end < 30 days) | Daily |

### Mobile Responsiveness
- Sidebar collapses to hamburger menu on screens < 768px
- Section headers remain visible, items hide behind expand gesture
- Badges shown as dots on collapsed sidebar
- Quick search always visible

### Performance
- Sidebar items rendered server-side based on permissions
- Badge counts fetched via lazy-load AJAX (not blocking initial render)
- Cached for 5 minutes per user
- Active section highlighted based on current route
