# Business Workflows

Version: 1.0.0

Revision date: 2026-07-08

## 1. Admission

- Actor: School admin or receptionist
- Step: Create student profile, assign class/section, link guardians
- Notification: Notification record can be created for follow-up
- Business Rule: Student data must belong to the active school context
- Exception: Missing active school context blocks downstream workflows

## 2. Attendance

- Actor: Teacher or admin
- Step: Record attendance for the relevant students or class section
- Approval: Depends on configured permissions
- Notification: Parent or administrator notifications can be triggered as needed
- Business Rule: Attendance records are school-scoped
- Exception: Missing records or permissions block updates

## 3. Homework

- Actor: Teacher
- Step: Create assignment for class/section and subject
- Approval: None in the documented implementation path
- Notification: Operational notifications may be used
- Business Rule: Homework must be assigned within the active school context
- Exception: Invalid class/subject mapping blocks creation

## 4. Exams and Marks

- Actor: Teacher or admin
- Step: Create exam, schedule, enter marks, publish results
- Approval: Publish action is permission-protected
- Notification: Notifications can be used for result availability
- Business Rule: Exam data is linked to the current school context
- Exception: Missing schedule or mark data can block publication

## 5. Fees

- Actor: Accountant or admin
- Step: Configure fee structure, assign fees, collect payments, generate receipts
- Approval: Fees collect and update actions require permissions
- Notification: Receipts and communication can be tracked through notifications
- Business Rule: Collections depend on the student fee assignment
- Exception: Missing assignment prevents collection

## 6. Payroll

- Actor: Payroll manager or admin
- Step: Generate payroll runs and payslips
- Approval: Payroll actions are permission-protected
- Notification: Notifications may be used for payroll completion
- Business Rule: Payroll depends on employee and school data
- Exception: Missing employee details block generation

## 7. Library

- Actor: Librarian or admin
- Step: Manage books, authors, categories, and issue records
- Approval: Depends on permissions
- Notification: Library and school notifications may be used
- Business Rule: Library operations follow the current school context
- Exception: Missing borrower or book records block issue flows

## 8. Transport

- Actor: Admin or transport manager
- Step: Manage vehicles, drivers, routes, assignments
- Approval: Related actions require permissions
- Notification: Operational notifications may be triggered
- Business Rule: Assignment records are school-scoped
- Exception: Invalid route or student assignments block completion
