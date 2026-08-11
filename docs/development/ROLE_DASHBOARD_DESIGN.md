# ROLE DASHBOARD DESIGN

**Document:** ROLE_DASHBOARD_DESIGN.md
**Date:** 2026-07-07

---

## Design Philosophy

Each dashboard answers three questions specific to the role:
1. **What needs my attention now?** (Alerts, pending approvals, issues)
2. **How are we performing?** (KPIs, trends, metrics)
3. **What should I do next?** (Quick actions, schedule, tasks)

---

## 1. Super Admin Dashboard

### KPI Cards
| Metric | Source | Refresh |
|--------|--------|---------|
| Total Schools Active | Schools table | Real-time |
| Total Users (All Schools) | Users table | Daily |
| Storage Used | System | Real-time |
| Active Sessions Today | LoginActivity | Real-time |
| Pending Support Tickets | Support system | Real-time |
| System Health Score | Health checks | 5 min |

### Charts
| Chart | Type | Description |
|-------|------|-------------|
| User Growth (All Schools) | Line (30d) | New user registrations across all schools |
| School Usage | Bar | Active users per school |
| Storage Trend | Area (30d) | Storage consumption over time |
| Error Rate | Line (7d) | Application error frequency |
| Peak Usage Hours | Heatmap | Hourly active users across all schools |

### Widgets
| Widget | Content |
|--------|---------|
| System Health | Uptime, response time, queue size, cache hit rate |
| School Status | List of all schools with status indicators (green/yellow/red) |
| Recent Errors | Latest 5 application errors with stack traces |
| License Expiry | Schools with licenses expiring in next 30 days |
| Active Users Now | Real-time active user count per school |

### Quick Actions
- View System Health Dashboard
- Manage Global Roles
- School Management
- View Audit Log
- Configuration Manager

### Alerts
- School offline for > 1 hour
- Storage usage > 85%
- Error rate spike > 5%
- Failed login attempts > 50 in 1 hour
- License expires within 7 days

### AI Insights
- **Usage Predictions**: "School XYZ is projected to reach 1000 users next month"
- **Anomaly Detection**: "Unusual login pattern detected from IP range 203.x.x.x"
- **Growth Analysis**: "School ABC shows 40% user growth this quarter"

### Recent Activities
- Last 10 login activities across all schools
- Recent role/permission changes
- Recent school configuration changes

---

## 2. School Admin Dashboard

### KPI Cards
| Metric | Source | Refresh |
|--------|--------|---------|
| Total Students | StudentSession | Daily |
| Total Teachers | Teachers | Daily |
| Today's Attendance % | Attendance | Real-time |
| Total Collected (Fees) | FeePayment | Real-time |
| Pending Approvals | Various | Real-time |
| Upcoming Events | Calendar | Daily |
| Payroll Status | PayrollRun | Monthly |
| Leave Requests Pending | LeaveRequest | Real-time |

### Charts
| Chart | Type | Description |
|-------|------|-------------|
| Attendance Trend | Line (30d) | Daily school-wide attendance % |
| Fee Collection Trend | Area (30d) | Daily collection amount |
| Class-wise Strength | Bar | Students per class (current year) |
| Revenue vs Expenses | Line (12m) | Fee collection vs payroll costs |
| Exam Performance | Radar | Subject-wise average marks |
| Teacher Workload | Bar | Periods per teacher per week |

### Widgets
| Widget | Content |
|--------|---------|
| Today's Attendance | Donut chart (Present/Absent/Leave) |
| Fee Summary | Collected vs Pending vs Due |
| Upcoming Events | Next 5 calendar events |
| Pending Approvals | Leave, fee concession, document verification count |
| Quick Stats Grid | Total Staff, Classes, Sections, Subjects |

### Quick Actions
- Add New Student
- Add New Teacher
- Approve Pending Leave
- View Fee Report
- Run Payroll
- Send Notification
- View School Analytics
- Manage Users

### Alerts
- Attendance below 75% for any class
- Fee collection below 60% of target
- Payroll processing overdue
- Teacher leave balance critical
- Exam schedule approaching without results
- Library overdue books > 50

### AI Insights
- **Enrollment Trends**: "Class 1 enrollment is up 15% vs last year"
- **At-Risk Students**: "15 students with attendance < 60% need intervention"
- **Fee Collection Forecast**: "Projected 85% collection by month end"
- **Staffing Alert**: "Science department has 20% higher student-teacher ratio than other departments"

### Recent Activities
- Last 10 login activities
- Recent fee collections
- Recent leave approvals
- Recent student admissions

