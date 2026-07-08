# SCHOOL ERP — Business Blueprint

**Document:** SCHOOL_BUSINESS_BLUEPRINT.md
**Date:** 2026-07-07
**Author:** Principal ERP Solution Architect
**Status:** Draft for Review

---

## 1. Executive Summary

This blueprint defines the complete business rules, workflows, and access governance for a multi-tenant School ERP system. The system serves K-12 educational institutions with 12 distinct user roles across 20+ operational modules.

The design prioritizes:
- **Operational efficiency** — reduce repetitive tasks through automation
- **Data security** — role-appropriate data visibility at all times
- **Workflow integrity** — clear approval chains and audit trails
- **Role clarity** — each actor knows their boundaries and responsibilities

---

## 2. School Organizational Structure

```
                         ┌──────────────────┐
                         │   Management     │
                         │ (Super Admin /   │
                         │  School Admin)   │
                         └────────┬─────────┘
                                  │
                    ┌─────────────┼──────────────┐
                    │             │              │
         ┌──────────┴───┐  ┌─────┴──────┐  ┌────┴──────────┐
         │  Principal   │  │    HR      │  │  Accountant   │
         │  (Academic   │  │  (Teacher  │  │  (Finance &   │
         │   Head)      │  │   Mgmt)    │  │   Fees)       │
         └──────┬───────┘  └────────────┘  └───────┬────────┘
                │                                   │
         ┌──────┴──────────┐               ┌────────┴─────────┐
         │ Vice Principal  │               │  Payroll Manager │
         │ (if applicable) │               │  (Salaries &     │
         └──────┬──────────┘               │   Benefits)      │
                │                          └──────────────────┘
     ┌──────────┼──────────┐
     │          │          │
  ┌──┴───┐  ┌───┴────┐  ┌─┴────┐
  │Teacher│  │Librarian│  │Staff │
  │       │  │         │  │      │
  └───┬───┘  └─────────┘  └──────┘
      │
  ┌───┴────────┐
  │  Student   │
  │            │
  └───┬────────┘
      │
  ┌───┴────────┐
  │   Parent   │
  │            │
  └────────────┘
```

### 2.1 Role Hierarchy & Reporting Lines

| Role | Reports To | Manages | Peer Roles |
|------|-----------|---------|------------|
| Super Admin | — (System Owner) | All roles across all schools | — |
| School Admin | Super Admin | All roles within one school | — |
| Principal | School Admin | Teachers, Staff, Vice Principal | HR, Accountant |
| HR | Principal | — | Principal, Accountant |
| Accountant | School Admin / Principal | — | HR, Payroll Manager |
| Payroll Manager | Accountant / School Admin | — | Accountant |
| Teacher | Principal / Vice Principal | Students (academic) | Librarian, Staff |
| Librarian | Principal | — | Teacher |
| Receptionist | Principal / Admin Staff | — | Staff |
| Staff | Principal / Admin | — | Receptionist |
| Student | Teacher (academic) | — | — |
| Parent | — | — | Teacher |

---

## 3. Module Ownership Model

Each module has a **Business Owner** (who defines rules), an **Operational Owner** (who manages daily work), and **Approvers** (who authorize critical actions).

| Module | Business Owner | Operational Owner | Approver |
|--------|---------------|-------------------|----------|
| Dashboard | Principal | All roles (self) | — |
| Students | Principal | Teacher / Receptionist | Principal |
| Teachers | HR | HR | Principal |
| Parents | Principal | Teacher / Receptionist | — |
| Attendance | Vice Principal | Teacher | Principal |
| Homework | Principal | Teacher | Principal / HOD |
| Timetable | Vice Principal | Teacher | Principal |
| Exams | Vice Principal | Teacher | Principal |
| Results | Vice Principal | Teacher | Principal |
| Academics | Vice Principal | Teacher / Admin | Principal |
| Calendar | Principal | Admin | Principal |
| Leave | HR | Staff / Teacher | Principal / HOD |
| Payroll | Accountant | Payroll Manager | School Admin |
| Fees | Accountant | Accountant | School Admin |
| Library | Principal | Librarian | Principal |
| Transport | Principal | Admin | Principal |
| Reports | Principal | All roles (self) | — |
| Notifications | Principal | HR / Admin | — |
| AI Workspace | Principal | All roles (self) | — |
| Documents | Vice Principal | Teacher / Admin | Vice Principal |
| Settings | School Admin | School Admin | — |
| Users | School Admin | HR / Admin | School Admin |
| Roles & Permissions | Super Admin | School Admin | Super Admin |

