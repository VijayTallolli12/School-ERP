# ROLE BUSINESS PROFILES

**Document:** ROLE_BUSINESS_PROFILES.md
**Date:** 2026-07-07

---

## Super Admin

### Daily Responsibilities
- System-wide oversight across all schools
- Create and manage school instances
- Define global configuration and defaults
- Monitor system health and usage
- Handle escalated issues from School Admins
- Manage role definitions and permission templates
- Review audit logs for security incidents

### Daily Workflow
```
Morning: Check system health → Review support tickets
Mid-day: Audit role changes → Review escalated requests
Evening: Approve new school setups → Review security logs
```

### Dashboard Purpose
System-level command center showing health of all schools, active users, storage, and critical alerts

### Sidebar Modules
Schools Management → Global Settings → RBAC (all schools) → Audit Logs → System Health → Support Tickets

### Reports Required
- System-wide usage analytics
- Per-school subscription/usage reports
- Security audit reports
- Error logs and exception reports

### Approval Responsibilities
- New school creation
- Global role/permission template changes
- System configuration changes
- Data purge/archival requests

### Data Visibility
- ALL data across ALL schools (masked PII by default, full access on explicit drill-down)
- Audit trails for all roles

### AI Features
- Executive Copilot (cross-school analytics)
- System health predictions
- Anomaly detection in usage patterns

### Notifications
- Critical system errors
- School setup completion
- Security incidents
- Storage warnings

### Quick Actions
- Add new school
- View audit log
- System health check
- Manage global roles

---

## School Admin

### Daily Responsibilities
- Oversee entire school operations
- Manage school-level configuration
- Create and manage user accounts
- Assign roles to staff and teachers
- Configure academic structure (classes, sections, subjects)
- Monitor financial health (fees, payroll)
- Handle escalated complaints from parents
- Define fee structures and approve waivers
- Manage school calendar and holidays

### Daily Workflow
```
Morning: Dashboard review → Approve pending requests → Check attendance summary
Mid-day: User management → Fee follow-ups → Staff coordination
Evening: Review reports → Plan next day → System checks
```

### Dashboard Purpose
School operations command center with key metrics across all departments

### Sidebar Modules
Dashboard → Students → Teachers → Parents → Academics → Attendance → Exams → Homework → Timetable → Fees → Payroll → Leave → Library → Transport → Documents → Calendar → Notifications → Reports → Users → Settings → RBAC → AI Workspace

### Reports Required
- Complete school analytics
- Fee collection summary
- Payroll summary
- Attendance trends
- Exam performance analysis
- Teacher workload reports
- Student strength reports

### Approval Responsibilities
- Fee waivers/concessions
- Payroll batch approval
- New user creation
- Role assignment changes
- Large financial adjustments
- Student record deletion
- System configuration changes

### Data Visibility
- ALL data within the school
- Cross-module access (read/write)
- Financial data (full)
- Personnel data (full)

### AI Features
- Executive Copilot (school analytics, trends)
- AI Agents (automated tasks)
- Ask ERP (natural language queries)
- Execution History

### Notifications
- All system notifications
- Pending approvals
- Fee collection alerts
- Payroll exceptions
- Staff leave requests
- Exam result publishing

### Quick Actions
- Add new student
- Add new teacher
- Approve pending leave
- Generate fee report
- Run payroll
- View school analytics

---

## Principal

### Daily Responsibilities
- Academic leadership and oversight
- Monitor student and teacher attendance
- Approve teacher leave requests
- Review exam results before publication
- Conduct academic reviews with teachers
- Approve timetable changes
- Oversee student discipline
- Conduct parent meetings (escalated)
- Verify student documents
- Review academic performance reports

### Daily Workflow
```
Morning: Attendance dashboard → Check teacher attendance → Pending leave approvals
Mid-day: Academic rounds → Classroom observations → Student/parent meetings
Evening: Review homework completion → Exam preparation status → Next day planning
```

### Dashboard Purpose
Academic command center showing attendance, teacher performance, and school metrics

### Sidebar Modules
Dashboard → Students → Teachers → Attendance → Exams → Homework → Timetable → Academics → Calendar → Documents → Leave → Notifications → Reports → AI Workspace

### Reports Required
- Teacher-wise attendance
- Class-wise performance
- Exam result analysis
- Homework completion rates
- Student attendance trends
- Teacher workload report
- Subject-wise performance

### Approval Responsibilities
- Teacher leave approval (or delegate to VP)
- Exam result publication
- Timetable changes
- Student promotion decisions
- Document verification (if VP unavailable)
- New subject/class creation

### Data Visibility
- All academic data school-wide
- Teacher records (view)
- Student records (view)
- Leave records (approval)
- Fee summary (overview, no transaction details)
- Payroll summary (no individual amounts)