---

## 3. Principal Dashboard

### KPI Cards
| Metric | Source | Refresh |
|--------|--------|---------|
| Total Students | StudentSession | Daily |
| Total Teachers | Teachers | Daily |
| Today's Student Attendance % | Attendance | Real-time |
| Today's Teacher Attendance % | TeacherAttendance | Real-time |
| Pending Leave Approvals | LeaveRequest | Real-time |
| Homework Pending Review | Homework | Daily |
| Upcoming Exams | Exam | Daily |
| Class Average Performance | ExamResult | Per exam |

### Charts
| Chart | Type | Description |
|-------|------|-------------|
| Class-wise Attendance | Bar (7d) | Average attendance % per class |
| Subject Performance | Radar | Average marks per subject (latest exam) |
| Teacher Attendance Trend | Line (30d) | Daily teacher attendance % |
| Homework Completion | Bar | Completion % per class |
| Leave Utilization | Pie | Leave type distribution (approved leaves) |

### Widgets
| Widget | Content |
|--------|---------|
| Today's Attendance | Donut (Present/Absent/Leave for students + teachers) |
| Pending Approvals | Leave requests, document verifications |
| Academic Calendar | Today's events and upcoming deadlines |
| School Overview | Total classes, sections, active teachers, students per class |
| Low Attendance Alert | Classes with < 70% attendance today |

### Quick Actions
- Approve/Reject Leave
- View Timetable
- View Reports
- Check Exam Schedule
- Review Homework Status
- Academic Calendar
- View Student Analytics

### Alerts
- Teacher absent > 3 days in a month
- Class attendance < 70%
- Exam results pending publication
- Student discipline incidents flagged
- Parent complaint unresolved > 48 hours
- Homework submission < 60% in any class

### AI Insights
- **Academic Trends**: "Class 10 Science scores dropped 8% compared to last term"
- **Attendance Patterns**: "Monday attendance is consistently 5% lower than other days"
- **Teacher Performance**: "3 teachers have > 90% student pass rate — recognition recommended"
- **Intervention Needed**: "12 students failed 2+ subjects — parent meeting recommended"

### Recent Activities
- Last 6 login activities
- Recent leave approvals
- Recent exam publications
- Recent document verifications

---

## 4. Vice Principal Dashboard (if applicable)

### KPI Cards
| Metric | Source | Refresh |
|--------|--------|---------|
| Today's Attendance % | Attendance | Real-time |
| Teacher Attendance % | TeacherAttendance | Real-time |
| Disciplinary Pending | Discipline system | Daily |
| Timetable Conflicts | Timetable | Weekly |
| Exam Schedule Status | Exam | Per exam period |

### Quick Actions
- Manage Timetable
- Verify Documents
- Check Attendance Report
- Exam Duty Assignment

### Widgets
- Same as Principal but focused on discipline, timetable, examinations
- Timetable conflict alerts
- Exam supervision schedule

---

## 5. Teacher Dashboard

### KPI Cards
| Metric | Source | Refresh |
|--------|--------|---------|
| Today's Classes | Timetable | Daily |
| Today's Attendance Pending | Attendance | Real-time (until marked) |
| Homework Pending Review | Homework | Daily |
| Upcoming Exams (own subjects) | Exam | Per exam period |
| My Attendance Rate (current month) | Attendance | Daily |
| Leave Balance | LeaveType | Real-time |
| Students in My Classes | StudentSession | Academic year |

### Charts
| Chart | Type | Description |
|-------|------|-------------|
| My Attendance Trend | Line (30d) | Personal attendance % |
| Class Attendance Trend | Line (30d) | Attendance % for own classes |
| Subject Performance | Bar | Latest exam results per subject taught |
| Homework Submission Rate | Bar | Submission % per class |

### Widgets
| Widget | Content |
|--------|---------|
| Today's Schedule | List of today's periods with class, subject, time |
| Pending Attendance | Classes where attendance not yet marked |
| Homework Overview | Pending creation, pending review, submitted |
| Upcoming Exams | Exam schedule for own subjects |
| Leave Overview | Available leave balance, pending applications |
| Quick Stats | Total students, subjects, classes assigned |

### Quick Actions
- Mark Today's Attendance
- Add Homework
- Enter Marks
- View Timetable
- Apply for Leave
- View My Schedule
- Check Student Performance

### Alerts
- Attendance not marked for first period
- Homework not created for today's classes
- Exam marks not entered within deadline
- Leave balance low (< 2 days remaining)
- Parent meeting scheduled today
- Timetable change notification