---

## 4. Core Business Rules

### 4.1 Data Visibility Rules

| Data Category | School-wide | Assigned Only | Self Only | Summary Only |
|--------------|-------------|--------------|-----------|--------------|
| Student Profiles | School Admin, Principal, Receptionist | Teacher (own classes) | — | Parent (own children) |
| Teacher Profiles | School Admin, Principal, HR | — | Teacher (self) | — |
| Attendance Records | School Admin, Principal | Teacher (own classes) | Student (self) | Parent (own children) |
| Exam Marks | School Admin, Principal | Teacher (own subjects) | Student (self) | Parent (own children) |
| Fee Records | School Admin, Accountant | — | — | Parent (own children), Student (self) |
| Payroll Data | School Admin | Payroll Manager | Teacher (own payslip) | — |
| Leave Records | School Admin, Principal, HR | — | Teacher/Staff (self) | — |
| Homework | School Admin, Principal | Teacher (own classes) | Student (enrolled) | Parent (own children) |
| Library Records | School Admin, Librarian | — | Student (self) | Parent (own children) |
| Transport Data | School Admin, Transport Admin | Driver (own route) | Student (assigned) | Parent (own children) |

### 4.2 Approval Rules

| Action | Requires Approval From | Max Escalation Time |
|--------|----------------------|-------------------:|
| Leave Application (Teacher) | Principal / HOD | 48 hours |
| Leave Application (Staff) | Principal | 48 hours |
| Fee Waiver | School Admin | 24 hours |
| Exam Result Publication | Principal | 24 hours |
| Document Verification | Vice Principal | 72 hours |
| Payroll Processing | School Admin | 48 hours |
| Library Fine Waiver | Librarian / Principal | 24 hours |
| Student Promotion | Principal | End of academic year |
| New User Creation | School Admin / HR | 24 hours |
| Role Assignment | School Admin | — |
| Homework Extension | Teacher (self) | — |
| Timetable Change | Vice Principal | 48 hours |

### 4.3 Audit Requirements

Every business-critical action MUST be logged with:
- **Who** performed the action
- **What** action was taken
- **When** it occurred (timestamp)
- **Where** (IP address / device)
- **Previous value** (for updates)
- **New value** (for updates)
- **Approval reference** (if applicable)

Modules requiring full audit trails:
- Fees (all financial transactions)
- Payroll (salary processing)
- Leave (approval chain)
- Exams (result changes)
- Student Records (admission, promotion, deletion)
- RBAC (role/permission changes)
- Document Verification (status changes)

### 4.4 Academic Year Scope

All academic data (classes, attendance, exams, homework, fees) operates within the **current academic year** context. Users should only see data for the active session unless explicitly querying historical data.

---

## 5. Notification Architecture

### 5.1 Notification Triggers

| Trigger | Recipients | Channel | Priority |
|---------|-----------|---------|----------|
| Leave Application | Principal, HR | In-app | High |
| Leave Approved | Applicant | In-app, Email | High |
| Leave Rejected | Applicant | In-app, Email | High |
| New Homework | Students (enrolled), Parents | In-app | Medium |
| Exam Results Published | Students, Parents | In-app, Email | High |
| Fee Due Reminder | Parents, Students | In-app, Email, SMS | High |
| Fee Payment Confirmed | Parent | In-app | Medium |
| Attendance Alert (low) | Teacher, Parents | In-app, SMS | High |
| Document Verified | Student, Parent | In-app | Low |
| Payroll Generated | All employees | In-app, Email | High |
| Book Due Reminder | Student | In-app | Medium |
| Calendar Event | Targeted roles | In-app | Low |
| Transport Delay | Parents (affected) | In-app, SMS | High |
| New User Created | User (self) | Email | Medium |
| Password Reset | User (self) | Email | High |