### AI Features
- Executive Copilot (school analytics)
- Ask ERP (policy, student queries)
- Insights (performance trends)

### Notifications
- Teacher leave applications
- Low attendance alerts
- Exam result ready for review
- Document verification requests
- Calendar event reminders

### Quick Actions
- Approve/reject leave
- View today's attendance
- Check exam schedule
- Review pending document verifications
- View academic calendar

---

## Teacher

### Daily Responsibilities
- Conduct classes as per timetable
- Mark student attendance daily
- Prepare and share homework
- Create and grade exams
- Enter and verify marks
- Maintain student academic records
- Communicate with parents about progress
- Apply for leave when needed
- View own timetable and schedule
- Track own attendance record
- Upload student documents for verification

### Daily Workflow
```
Morning: Mark period-1 attendance → Teach scheduled classes
Mid-day: Homework assignment → Exam grading → Parent calls/meetings
Evening: Record marks → Review next day lessons → Check timetable → Submit reports
```

### Dashboard Purpose
Classroom command center showing today's schedule, attendance status, and pending academic tasks

### Sidebar Modules
Dashboard → Students (own classes) → Attendance → Exams → Homework → Timetable → Calendar → Documents (upload) → Notifications → Reports → AI Workspace

### Reports Required
- Class-wise attendance report
- Student performance report
- Subject-wise exam analysis
- Homework completion report
- Own leave balance

### Approval Responsibilities
- Homework content (self-approved)
- Exam marks (self-entered, verified by HOD/Principal)
- Student attendance (self-marked)

### Data Visibility
- Own timetable
- Own class students (assigned sections/subjects)
- Own attendance record
- Own leave balance and history
- Student records for assigned classes only
- Academic calendar

### AI Features
- Ask ERP (curriculum queries, student insights)
- AI-assisted grading assistance
- Homework suggestions

### Notifications
- Leave approval/rejection
- Timetable changes
- Parent meeting requests
- Exam schedule changes
- Calendar events
- Homework submissions (if applicable)

### Quick Actions
- Mark today's attendance
- Add homework
- Enter marks
- View timetable
- Apply for leave

---

## HR

### Daily Responsibilities
- Manage teacher lifecycle (hiring to exit)
- Process teacher attendance
- Handle teacher leave management
- Maintain teacher records and documents
- Coordinate teacher training and development
- Manage teacher performance reviews
- Handle disciplinary matters
- Maintain employee contracts and renewals
- Generate teacher-related reports
- Coordinate with Payroll Manager for salary inputs

### Daily Workflow
```
Morning: Teacher attendance → Leave requests review → New joiner coordination
Mid-day: Document verification → Contract renewals → Interview coordination
Evening: Report generation → Training planning → HR analytics
```

### Dashboard Purpose
Human resources command center showing teacher attendance, leave status, and HR metrics

### Sidebar Modules
Dashboard → Teachers → Reports (teacher-related) → Notifications → AI Workspace

### Reports Required
- Teacher attendance summary
- Leave utilization report
- Teacher strength report
- Contract expiry report
- Training completion report
- Teacher performance summary

### Approval Responsibilities
- Teacher attendance verification
- Leave recommendation (to Principal)
- Teacher document verification
- Contract renewal recommendation

### Data Visibility
- All teacher profiles and documents
- Teacher attendance records
- Teacher leave records
- Teacher performance data
- Payroll summary (no individual salary details)
- Student data (not accessible)

### AI Features
- Ask ERP (policy queries)
- Reports generation

### Notifications
- Teacher leave applications
- Contract expiry alerts
- Document expiry alerts
- Attendance discrepancies

### Quick Actions
- View teacher list
- Process attendance
- Check leave requests
- View contracts expiring

---

## Accountant

### Daily Responsibilities
- Manage fee structures for all classes
- Track fee collection and dues
- Process fee receipts and refunds
- Generate fee reports
- Manage transport fee assignments
- Coordinate with parents for fee follow-ups
- Reconcile daily collections
- Generate fee-related reports for management
- Handle fee concessions with School Admin approval
- Manage receipt sequences and numbering

### Daily Workflow
```
Morning: Review yesterday's collections → Process receipts → Send due reminders
Mid-day: Parent queries → Fee structure adjustments → Concession approvals
Evening: Day-end reconciliation → Report generation → Bank deposit coordination
```

### Dashboard Purpose
Finance command center showing fee collection status, dues, and daily collections

### Sidebar Modules
Dashboard → Fees → Transport (view) → Reports → Notifications → AI Workspace

### Reports Required
- Daily collection report
- Fee due list
- Class-wise fee collection
- Concession report
- Receipt register
- Defaulters list
- Transport fee report