### AI Insights
- **Student Attention**: "3 students have missed 5+ classes this month — check-in recommended"
- **Homework Alert**: "Class 5-A has 40% lower submission rate than other classes"
- **Performance Pattern**: "Students who scored poorly in Term 1 are showing 15% improvement"
- **Teaching Insight**: "Friday afternoon classes show 20% lower attendance — consider activity-based learning"

### Recent Activities
- Last 5 login activities
- Recent homework assignments
- Recent attendance marking
- Recent leave applications

---

## 6. HR Dashboard

### KPI Cards
| Metric | Source | Refresh |
|--------|--------|---------|
| Total Teachers | Teachers | Daily |
| Teacher Attendance Today | TeacherAttendance | Real-time |
| Pending Leave Requests | LeaveRequest | Real-time |
| Contracts Expiring (30 days) | Teacher contracts | Daily |
| New Joiners This Month | Teachers | Monthly |
| Teacher Absent Today | TeacherAttendance | Real-time |

### Charts
| Chart | Type | Description |
|-------|------|-------------|
| Teacher Attendance Trend | Line (30d) | Daily teacher attendance % |
| Department-wise Strength | Bar | Teachers per department/subject |
| Leave Utilization | Bar | Leave taken vs balance per teacher type |
| Joiner vs Leaver Trend | Area (12m) | Monthly teacher churn |

### Widgets
| Widget | Content |
|--------|---------|
| Today's Teacher Attendance | List of absent teachers today |
| Pending Leave | Teacher leave requests requiring recommendation |
| Contract Alerts | Contracts expiring in 30, 60, 90 days |
| Department Overview | Teacher count per department |
| Recent Joiners | Teachers joined this month |

### Quick Actions
- View Teacher List
- Process Teacher Attendance
- Manage Leave Requests
- View Contracts Expiring
- Teacher Reports

### Alerts
- Teacher absent without leave
- Contract expiring within 30 days
- Leave balance exhausted
- New joiner documentation pending
- Teacher attendance below 80% for 2 consecutive weeks

### AI Insights
- **Retention Risk**: "3 teachers with high performance have not taken leave in 6 months — burnout risk"
- **Leave Patterns**: "Science department has highest leave utilization — investigate workload"
- **Staffing Gap**: "Mathematics department needs 2 additional teachers based on student-teacher ratio"

### Recent Activities
- Recent teacher attendance records
- Recent leave recommendations
- Recent teacher profile updates

---

## 7. Accountant Dashboard

### KPI Cards
| Metric | Source | Refresh |
|--------|--------|---------|
| Total Collection Today | FeePayment | Real-time |
| Monthly Collection | FeePayment | Daily |
| Pending Dues | StudentFee | Real-time |
| Total Defaulters | StudentFee | Daily |
| Collection Rate % | FeePayment | Daily |
| Receipts Generated Today | FeeReceipt | Real-time |
| Transport Fees Pending | TransportAssignment | Daily |

### Charts
| Chart | Type | Description |
|-------|------|-------------|
| Daily Collection Trend | Bar (30d) | Daily fee collection amount |
| Class-wise Collection | Bar | Collection % per class |
| Fee Category Breakdown | Pie | Revenue by fee category |
| Monthly Target vs Actual | Line (12m) | Fee target vs actual collection |

### Widgets
| Widget | Content |
|--------|---------|
| Today's Collections | Real-time list of payments received today |
| Top Defaulters | Students with highest overdue amount |
| Pending Concessions | Fee concession requests awaiting approval |
| Receipt Counter | Number of receipts generated today |
| Quick Summary | Collected, Pending, Overdue amounts |

### Quick Actions
- Record Payment
- Check Defaulters
- Generate Receipt
- View Fee Structure
- Print Daily Report
- View Fee Reports

### Alerts
- Daily collection target not met
- Defaulter count > 50
- Fee concession requests pending > 48 hours
- Receipt sequence nearing end
- Transport fees not assigned for new students

### AI Insights
- **Collection Forecast**: "Projected 92% collection rate by month end"
- **Defaulter Pattern**: "15 parents consistently pay 2 weeks late — send automated reminders"
- **Fee Structure Optimization**: "Class 8 sports fee has 30% non-payment — consider restructuring"

### Recent Activities
- Last 10 fee payments recorded
- Recent receipt generations
- Recent fee structure updates

---

## 8. Payroll Manager Dashboard

