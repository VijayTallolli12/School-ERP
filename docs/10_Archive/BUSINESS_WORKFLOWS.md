# BUSINESS WORKFLOWS

**Document:** BUSINESS_WORKFLOWS.md
**Date:** 2026-07-07

---

## Workflow 1: Student Admission

### Actors
- **Receptionist** — Initial inquiry registration
- **Parent** — Submits application and documents
- **School Admin** — Reviews and approves admission
- **Accountant** — Assigns fee structure
- **Teacher** — Class assignment (if applicable)

### Steps

```
1. INQUIRY
   └─ Receptionist registers walk-in/online inquiry
       ├─ Captures: Parent name, Child name, Class seeking, Contact, Status
       └─ System creates lead record

2. APPLICATION
   └─ Parent submits application form
       ├─ Student details (name, DOB, gender, address, photo)
       ├─ Parent details (name, occupation, phone, email)
       ├─ Previous school records (if applicable)
       └─ Upload documents (birth certificate, transfer cert, photos)

3. DOCUMENT VERIFICATION
   └─ Vice Principal / designated verifier reviews documents
       ├─ Verify authenticity of uploaded documents
       ├─ Mark documents as verified / rejected
       └─ Request additional documents if needed

4. ENTRANCE ASSESSMENT (if applicable)
   └─ Teacher / HOD conducts assessment
       ├─ Schedule assessment date
       ├─ Record assessment scores
       └─ Mark pass/fail

5. ADMISSION APPROVAL
   └─ School Admin / Principal approves admission
       ├─ Review application + documents + assessment
       ├─ Approve or reject
       └─ Generate admission number

6. FEE ASSIGNMENT
   └─ Accountant assigns fee structure
       ├─ Select applicable fee categories
       ├─ Apply concessions if any (requires School Admin approval)
       └─ Generate initial fee invoice

7. CLASS ASSIGNMENT
   └─ School Admin assigns class and section
       ├─ Create StudentSession record
       ├─ Assign roll number
       └─ Notify class teacher

8. INDUCTION
   └─ School Admin / Teacher conducts induction
       ├─ Provide student ID card
       ├─ Provide timetable
       ├─ Add to transport route (if applicable)
       └─ Add to library membership

9. COMPLETION
   └─ System marks student as Active
       ├─ Parent account created with login credentials
       ├─ Student account created with login credentials
       └─ Welcome notification sent to parent and student
```

### Approval Points
- Step 5: Admission Approval (School Admin / Principal)
- Step 6: Fee Concession Approval (School Admin)

### Notifications
- Application submitted → Admin
- Document verified → Parent
- Admission approved → Parent, Student
- Fee assigned → Parent
- Class assigned → Teacher (class teacher)
- Welcome credentials → Parent, Student

### Audit Trail
- Every step timestamped with user ID
- Document verification history
- Admission approval with reason
- Fee structure assignment history

---

## Workflow 2: Student Promotion

### Actors
- **Teacher** — Academic evaluation input
- **Principal** — Promotion approval
- **School Admin** — Promotion execution

### Steps

```
1. PRE-PROMOTION REVIEW (Term 3 / Year-end)
   └─ Principal initiates promotion cycle
       ├─ Set promotion criteria (min attendance %, min marks)
       └─ System identifies students meeting/not meeting criteria

2. TEACHER FEEDBACK
   └─ Teachers provide promotion recommendations
       ├─ Academic performance review
       ├─ Behavioral assessment
       └─ Recommendation: Promote / Retain / Conditional

3. PRINCIPAL REVIEW
   └─ Principal reviews all recommendations
       ├─ Review borderline cases
       ├─ Make final promotion decisions
       └─ Record reasons for retention

4. PROMOTION EXECUTION
   └─ School Admin executes promotion batch
       ├─ Select students to promote
       ├─ Select target class for each promoted student
       ├─ System creates new StudentSession records
       ├─ Mark previous session as completed
       └─ Generate new roll numbers

5. NOTIFICATION
   └─ System notifies parents of promotion status
       ├─ Promoted: New class, section, roll number
       ├─ Retained: Reason and improvement plan
       └─ Conditional: Conditions for promotion
```

