# Teacher Workflow Report

## 1. Login -> Dashboard

### Steps
1. Navigate to login page (`/login`)
2. Enter credentials (email/employee ID + password)
3. System authenticates via `auth` middleware
4. School context is resolved via `school` middleware
5. `DashboardFactory` detects `Teacher` role and instantiates `TeacherDashboardBuilder`
6. `TeacherDashboardBuilder::build()` renders teacher-specific dashboard view

### Permissions Needed
- `dashboard.view` (auto-assigned to Teacher role)

### Business Rules
- Only active teachers with `status = 'active'` can log in
- Teacher must be associated with a school
- Dashboard shows only teacher-relevant data (no Finance, no Transport, no Library)

---

## 2. Taking Attendance

### Steps
1. Click "Record Attendance" quick action or navigate to Attendance
2. System pre-filters class sections assigned to the teacher
3. Select date (defaults to today)
4. Mark students as Present/Absent/Late
5. Submit attendance records

### Permissions Needed
- `attendance.view` to access the page
- `attendance.create` to submit records

### Business Rules
- Teacher can only mark attendance for their assigned class sections
- Attendance cannot be backdated beyond 7 days (configurable)
- Cannot mark attendance for future dates
- At least one student must be marked before submission

---

## 3. Managing Homework

### Steps
1. Click "Manage Homework" quick action or navigate to Homework
2. View list of homework filtered by teacher's class sections
3. Create new homework: select class section, subject, enter title/description, set due date, attach file
4. Edit or delete existing homework

### Permissions Needed
- `homework.view` to access the page
- `homework.create` to create
- `homework.update` to edit
- `homework.delete` to remove

### Business Rules
- Homework is scoped to teacher's class sections (`created_by` = teacher's user ID)
- Due date must be a future date
- Attachment is optional, max 10MB (PDF, DOC, DOCX, images)

---

## 4. Entering Marks

### Steps
1. Navigate to Exams section
2. Select an exam scheduled for their class section
3. Enter marks for each student
4. Save or publish results

### Permissions Needed
- `exams.view` to see exams
- `exams.create` to enter marks
- `exams.publish` to publish results

### Business Rules
- Teacher can only enter marks for exams associated with their class sections
- Marks must be between 0 and maximum marks defined for the exam
- Published results are visible to students and parents
- Cannot edit marks after publishing without admin override

---

## 5. Applying for Leave

### Steps
1. Click "Apply Leave" quick action or navigate to Leave
2. Select leave type (Sick Leave, Casual Leave, etc.)
3. Enter from/to dates, reason, optionally attach supporting document
4. Submit for approval
5. View leave balance on dashboard widget

### Permissions Needed
- `leave_management.view` to access leave section
- `leave_requests.create` to apply

### Business Rules
- Leave request is automatically linked to the teacher's user ID
- Teacher can view only their own leave requests
- Approval requires Principal or Admin action
- Cannot apply for dates in the past
- Maximum consecutive days configurable per leave type

---

## 6. Viewing Payslips

### Steps
1. Navigate to Payroll section (if permitted)
2. View personal payslips only
3. Download payslip PDF

### Permissions Needed
- `payroll.view_own` (self-service permission)
- Teachers do NOT have `payroll.view` (which allows viewing all employees)

### Business Rules
- Teacher can only view their own payslips
- Cannot view payroll runs, locked runs, or other employees' salary data
- Payslip visibility is read-only

---

## 7. Using Ask ERP

### Steps
1. Click "Ask ERP" in the sidebar
2. Modal opens with a text input
3. Type a question (e.g., "How many students are absent today?")
4. View AI-generated response with confidence score

### Permissions Needed
- No special permission (available to all authenticated users)

### Business Rules
- Teachers can ONLY ask questions about: attendance, students, homework, exams, notifications
- Questions about fees, transport, library, payroll are BLOCKED with message: "Teachers can only ask questions about their classes, students, attendance, homework, and exams."
- All responses are scoped to the teacher's assigned class sections via `scopeToTeacherData()`
- Maximum 500 characters per question
- Confirmation required for destructive actions

---

## 8. Viewing Timetable

### Steps
1. Click "Timetable" in the sidebar or "View Timetable" quick action
2. View today's schedule (periods, subjects, rooms)
3. Filter by day of week if needed

### Permissions Needed
- `timetable.view`

### Business Rules
- Timetable is scoped to the teacher's assigned class sections
- Only active timetable slots are shown
- Today's schedule widget on dashboard shows teacher's current day's classes

---

## 9. Managing Documents

### Steps
1. Navigate to Student Documents
2. View documents of students in their class sections
3. Upload new documents
4. Download existing documents

### Permissions Needed
- `student_documents.view` to view
- `student_documents.create` to upload

### Business Rules
- Teacher can only view/upload documents for students in their assigned class sections
- Document types supported: ID proof, marksheets, transfer certificates, etc.
- Max file size: 5MB per document