### Approval Responsibilities
- Fee receipt cancellation
- Minor fee adjustments (major → School Admin)
- Transport fee assignment

### Data Visibility
- All fee records (students, transactions, receipts)
- Transport fee data
- Reports (finance-related)
- No access to student academic records
- No access to payroll data
- No access to teacher records

### AI Features
- Ask ERP (fee queries, defaulter analysis)
- Reports generation

### Notifications
- Fee due alerts
- Large payment received
- Receipt cancellation requests
- Fee structure change confirmations

### Quick Actions
- Record payment
- Check defaulters
- Generate receipt
- View fee structure
- Print daily report

---

## Payroll Manager

### Daily Responsibilities
- Manage employee salary structures
- Process monthly payroll runs
- Generate and distribute payslips
- Handle salary components (allowances, deductions)
- Manage pay grades and designations
- Coordinate with HR for new joiners and exits
- Process loan/advance deductions
- Generate payroll reports
- Lock payroll after verification
- Handle employee salary queries
- Ensure statutory compliance (tax, PF, etc.)

### Daily Workflow
```
1st-5th: Collect attendance inputs from HR → Verify new joiners/exits → Prepare payroll data
5th-7th: Run payroll calculation → Review exceptions → Generate payslips
8th-10th: Get School Admin approval → Lock payroll → Distribute payslips → Process disbursement
Rest of month: Salary queries → Structure updates → Reporting
```

### Dashboard Purpose
Payroll operations center showing upcoming payroll runs, processed month status, and key metrics

### Sidebar Modules
Dashboard → Payroll → Reports → Notifications → AI Workspace

### Reports Required
- Monthly payroll summary
- Department-wise salary report
- Deduction summary (tax, PF, etc.)
- Year-to-date earnings report
- Payslip history
- Pay grade analysis
- Employee salary structure report

### Approval Responsibilities
- Salary structure changes (recommend → School Admin approves)
- Payroll processing and locking
- Ad-hoc payment processing

### Data Visibility
- All employee salary structures (teachers, staff, admin)
- Full payroll transaction history
- Deduction and tax data
- Attendance summary (for payroll calculation)
- No access to student data
- No access to fee data
- No access to academic records

### AI Features
- Ask ERP (queries)
- Payroll analytics and trends

### Notifications
- Payroll processing reminders
- Lock confirmation
- Payslip generated
- Salary structure change requests
- Exception alerts (missing data)

### Quick Actions
- Run payroll
- Generate payslips
- View salary structure
- Check payroll history
- Export reports

---

## Librarian

### Daily Responsibilities
- Manage book catalog (add, update, remove books)
- Process book issues and returns
- Manage library memberships (students, staff)
- Collect and manage fines for late returns
- Maintain publisher and author records
- Generate library reports
- Process book requests from students/teachers
- Annual stock verification
- Manage damaged/lost books and replacements
- Recommend new book purchases

### Daily Workflow
```
Morning: Process returns → Check fine collection → New issues
Mid-day: Catalog management → Book requests processing → Member queries
Evening: Shelf organization → Stock verification → Reports
```

### Dashboard Purpose
Library operations center showing issued books, overdue items, and catalog statistics

### Sidebar Modules
Dashboard → Library → Reports → Notifications → AI Workspace

### Reports Required
- Books issued (currently borrowed)
- Overdue books list
- Most borrowed books
- Fine collection report
- Member-wise book history
- Stock report (category-wise)
- Lost/damaged books report

### Approval Responsibilities
- Library fine waiver (up to limit)
- Book removal from catalog (with Principal approval)
- Long-term book lending (reference books)

### Data Visibility
- Complete library catalog
- Issue/return history for all members
- Fine records
- Member profiles (name, class/employee ID)
- No access to academic records
- No access to financial records

### AI Features
- Ask ERP (book search, availability queries)
- Reading recommendations (based on borrowing history)

### Notifications
- Overdue book reminders
- Book request notifications
- Fine payment confirmations
- New book arrival alerts

### Quick Actions
- Issue a book
- Return a book
- Collect fine
- Add new book
- Search catalog
- Check member history

---

## Receptionist

### Daily Responsibilities
- Greet visitors and manage visitor log
- Register new student inquiries
- Assist parents with inquiries
- Manage incoming/outgoing communication
- Maintain student inquiry database
- Coordinate with teachers for parent meetings
- Handle front-desk administrative tasks
- Direct calls and messages to appropriate staff
- Assist with student record lookups

### Daily Workflow
```
Morning: School opening → Welcome students → Attendance monitoring
Mid-day: Visitor management → Parent inquiries → Call management → New registrations
Evening: End-of-day reports → Next day preparation → School closing
```