### Approval Points
- Step 3: Promotion decisions (Principal)
- Step 4: Execution (School Admin)

### Notifications
- Promotion result → Parent, Student
- Retention warning → Parent (with meeting request)

### Audit Trail
- Promotion criteria settings
- Teacher recommendations
- Principal decisions with reasons
- Batch execution log

---

## Workflow 3: Teacher Attendance

### Actors
- **HR** — Marks teacher attendance
- **Teacher** — Views own attendance record
- **Principal** — Views attendance reports

### Steps

```
1. DAILY MARKING (Morning)
   └─ HR marks teacher attendance in system
       ├─ Login to Teacher Attendance module
       ├─ Mark each teacher: Present / Absent / Late / Half-Day / On Leave
       ├─ Add remarks for absent/late (reason)
       └─ System logs timestamp

   Alternative: Self-marking via Kiosk / Mobile App
   └─ Teacher marks own attendance
       ├─ Login with biometric/PIN
       ├─ System records check-in time
       └─ HR verifies and approves

2. MID-DAY RECONCILIATION
   └─ HR reconciles attendance
       ├─ Check for unmarked teachers
       ├─ Follow up with absent teachers
       └─ Update records if needed

3. END OF DAY
   └─ HR finalizes attendance for the day
       ├─ System locks attendance (no further changes)
       ├─ Generate daily attendance report
       └─ Notify Principal of absences

4. MONTHLY CLOSURE
   └─ HR closes monthly attendance
       ├─ Generate monthly attendance summary
       ├─ Forward to Payroll Manager for salary calculation
       └─ Archive monthly report
```

### Approval Points
- Step 3: Day closure (HR — system-enforced after cut-off time)
- Step 4: Monthly closure (HR + Payroll Manager)

### Notifications
- Absent without notice → Teacher, Principal
- Attendance corrected → Teacher
- Monthly summary ready → Payroll Manager

### Audit Trail
- Daily attendance log with timestamps
- Late/correction entries with reasons
- Monthly closure with approver ID

---

## Workflow 4: Student Attendance

### Actors
- **Teacher** — Marks attendance
- **Principal** — Reviews attendance trends
- **Parent** — Views child's attendance
- **School Admin** — Configures attendance policy

### Steps

```
1. PERIOD-WISE MARKING
   └─ Teacher marks attendance for each period
       ├─ Select class and period
       ├─ Mark each student: Present / Absent / Late
       ├─ System auto-fills based on previous periods (smart default)
       └─ Submit attendance

   Alternative: Daily Single Marking
   └─ Teacher marks once for the day (homeroom teacher)
       ├─ Mark entire class
       ├─ Individually adjust absent/late students
       └─ Submit

2. PARENT NOTIFICATION (Real-time)
   └─ System sends notification to parent
       ├─ If student marked absent
       ├─ If student marked late
       └─ Include timestamp of marking

3. DAILY REVIEW
   └─ Principal reviews daily attendance summary
       ├─ View class-wise attendance %
       ├─ View teacher-wise attendance marking compliance
       └─ Flag classes with low attendance

4. WEEKLY REPORT
   └─ System generates weekly attendance report
       ├─ Students with < 75% attendance flagged
       ├─ Notification sent to parents of flagged students
       └─ Principal notified

5. MONTHLY CLOSURE
   └─ Attendance locked for the month
       ├─ Generate monthly report
       ├─ Archive for academic records
       └─ Flag students at risk (< 75% attendance)
```

### Approval Points
- Step 5: Monthly closure (System auto-lock after configured date)

### Notifications
- Absent marking → Parent (immediate)
- Late arrival → Parent
- Weekly low attendance alert → Parent, Principal
- Monthly attendance summary → Parent

### Audit Trail
- Period-by-period attendance log
- Late/correction entries with reason and user ID
- Monthly archival log

---

## Workflow 5: Homework

### Actors
- **Teacher** — Creates and assigns homework
- **Student** — Views and completes homework
- **Parent** — Views child's homework
- **Principal** — Reviews homework load