### KPI Cards
| Metric | Source | Refresh |
|--------|--------|---------|
| Total Employees | EmployeeSalaryStructure | Monthly |
| Current Month Payroll Amount | PayrollRun | During processing |
| Payslips Generated | EmployeePayslip | During processing |
| Pending Inputs | PayrollRun | During processing |
| Last Processed Month | PayrollRun | Monthly |
| Average Processing Time | PayrollRun | Monthly |

### Charts
| Chart | Type | Description |
|-------|------|-------------|
| Monthly Payroll Trend | Bar (12m) | Total payroll amount per month |
| Department-wise Salary | Bar | Salary distribution by department |
| Deduction Breakdown | Pie | Tax, PF, other deductions |
| Salary Component Split | Pie | Basic, HRA, allowances, deductions |

### Widgets
| Widget | Content |
|--------|---------|
| Payroll Status | Current month processing stage (Not Started / Input Pending / Processing / Awaiting Approval / Locked) |
| Missing Inputs | Teachers without attendance data for current month |
| Recent Payrolls | Last 3 processed payroll months |
| Salary Structure Alerts | New joiners/exits not reflected in salary structure |

### Quick Actions
- Run Payroll
- Generate Payslips
- View Salary Structures
- Check Payroll History
- Export Reports
- Lock Payroll

### Alerts
- Attendance inputs missing for payroll processing
- New joiners without salary structure
- Payroll not processed by 7th of month
- Ad-hoc payments/ deductions pending
- Tax declaration pending from employees

### AI Insights
- **Salary Trends**: "Average salary increased 8% YoY"
- **Cost Analysis**: "Payroll is 65% of total school expenses"
- **Leave Impact**: "Unpaid leave deductions this month: Rs. 45,000"

### Recent Activities
- Recent payroll runs
- Recent salary structure changes
- Recent payslip downloads

---

## 9. Librarian Dashboard

### KPI Cards
| Metric | Source | Refresh |
|--------|--------|---------|
| Total Books | Book | Daily |
| Books Issued Now | BookIssue | Real-time |
| Overdue Books | BookIssue | Real-time |
| Today's Issues | BookIssue | Real-time |
| Today's Returns | BookIssue | Real-time |
| Pending Fines | FineSetting | Real-time |
| Members Registered | Users/Students | Daily |

### Charts
| Chart | Type | Description |
|-------|------|-------------|
| Weekly Issue Trend | Bar (7d) | Books issued per day this week |
| Category Distribution | Pie | Books by category |
| Most Borrowed Books | Horizontal Bar | Top 10 most borrowed books |
| Member Type Usage | Pie | Issues by student vs staff |
| Overdue Trend | Line (30d) | Overdue books count over time |

### Widgets
| Widget | Content |
|--------|---------|
| Currently Issued | Recent 5 book issues |
| Overdue Alerts | Books overdue by > 7 days |
| Popular Books | Top 5 most borrowed books this month |
| Low Stock Alert | Books with quantity < 3 |
| New Arrivals | Books added in last 7 days |

### Quick Actions
- Issue a Book
- Return a Book
- Collect Fine
- Add New Book
- Search Catalog
- View Reports
- Check Member History

### Alerts
- Books overdue > 30 days (escalate)
- Book quantity = 0 for popular titles
- Fine collection pending > 7 days
- New book requests from teachers pending
- Annual stock verification due

### AI Insights
- **Reading Trends**: "Science fiction genre has 40% higher borrowing than last year"
- **Book Recommendation**: "Students who borrowed 'Harry Potter' also borrowed 'Percy Jackson' — recommend series"
- **Usage Prediction**: "Exam season approaching — reference book demand will increase 200%"

### Recent Activities
- Recent book issues/returns
- Recent fine collections
- Recent catalog additions

---

## 10. Receptionist Dashboard

### KPI Cards
| Metric | Source | Refresh |
|--------|--------|---------|
| Visitors Today | Visitor log | Real-time |
| New Inquiries Today | Student inquiries | Real-time |
| Calls Received | Call log | Real-time |
| Appointments Scheduled | Appointment system | Daily |
| Pending Follow-ups | Inquiry status | Real-time |

### Widgets
| Widget | Content |
|--------|---------|
| Today's Visitors | List of visitors with purpose and check-in time |
| New Inquiries | Recent student inquiries requiring follow-up |
| Quick Student Lookup | Search bar for instant student profile access |
| Today's Schedule | Appointments, meetings, events |
| Pending Tasks | Follow-ups, call-backs, document collection |

### Quick Actions
- Register New Inquiry
- Look Up Student
- View Visitor Log
- Contact Teacher
- View Student/Parent Records