### Dashboard Purpose
Front-desk command center showing today's visitors, new inquiries, and quick access to student info

### Sidebar Modules
Dashboard → Students (view/create) → Parents (view/create) → Notifications → AI Workspace

### Reports Required
- Daily visitor log
- New inquiries report
- Student record lookups

### Approval Responsibilities
- None (information gathering only)

### Data Visibility
- Student profiles (view, create new inquiry)
- Parent contact information
- Visitor records
- No access to academic records, fees, payroll, or HR data

### AI Features
- Ask ERP (general queries, student lookup)

### Notifications
- Parent meeting requests
- Visitor notifications
- New inquiry assignments

### Quick Actions
- Register new inquiry
- Look up student
- View visitor log
- Contact teacher

---

## Staff

### Daily Responsibilities
- Provide administrative support to school operations
- Assist in event coordination
- Manage school supplies and inventory
- Handle non-academic administrative tasks
- Support reception during busy periods
- Coordinate maintenance and facilities
- Assist with document preparation
- Support HR with administrative tasks

### Daily Workflow
```
Morning: Check daily tasks → Assist with school opening
Mid-day: Administrative support → Event preparation → Supply management
Evening: Task completion report → Next day preparation
```

### Dashboard Purpose
Task management center showing daily assignments, schedule, and pending requests

### Sidebar Modules
Dashboard → Notifications → AI Workspace

### Reports Required
- Own attendance record
- Leave balance

### Approval Responsibilities
- None

### Data Visibility
- Own profile
- Own attendance
- Own leave records
- Dashboard metrics (limited)
- No access to student, teacher, financial, or academic data

### AI Features
- Ask ERP (general queries)

### Notifications
- Leave approval/rejection
- Event assignments
- Administrative announcements

### Quick Actions
- Check schedule
- Track attendance
- Apply for leave

---

## Parent

### Daily Responsibilities
- Monitor child's academic progress
- View child's attendance record
- Pay school fees
- Communicate with teachers
- Approve/acknowledge school communications
- View exam results and report cards
- Track child's homework and assignments
- Monitor child's library books
- Track school transport
- Apply for leave (if required by school policy)
- Download student documents

### Daily Workflow
```
Morning: Check child's attendance → View school notifications
Mid-day: Check homework updates → Fee payment if due
Evening: Review exam results → Communicate with teachers → View timetable
```

### Dashboard Purpose
Child monitoring center showing attendance, grades, homework, fees, and school communications

### Sidebar Modules
Dashboard → Attendance (child) → Fees → Exams → Results → Homework → Timetable → Calendar → Documents → Transport → Library → Notifications → AI Workspace

### Reports Required
- Child attendance report
- Fee payment history
- Exam result report card
- Homework completion status
- Library book history
- Transport route details

### Approval Responsibilities
- Acknowledge school communications
- Fee payment authorization

### Data Visibility
- Own children only
- Children's attendance, exams, homework, timetable, fees, library, transport
- No access to other students' data
- No access to school financial or HR data

### AI Features
- Ask ERP (child-specific queries only — "What is my child's next exam?")

### Notifications
- Fee due reminders
- Exam results published
- Low attendance alerts
- Homework assignments
- Parent-teacher meeting invitations
- Transport delays
- Library book due reminders
- School announcements

### Quick Actions
- View child attendance
- Pay fees
- Check exam results
- View homework
- Contact teacher

---

## Student

### Daily Responsibilities
- Attend classes as per timetable
- Complete and submit homework
- Take exams
- Track own attendance
- View own academic performance
- Check timetable and schedule
- Borrow and return library books
- Track own fee status (if applicable)
- View school announcements
- Download own documents

### Daily Workflow
```
Morning: Check timetable → Attend classes
Mid-day: Homework completion → Library visits
Evening: Exam preparation → Check attendance → Plan next day
```

### Dashboard Purpose
Self-learning center showing personal schedule, attendance, grades, homework, and announcements

### Sidebar Modules
Dashboard → Attendance (self) → Exams → Homework → Timetable → Calendar → Library (self) → Documents → Notifications → AI Workspace

### Reports Required
- Own attendance report
- Own exam results and report card
- Own homework status
- Own library book history
- Own fee status

### Approval Responsibilities
- None

### Data Visibility
- Self only
- Own attendance, exams, homework, timetable, fees, library, documents
- No access to other students' data
- No access to any administrative data

### AI Features
- Ask ERP (self-limited — "What are my pending homework assignments?")

### Notifications
- Homework assignments
- Exam schedule
- Results published
- Book due reminders
- Timetable changes
- School announcements
- Attendance alerts

### Quick Actions
- View timetable
- Check homework
- View attendance
- Check exam schedule
- View results