### Steps

```
1. CREATION
   └─ Teacher creates homework assignment
       ├─ Select class, section, subject
       ├─ Enter title, description, instructions
       ├─ Attach files (worksheets, reference material)
       ├─ Set due date and time
       ├─ Set max marks (optional)
       └─ Publish

2. NOTIFICATION
   └─ System notifies students and parents
       ├─ In-app notification
       ├─ Email (if enabled)
       └─ Include title, subject, due date

3. SUBMISSION
   └─ Student submits homework
       ├─ Upload file(s)
       ├─ Enter text response
       └─ Submit before due date/time

4. REVIEW & GRADING
   └─ Teacher reviews submissions
       ├─ View submitted homework list
       ├─ Download attachments
       ├─ Add comments/feedback
       ├─ Assign marks/grades
       └─ Mark as reviewed

5. FEEDBACK TO STUDENT
   └─ Student views graded homework
       ├─ See marks/grades
       ├─ Read teacher comments
       └─ View corrected attachments

6. PARENT MONITORING
   └─ Parent views homework status
       ├─ Pending submissions
       ├─ Graded homework with marks
       └─ Teacher comments

7. HOMEWORK LOAD ANALYSIS (Principal)
   └─ Principal reviews homework load
       ├─ Number of assignments per class per week
       ├─ Subject-wise distribution
       └─ Flag over-loaded classes
```

### Approval Points
- Step 1: Teacher self-approves (no approval needed)
- Step 4: Teacher grades independently

### Notifications
- Homework published → Students, Parents
- Submission reminder (24h before due) → Students
- Overdue alert → Students, Parents
- Graded → Student, Parent

### Audit Trail
- Creation timestamp
- Submission timestamp
- Grading timestamp and marks
- Late submission flag

---

## Workflow 6: Exam Creation

### Actors
- **School Admin / Vice Principal** — Creates exam schedule
- **Teacher** — Sets question paper (if applicable)
- **Principal** — Approves exam schedule

### Steps

```
1. EXAM PLAN
   └─ School Admin / Vice Principal creates exam
       ├─ Exam name (e.g., Term 1, Mid-Term, Final)
       ├─ Select classes and sections
       ├─ Select subjects
       ├─ Set date range (start date, end date)
       ├─ Set time slots for each subject
       ├─ Set max marks per subject
       ├─ Set passing marks
       └─ Assign exam supervisors (teachers on duty)

2. APPROVAL
   └─ Principal approves exam schedule
       ├─ Review scheduling conflicts
       ├─ Review supervisor assignments
       └─ Approve or request changes

3. PUBLICATION
   └─ System publishes exam schedule
       ├─ Students view their exam timetable
       ├─ Teachers view their supervision duties
       └─ Notifications sent

4. QUESTION PAPER (if applicable)
   └─ Teacher uploads question paper
       ├─ Upload file
       ├─ Set submission deadline
       └─ School Admin reviews (security)

5. READINESS CHECK
   └─ School Admin verifies readiness
       ├─ All question papers uploaded
       ├─ All supervisors assigned
       ├─ All rooms allocated
       └─ Print answer sheets, admit cards
```

### Approval Points
- Step 2: Exam schedule approval (Principal)

### Notifications
- Exam schedule published → Students, Teachers, Parents
- Supervisor duty assigned → Teacher
- Exam tomorrow reminder → Students, Parents

### Audit Trail
- Exam creation log
- Approval log with timestamp
- Schedule change history

---

## Workflow 7: Marks Entry

### Actors
- **Teacher** — Enters marks
- **Principal** — Reviews before publication
- **Student** — Views own marks
- **Parent** — Views child's marks

### Steps