### Alerts
- No follow-up on inquiry > 24 hours
- Parent waiting beyond appointment time
- Urgent message for teacher/staff

---

## 11. Staff Dashboard

### KPI Cards
| Metric | Source | Refresh |
|--------|--------|---------|
| Today's Attendance | Own | Real-time |
| Leave Balance | LeaveType | Daily |
| Pending Tasks | Task system | Real-time |
| Upcoming Events | Calendar | Daily |

### Widgets
| Widget | Content |
|--------|---------|
| Today's Schedule | Assigned duties/tasks for today |
| Leave Overview | Available leave balance |
| Upcoming Events | School events this week |
| Notifications | Recent announcements |

### Quick Actions
- Track Attendance
- Apply for Leave
- Check Schedule
- View Notifications

### Alerts
- Task deadline approaching
- Event schedule changed
- Leave balance low

---

## 12. Parent Dashboard

### KPI Cards (per child — shown for each child)
| Metric | Source | Refresh |
|--------|--------|---------|
| Today's Attendance | Attendance | Real-time |
| Upcoming Exams | Exam | Per exam period |
| Pending Homework | Homework | Daily |
| Fee Due | StudentFee | Real-time |
| Last Exam Score | ExamResult | Per exam |
| Library Books Issued | BookIssue | Real-time |
| Transport Status | Transport | Real-time (during commute) |

### Charts
| Chart | Type | Description |
|-------|------|-------------|
| Child's Attendance Trend | Line (30d) | Daily attendance % |
| Subject-wise Performance | Radar | Latest exam marks across subjects |
| Fee Payment History | Bar (12m) | Monthly fee payment status |

### Widgets
| Widget | Content |
|--------|---------|
| Today's Attendance | Present/Absent status with timestamp |
| Homework Pending | List of pending homework with due dates |
| Recent Exam Results | Latest exam scores for each subject |
| Fee Status | Paid, due, overdue with amounts |
| Upcoming Events | Parent-teacher meetings, holidays |
| Transport Alert | Bus delay or route change notifications |
| Quick Stats | Attendance %, homework completion %, class rank |

### Quick Actions
- View Attendance Report
- Pay Fees
- Check Exam Results
- View Homework
- Contact Teacher
- View Timetable
- Download Documents

### Alerts
- Child absent without prior notice
- Fee due within 7 days
- Exam results published
- Homework submission missed
- Parent-teacher meeting scheduled
- Library book overdue
- Transport route changed

### AI Insights
- **Academic Progress**: "Your child improved 12% in Mathematics this term"
- **Attendance Pattern**: "Your child's Monday attendance is lower than other days"
- **Performance Alert**: "Your child's Science score dropped 15% — consider extra help"
- **Personalized Recommendation**: "Based on interests, recommend science enrichment program"

### Recent Activities
- Recent attendance records
- Recent fee payments
- Recent exam results published
- Recent teacher communications

---

## 13. Student Dashboard

### KPI Cards
| Metric | Source | Refresh |
|--------|--------|---------|
| Today's Attendance | Attendance | Real-time |
| Upcoming Exams | Exam | Per exam period |
| Homework Pending | Homework | Daily |
| Last Exam Score | ExamResult | Per exam |
| Library Books Issued | BookIssue | Real-time |
| Leave Balance (if applicable) | LeaveType | Daily |

### Charts
| Chart | Type | Description |
|-------|------|-------------|
| My Attendance Trend | Line (30d) | Personal attendance % |
| Subject-wise Performance | Bar | Marks per subject (latest exam) |
| Homework Completion | Pie | Completed vs pending homework |

### Widgets
| Widget | Content |
|--------|---------|
| Today's Schedule | Today's periods with subject and teacher |
| Pending Homework | Assignments due this week |
| Upcoming Exams | Exam schedule for next 30 days |
| Recent Results | Latest exam scores |
| Library Status | Books currently issued, due dates |
| Quick Stats | Attendance %, rank (if available), homework completion % |

### Quick Actions
- View Timetable
- Check Homework
- View Attendance
- Check Exam Schedule
- View Results
- View Library Account

### Alerts
- Homework due today
- Exam tomorrow
- Attendance below 75%
- Library book due for return
- Exam results published
- Timetable change

### AI Insights
- **Personalized Performance**: "You improved 10% in Science compared to last term"
- **Study Recommendation**: "Based on weak areas, practice more Algebra problems"
- **Attendance Impact**: "Your attendance this month is 80% — maintain above 75%"

### Recent Activities
- Recent attendance records
- Recent homework submissions
- Recent exam results
- Recent library transactions