### 5.2 Notification Preference Per Role

| Role | In-app | Email | SMS | Push |
|------|--------|-------|-----|------|
| Super Admin | All | Critical | — | Critical |
| School Admin | All | All | Critical | Critical |
| Principal | All | All | Critical | Critical |
| Teacher | All | All | Attendance | Attendance |
| HR | All | All | — | Leave |
| Accountant | All | All | Fee | Fee |
| Payroll Manager | All | Payroll | — | Payroll |
| Librarian | All | Book due | — | Book due |
| Receptionist | All | All | — | — |
| Staff | All | All | Attendance | Attendance |
| Parent | All | All | Fee, Attendance | Fee, Attendance |
| Student | All | Results | — | Homework |

---

## 6. Academic Structure

### 6.1 Standard School Hierarchy

```
School
 ├── Academic Year (e.g., 2025-2026)
 │    ├── Class (e.g., Class 10)
 │    │    ├── Section A (e.g., 10-A)
 │    │    │    ├── Subject: Mathematics → Teacher: Mr. Sharma
 │    │    │    ├── Subject: Science → Teacher: Ms. Patel
 │    │    │    └── Student: Roll 1, Roll 2, ... Roll 40
 │    │    ├── Section B
 │    │    └── ...
 │    ├── Class 9
 │    └── ...
 └── Academic Calendar (Terms, Holidays, Events)
```

### 6.2 Session Context Rules

- All data queries default to the active academic year
- Students are assigned to a class-section for the academic year (`StudentSession`)
- Teachers are assigned to class-sections and subjects per academic year
- Attendance is recorded per session per student per day
- Fees are structured per class per academic year

---

## 7. Financial Year Rules

- Fee structures are defined per academic year
- Payroll runs on a calendar month basis
- Financial reports use the academic year as the default fiscal period
- Receipt numbering resets per academic year (`FeeReceiptSequence`)
- Fee concessions/waivers require School Admin approval

---

## 8. Compliance & Data Retention

| Data Type | Retention Period | Archival Action |
|-----------|-----------------|-----------------|
| Student Records | 5 years after leaving | Archive to cold storage |
| Teacher Records | 5 years after leaving | Archive to cold storage |
| Attendance Data | Current + 3 years | Aggregate after 3 years |
| Fee Records | Current + 7 years | Archive to cold storage |
| Payroll Records | Current + 7 years | Archive to cold storage |
| Exam Results | Permanent | Keep in system |
| Leave Records | Current + 3 years | Aggregate after 3 years |
| Audit Logs | Current + 5 years | Archive to cold storage |
| Communication | Current + 2 years | Delete after 2 years |

---

## 9. Key Design Principles

1. **Principle of Least Privilege** — Every role gets the minimum access required to perform its duties
2. **Separation of Duties** — No single role can both create and approve the same transaction
3. **Data Isolation** — Teachers see only their assigned classes; students see only themselves
4. **Self-Service First** — Employees manage their own leave, students view their own data
5. **Audit Everything** — Every financial and academic change is traceable
6. **Notification by Default** — Critical events always generate notifications
7. **Role Before Permission** — A role's business profile defines its permissions, not the reverse

---

## 10. Glossary

| Term | Definition |
|------|-----------|
| Academic Year | The school year (e.g., April 2025 – March 2026) |
| Class | Year/grade level (e.g., Class 10) |
| Section | Division within a class (e.g., Section A, B) |
| Subject | Course of study (e.g., Mathematics, Science) |
| Session | A student's enrollment in a specific class-section for an academic year |
| Fee Structure | Set of fee categories and amounts for a class |
| Payroll Run | Monthly batch that calculates and processes salaries |
| Leave Quota | Allocated leave days per employee type per year |
| Transport Route | Bus route with stops and assigned students |
| Book Issue | Library checkout transaction |
| Document Verification | Approval workflow for student documents |