```
1. MARKS ENTRY
   └─ Teacher enters marks for each subject
       ├─ Select exam → class → subject
       ├─ Enter marks per student
       ├─ Option A: Manual entry (student by student)
       ├─ Option B: Bulk upload (CSV/Excel)
       ├─ Option C: Import from optical reader
       ├─ Add remarks (absent, grace, etc.)
       └─ Save as draft

2. MARKS VERIFICATION
   └─ Teacher reviews entered marks
       ├─ Check for data entry errors
       ├─ Verify against answer sheets
       ├─ Make corrections
       └─ Mark as "Ready for Review"

3. PRINCIPAL REVIEW
   └─ Principal reviews marks before publication
       ├─ View class-wise performance
       ├─ Check for anomalies (sudden drops, outliers)
       ├─ Review pass/fail ratios
       └─ Approve for publication

4. PUBLICATION
   └─ Principal publishes results
       ├─ Results visible to students and parents
       ├─ Generate report cards
       └─ Send notifications

5. POST-PUBLICATION
   └─ Students/Parents view results
       ├─ Subject-wise marks
       ├─ Total, percentage, grade
       ├─ Rank (if applicable)
       └─ Download report card

6. RE-EVALUATION (if applicable)
   └─ Student/Parent requests re-evaluation
       ├─ Submit request with fee (if applicable)
       ├─ Teacher re-checks answer sheet
       ├─ Update marks if needed
       └─ Notify student/parent of outcome
```

### Approval Points
- Step 3: Marks approval (Principal)
- Step 6: Re-evaluation (Teacher + Principal)

### Notifications
- Marks ready for review → Principal
- Results published → Student, Parent
- Re-evaluation outcome → Student, Parent

### Audit Trail
- Marks entry timestamp and user
- Correction history (before/after values)
- Publication timestamp
- Re-evaluation requests and outcomes

---

## Workflow 8: Result Publication

### Actors
- **Principal** — Sole authority to publish
- **Teacher** — Prepares results
- **Student** — Views published results
- **Parent** — Views published results

### Steps

```
1. RESULT PREPARATION
   └─ All marks entered and verified
       ├─ System calculates totals, percentages, grades
       ├─ Generate rank list (if applicable)
       ├─ Identify students needing remedial action
       └─ Generate report card PDFs

2. PRINCIPAL REVIEW
   └─ Principal reviews complete results
       ├─ Overall pass percentage
       ├─ Subject-wise performance
       ├─ Class-wise performance
       ├─ Compare with previous exams
       ├─ Flag anomalies
       └─ Approve or request revisions

3. PUBLICATION
   └─ Principal clicks "Publish Results"
       ├─ Results become visible to students/parents
       ├─ Report cards available for download
       ├─ SMS/Email notification sent
       └─ System logs publication event

4. REPORT CARD DISTRIBUTION
   └─ Report cards available:
       ├─ Download from parent/student portal (PDF)
       ├─ Print from school (Admin/Teacher)
       └─ Email to registered email
```

### Approval Points
- Step 2: Principal review and approval (MANDATORY — cannot be delegated)

### Notifications
- Results published → All students and parents (SMS, Email, In-app)
- Report card available → Student, Parent

### Audit Trail
- Publication timestamp and Principal user ID
- Pre-publish review comments
- Post-publish corrections (rare, logged separately)

---

## Workflow 9: Leave Application

### Actors
- **Teacher / Staff** — Applies for leave
- **HR** — Reviews and recommends
- **Principal** — Approves or rejects
- **Payroll Manager** — Receives approved leaves for payroll calculation

### Steps

```
1. APPLICATION
   └─ Employee applies for leave
       ├─ Select leave type (Sick, Casual, Earned, etc.)
       ├─ Enter start date, end date
       ├─ Enter reason
       ├─ Upload supporting document (medical cert, etc.)
       ├─ Check leave balance (auto-shown)
       └─ Submit

2. AUTO-CHECKS
   └─ System validates application
       ├─ Sufficient leave balance
       ├─ No overlapping leave
       ├─ Minimum notice period (if applicable)
       ├─ Blackout dates (exam period, etc.)
       └─ Flag issues if any

3. HR REVIEW
   └─ HR reviews and recommends
       ├─ Verify leave type appropriateness
       ├─ Check workload coverage (who will substitute)
       ├─ Add recommendation note
       └─ Forward to Principal OR Recommend Approval

4. PRINCIPAL APPROVAL
   └─ Principal approves or rejects
       ├─ Review application and HR recommendation
       ├─ Check overall staff availability
       ├─ Approve, reject (with reason), or modify (reduce days)
       └─ System notifies employee

5. LEAVE RECORDING
   └─ System records approved leave
       ├─ Deduct from leave balance
       ├─ Update attendance record
       ├─ Notify Payroll Manager (for salary calculation)
       └─ Add to employee leave history

6. SUBSTITUTION (if applicable)
   └─ Principal / HR arranges substitute teacher
       ├─ Assign substitute
       ├─ Notify substitute of dates and classes
       └─ Update timetable if needed
```

### Approval Points
- Step 3: HR recommendation (optional — can be skipped for direct reports)
- Step 4: Principal approval (MANDATORY)

### Notifications
- Application submitted → HR, Principal
- HR recommendation → Principal
- Approved / Rejected → Employee
- Substitute assigned → Substitute Teacher

### Audit Trail
- Application timestamp and user
- HR recommendation timestamp
- Approval/rejection with reason
- Leave balance deduction
- Substitution record

---

## Workflow 10: Leave Approval (Hierarchical)

### Actors
- **Teacher / Staff** — Applicant
- **HOD / Vice Principal** — First-level approval (if configured)
- **Principal** — Final approval
- **HR** — Processing

### Steps for Multi-Level Approval

```
1. APPLICANT submits leave
       ↓
2. HOD/VICE PRINCIPAL reviews
   ├─ Approve → Forward to Principal
   ├─ Reject → Notify Applicant (end)
   └─ Modify → Suggest changes → Applicant
       ↓
3. PRINCIPAL reviews
   ├─ Approve → Notify Applicant + HR
   ├─ Reject → Notify Applicant (end)
   └─ Modify → Suggest changes → Applicant
       ↓
4. HR processes approved leave
   ├─ Update attendance
   ├─ Notify Payroll Manager
   └─ Archive
```

### Escalation
- If HOD doesn't act within 24 hours → Auto-escalate to Principal
- If Principal doesn't act within 48 hours → Auto-escalate to School Admin

---

## Workflow 11: Payroll Processing

### Actors
- **HR** — Provides attendance and leave data
- **Payroll Manager** — Processes payroll
- **Accountant** — Reviews payroll costing
- **School Admin** — Approves payroll

### Steps

```
1. DATA COLLECTION (1st - 3rd of month)
   └─ System/HR provides inputs:
       ├─ Attendance summary (present days, absent days)
       ├─ Approved leave data
       ├─ New joiners and exits (pro-rated salary)
       ├─ Loan/advance deductions
       ├─ Ad-hoc additions (bonus, overtime)
       ├─ Tax declaration updates
       └─ Deduction changes (PF, insurance, etc.)

2. PAYROLL CALCULATION (4th - 5th)
   └─ Payroll Manager runs payroll calculation
       ├─ System calculates:
       │   ├─ Basic salary
       │   ├─ Allowances (HRA, DA, Transport, Medical, etc.)
       │   ├─ Gross pay
       │   ├─ Deductions (PF, Tax, Loan, etc.)
       │   └─ Net pay
       ├─ Review exceptions (missing data, anomalies)
       └─ Generate pre-run report

3. REVIEW (6th - 7th)
   └─ Payroll Manager reviews
       ├─ Compare with previous month
       ├─ Verify new joiner/exits amounts
       ├─ Check exception list
       └─ Resolve discrepancies

4. ACCOUNTANT REVIEW (7th - 8th)
   └─ Accountant reviews payroll costing
       ├─ Total payroll vs budget
       ├─ Department-wise breakdown
       └─ Flag budget overruns

5. SCHOOL ADMIN APPROVAL (8th - 9th)
   └─ School Admin approves payroll
       ├─ Review payroll summary
       ├─ Verify total amounts
       └─ Approve for processing

6. LOCK & DISBURSE (10th)
   └─ Payroll Manager locks payroll
       ├─ System prevents further changes
       ├─ Generate final payslips
       ├─ Generate bank transfer file
       ├─ Send payslips to employees
       └─ Archive payroll run

7. POST-PROCESSING
   └─ Employees receive payslips
       ├─ Download from portal
       ├─ Email notification
       └─ Print (if needed)
```

### Approval Points
- Step 4: Accountant review (advisory)
- Step 5: School Admin approval (MANDATORY)
- Step 6: Payroll lock (Payroll Manager — final)

### Notifications
- Payroll processing started → HR, Accountant, School Admin
- Payroll ready for approval → School Admin
- Payslip generated → All employees
- Payroll locked → Accountant, School Admin

### Audit Trail
- Full payroll calculation log
- Pre-approval changes with user ID
- Approval timestamps (Accountant, School Admin)
- Lock timestamp
- Payslip generation log
- Bank file generation log

---

## Workflow 12: Fee Collection

### Actors
- **Parent** — Makes payment
- **Accountant** — Records and reconciles
- **School Admin** — Approves concessions

### Steps

```
1. FEE STRUCTURE SETUP (Annual)
   └─ Accountant creates fee structure
       ├─ Define fee categories (Tuition, Transport, Library, etc.)
       ├─ Set amounts per class
       ├─ Set due dates / installment schedule
       ├─ Set late fee rules
       └─ Publish fee structure

2. STUDENT FEE ASSIGNMENT
   └─ Accountant assigns fee structure to students
       ├─ By class bulk assignment
       ├─ Individual adjustments (concessions)
       ├─ Transport fee based on route assignment
       └─ Generate fee ledger per student

3. PAYMENT
   └─ Parent makes payment
       ├─ Check fee due amount
       ├─ Select payment method:
       │   ├─ Cash (at school counter → Accountant records)
       │   ├─ Cheque → Accountant records, tracks clearance
       │   ├─ Bank Transfer → Accountant verifies
       │   ├─ Online Payment → Auto-reconciled
       │   └─ UPI / QR → Auto-reconciled
       ├─ Apply late fee if applicable
       ├─ Generate receipt
       └─ Update fee ledger

4. RECONCILIATION (End of Day)
   └─ Accountant reconciles daily collections
       ├─ Total cash count
       ├─ Cheque summary
       ├─ Online payment verification
       ├─ Match with system records
       └─ Generate daily collection report

5. DUE FOLLOW-UP
   └─ System sends fee reminders
       ├─ 7 days before due date
       ├─ On due date
       ├─ 7 days overdue
       ├─ 30 days overdue (escalate to Accountant)
       └─ 60 days overdue (escalate to Principal)

6. CONCESSION MANAGEMENT
   └─ School Admin approves concessions
       ├─ Parent applies for concession
       ├─ Accountant reviews
       ├─ School Admin approves/declines
       └─ Adjust fee ledger
```

### Approval Points
- Step 6: Concession approval (School Admin)

### Notifications
- Fee due reminder → Parent (SMS, Email, In-app)
- Payment confirmed → Parent (receipt)
- Late fee applied → Parent
- Overdue escalation → Accountant, Principal
- Concession approved → Parent

### Audit Trail
- Fee structure creation log
- Payment transaction log (amount, method, date, collector)
- Receipt sequence tracking
- Concession approval with reason
- Late fee calculation log

---

## Workflow 13: Transport Assignment

### Actors
- **School Admin** — Manages routes and assignments
- **Driver** — Assigned to route
- **Parent** — Views route and tracking
- **Student** — Uses transport

### Steps

```
1. ROUTE SETUP
   └─ School Admin creates transport route
       ├─ Route name (e.g., "North Zone - Bus 1")
       ├─ Start point, end point
       ├─ Vehicle assignment
       ├─ Driver assignment
       └─ Total capacity

2. STOP SETUP
   └─ School Admin adds stops to route
       ├─ Stop name / location
       ├─ Pickup time (morning)
       ├─ Drop time (evening)
       ├─ Stop order
       └─ Distance from school

3. STUDENT ASSIGNMENT
   └─ School Admin / Accountant assigns student to route
       ├─ Select student
       ├─ Select route and stop
       ├─ Calculate transport fee
       └─ Add to fee structure

4. PARENT NOTIFICATION
   └─ Parent receives transport details
       ├─ Route, stop, timings
       ├─ Driver name and contact
       ├─ Vehicle number
       └─ Live tracking link (if available)

5. DAILY OPERATION
   └─ Driver operates route
       ├─ Check-in (start route)
       ├─ Pickup students at stops
       ├─ Mark attendance (optional)
       ├─ Drop at school
       ├─ Return route (evening)
       └─ Check-out (end route)

6. LIVE TRACKING
   └─ Parent views bus location (during commute)
       ├─ Real-time map view
       ├─ Estimated arrival at stop
       └─ Delay notifications
```

### Approval Points
- Step 3: Student assignment (Admin/Accountant)
- Route creation/change (School Admin)

### Notifications
- Transport assigned → Parent
- Route changed → Affected parents
- Bus delayed → Affected parents
- Driver assigned/changed → Parents on route

### Audit Trail
- Route creation/modification history
- Student assignment history
- Daily trip log (check-in/check-out timestamps)

---

## Workflow 14: Library Issue & Return

### Actors
- **Librarian** — Manages issues and returns
- **Student** — Borrows and returns books
- **Teacher** — Borrows reference books
- **Parent** — Views child's books

### Steps

```
ISSUE:
1. STUDENT SELECTION
   └─ Student brings book to counter
       ├─ Librarian searches catalog
       └─ Select book copy

2. ISSUE PROCESSING
   └─ Librarian issues book
       ├─ Select member (student/teacher)
       ├─ Scan book barcode / select copy
       ├─ Set issue date
       ├─ Set due date (auto-calculated: 14 days for students, 30 for teachers)
       └─ Confirm issue

3. RECORD
   └─ System updates:
       ├─ Book status → Issued
       ├─ Member's issue list updated
       └─ Due date recorded

RETURN:
4. RETURN PROCESSING
   └─ Student returns book to library
       ├─ Librarian scans book barcode
       ├─ Check due date
       ├─ Calculate fine (if overdue)
       └─ Process return

5. FINE COLLECTION (if overdue)
   └─ Librarian collects fine
       ├─ System calculates fine amount
       ├─ Collect payment
       ├─ Issue fine receipt
       └─ Update book status → Available

6. NOTIFICATION
   └─ System sends reminders:
       ├─ 3 days before due date
       ├─ On due date
       ├─ 7 days overdue
       ├─ 14 days overdue (escalate to class teacher)
       └─ 30 days overdue (escalate to Principal)

FINE WAIVER:
7. FINE WAIVER
   └─ Librarian (up to limit) / Principal (above limit)
       ├─ Review reason for late return
       ├─ Approve waiver or partial waiver
       └─ Update fine record
```

### Approval Points
- Step 7: Fine waiver (Librarian ≤ Rs.50, Principal > Rs.50)

### Notifications
- Book issued → Student
- Due date reminder → Student, Parent
- Overdue → Student, Parent (escalating frequency)
- Fine collected → Student, Parent
- Fine waived → Student, Parent

### Audit Trail
- Issue/return timestamps
- Fine calculation and collection
- Fine waiver with reason and approver
- Lost book replacement record

---

## Workflow 15: Document Verification

### Actors
- **Student / Parent** — Uploads documents
- **Teacher** — Reviews and flags
- **Vice Principal / Principal** — Verifies documents

### Steps

```
1. DOCUMENT UPLOAD
   └─ Student/Parent uploads document
       ├─ Document type (Birth Certificate, Transfer Cert, Marksheet, etc.)
       ├─ Upload file (PDF/Image)
       ├─ Add notes (optional)
       └─ Submit

   OR Teacher uploads on behalf
       └─ Document tagged to student record

2. INITIAL REVIEW
   └─ Teacher / Class Teacher reviews document
       ├─ Check readability
       ├─ Check completeness
       ├─ Flag if unclear or incomplete
       └─ Recommend verification

3. VERIFICATION
   └─ Vice Principal / Principal verifies document
       ├─ Review original vs uploaded copy
       ├─ Verify authenticity
       ├─ Mark as Verified / Rejected
       ├─ Add verification notes
       └─ Digital signature / approval

4. STATUS UPDATE
   └─ System updates document status
       ├─ Verified → Available for download
       ├─ Rejected → Notify uploader with reason
       └─ Flagged for re-upload → Notify uploader

5. NOTIFICATION
   └─ Uploader notified of verification result
       ├─ Document verified
       ├─ Document rejected (with reason)
       └─ Action needed (re-upload)
```

### Approval Points
- Step 3: Verification (Vice Principal / Principal)

### Notifications
- Document uploaded → Teacher, Vice Principal
- Document verified → Student, Parent
- Document rejected → Student, Parent (with reason)
- Re-upload requested → Student, Parent

### Audit Trail
- Upload timestamp and user
- Review comments
- Verification timestamp and verifier ID
- Rejection reason
- Re-upload history

---

## Workflow 16: Parent Communication

### Actors
- **Teacher** — Initiates communication
- **Parent** — Receives and responds
- **Principal** — Escalated communication
- **HR/School Admin** — Bulk communication

### Channels
- In-app messaging
- Email
- SMS
- Push notification (mobile app)
- Printed circular

### Steps — Standard Communication

```
1. INITIATION
   └─ Sender composes message
       ├─ Select recipient(s):
       │   ├─ Individual parent
       │   ├─ Multiple parents (by class, section)
       │   ├─ All parents (School Admin only)
       │   └─ Targeted group (transport parents, defaulters, etc.)
       ├─ Enter subject and message
       ├─ Attach files (optional)
       └─ Choose channel(s)

2. APPROVAL (if applicable)
   └─ Bulk/Sensitive messages require approval
       ├─ Class communication → Teacher self
       ├─ Section communication → Teacher self
       ├─ Class-wide announcement → Principal approval
       └─ School-wide announcement → School Admin approval

3. DELIVERY
   └─ System delivers via selected channels
       ├─ In-app (immediate)
       ├─ Email (queued)
       ├─ SMS (queued, batch)
       └─ Push notification (real-time)

4. READ RECEIPT
   └─ Sender can see read status
       ├─ Read / Unread
       ├─ Delivery status
       └─ Track who has read

5. RESPONSE
   └─ Parent can respond
       ├─ Reply to teacher
       ├─ Request meeting
       └─ Attach documents

6. ESCALATION
   └─ If issue unresolved:
       ├─ Parent request → Teacher → HOD → Principal
       ├─ Complaint → Principal → School Admin
       └─ System tracks escalation status
```

### Approval Points
- Step 2: Bulk communication approval (Principal or School Admin)

### Notifications
- New message → Parent (all channels)
- Reply received → Teacher
- Meeting request → Teacher
- Escalation → Next level

### Audit Trail
- All communication logged
- Read receipts
- Escalation history
- Meeting request history

---

## Summary: Workflow Characteristics

| Workflow | # Steps | Approval Points | Notifications | Actors Involved |
|----------|---------|-----------------|---------------|-----------------|
| Student Admission | 9 | 2 | 5 | 5 |
| Student Promotion | 4 | 2 | 2 | 3 |
| Teacher Attendance | 4 | 2 | 3 | 3 |
| Student Attendance | 5 | 1 | 4 | 4 |
| Homework | 7 | 0 | 4 | 4 |
| Exam Creation | 5 | 1 | 3 | 4 |
| Marks Entry | 6 | 2 | 3 | 4 |
| Result Publication | 3 | 1 | 2 | 4 |
| Leave Application | 6 | 2 | 3 | 4 |
| Leave Approval (Multi-level) | 4 | 2 | 3 | 4 |
| Payroll Processing | 7 | 3 | 4 | 4 |
| Fee Collection | 6 | 1 | 5 | 3 |
| Transport Assignment | 6 | 1 | 3 | 4 |
| Library Issue & Return | 7 | 1 | 5 | 4 |
| Document Verification | 5 | 1 | 3 | 4 |
| Parent Communication | 6 | 1 | 3 | 4 |
