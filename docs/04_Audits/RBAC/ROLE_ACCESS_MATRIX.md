# ROLE ACCESS MATRIX

**Document:** ROLE_ACCESS_MATRIX.md
**Date:** 2026-07-07

---

## Legend

| Symbol | Meaning |
|--------|---------|
| SA | Super Admin |
| AD | School Admin |
| PR | Principal |
| TCH | Teacher |
| HR | Human Resources |
| ACC | Accountant |
| PM | Payroll Manager |
| LIB | Librarian |
| REC | Receptionist |
| STF | Staff |
| PAR | Parent |
| STU | Student |
| C | Create |
| R | Read (View) |
| U | Update |
| D | Delete |
| Ap | Approve |
| Ex | Export |
| Pr | Print |
| O | Own Records Only |
| As | Assigned Records Only |
| Sw | School-wide |
| — | No Access |

---

## 1. Dashboard

| Capability | SA | AD | PR | TCH | HR | ACC | PM | LIB | REC | STF | PAR | STU |
|-----------|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| View Dashboard | Sw | Sw | Sw | O | O | O | O | O | O | O | O | O |

---

## 2. Students

| Capability | SA | AD | PR | TCH | HR | ACC | PM | LIB | REC | STF | PAR | STU |
|-----------|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| View List | Sw | Sw | Sw | As | — | — | — | — | Sw | — | O | O |
| View Profile | Sw | Sw | Sw | As | — | — | — | — | Sw | — | O | O |
| Create | Sw | Sw | Sw | — | — | — | — | — | C | — | — | — |
| Update | Sw | Sw | Sw | As* | — | — | — | — | — | — | — | — |
| Delete | Sw | Sw | Ap | — | — | — | — | — | — | — | — | — |
| Export | Sw | Sw | Sw | As | — | — | — | — | — | — | — | — |
| Print Profile | Sw | Sw | Sw | As | — | — | — | — | Sw | — | O | O |
| Promote | Sw | Sw | Ap | — | — | — | — | — | — | — | — | — |

*As = Teacher can update assigned students' academic info only (not personal/contact details)
O = Own children only (Parent) or self (Student)
C = Receptionist creates inquiries/lead records only (not full admission)

---

## 3. Teachers

| Capability | SA | AD | PR | TCH | HR | ACC | PM | LIB | REC | STF | PAR | STU |
|-----------|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| View List | Sw | Sw | Sw | — | Sw | — | — | — | — | — | — | — |
| View Profile | Sw | Sw | Sw | O | Sw | — | — | — | — | — | — | — |
| Create | Sw | Sw | — | — | C | — | — | — | — | — | — | — |
| Update | Sw | Sw | — | O | U | — | — | — | — | — | — | — |
| Delete | Sw | Sw | — | — | — | — | — | — | — | — | — | — |
| Export | Sw | Sw | Sw | — | Sw | — | — | — | — | — | — | — |
| Print | Sw | Sw | Sw | — | Sw | — | — | — | — | — | — | — |
| Assign Classes | Sw | Sw | Ap | — | — | — | — | — | — | — | — | — |

---

## 4. Parents

| Capability | SA | AD | PR | TCH | HR | ACC | PM | LIB | REC | STF | PAR | STU |
|-----------|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| View List | Sw | Sw | Sw | As | — | — | — | — | Sw | — | O | — |
| View Profile | Sw | Sw | Sw | As | — | — | — | — | Sw | — | O | — |
| Create | Sw | Sw | Sw | — | — | — | — | — | C | — | — | — |
| Update | Sw | Sw | Sw | — | — | — | — | — | — | — | O | — |
| Delete | Sw | Sw | — | — | — | — | — | — | — | — | — | — |
| Export | Sw | Sw | Sw | — | — | — | — | — | — | — | — | — |

---

## 5. Academics (Classes, Sections, Subjects)

| Capability | SA | AD | PR | TCH | HR | ACC | PM | LIB | REC | STF | PAR | STU |
|-----------|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| View | Sw | Sw | Sw | Sw | — | — | — | — | — | — | — | — |
| Create | Sw | Sw | Ap | — | — | — | — | — | — | — | — | — |
| Update | Sw | Sw | Ap | — | — | — | — | — | — | — | — | — |
| Delete | Sw | Sw | — | — | — | — | — | — | — | — | — | — |

---

## 6. Attendance (Student)

| Capability | SA | AD | PR | TCH | HR | ACC | PM | LIB | REC | STF | PAR | STU |
|-----------|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| View | Sw | Sw | Sw | As | — | — | — | — | — | — | O | O |
| Mark | — | — | — | As | — | — | — | — | — | — | — | — |
| Bulk Mark | — | — | — | As | — | — | — | — | — | — | — | — |
| Update | Sw | Sw | Sw | As | — | — | — | — | — | — | — | — |
| Delete | Sw | Sw | — | — | — | — | — | — | — | — | — | — |
| Export | Sw | Sw | Sw | As | — | — | — | — | — | — | O | O |
| Reports | Sw | Sw | Sw | As | — | — | — | — | — | — | O | O |

---

## 7. Attendance (Teacher)

| Capability | SA | AD | PR | TCH | HR | ACC | PM | LIB | REC | STF | PAR | STU |
|-----------|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| View | Sw | Sw | Sw | O | Sw | — | — | — | — | O | — | — |
| Mark | — | — | — | — | C | — | — | — | — | — | — | — |
| Update | Sw | Sw | Sw | — | U | — | — | — | — | — | — | — |
| Delete | Sw | Sw | — | — | — | — | — | — | — | — | — | — |
| Export | Sw | Sw | Sw | O | Sw | — | — | — | — | O | — | — |

---

## 8. Homework

| Capability | SA | AD | PR | TCH | HR | ACC | PM | LIB | REC | STF | PAR | STU |
|-----------|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| View | Sw | Sw | Sw | As | — | — | — | — | — | — | O | O |
| Create | Sw | Sw | — | As | — | — | — | — | — | — | — | — |
| Update | Sw | Sw | As* | As | — | — | — | — | — | — | — | — |
| Delete | Sw | Sw | Sw | As | — | — | — | — | — | — | — | — |
| Export | Sw | Sw | Sw | As | — | — | — | — | — | — | O | O |
| Print | Sw | Sw | Sw | As | — | — | — | — | — | — | O | O |

*As (Principal) = Can update only to review/return for revision, not modify content
O (Parent) = Own children only
O (Student) = Self only

---

## 9. Timetable

| Capability | SA | AD | PR | TCH | HR | ACC | PM | LIB | REC | STF | PAR | STU |
|-----------|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| View | Sw | Sw | Sw | As | — | — | — | — | — | — | O | O |
| Create | Sw | Sw | Ap | — | — | — | — | — | — | — | — | — |
| Update | Sw | Sw | Ap | — | — | — | — | — | — | — | — | — |
| Delete | Sw | Sw | — | — | — | — | — | — | — | — | — | — |
| Print | Sw | Sw | Sw | As | — | — | — | — | — | — | O | O |

---

## 10. Exams

| Capability | SA | AD | PR | TCH | HR | ACC | PM | LIB | REC | STF | PAR | STU |
|-----------|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| View Schedule | Sw | Sw | Sw | As | — | — | — | — | — | — | O | O |
| Create | Sw | Sw | Ap | — | — | — | — | — | — | — | — | — |
| Update | Sw | Sw | Ap | — | — | — | — | — | — | — | — | — |
| Delete | Sw | Sw | — | — | — | — | — | — | — | — | — | — |
| Enter Marks | — | — | — | As | — | — | — | — | — | — | — | — |
| View Results | Sw | Sw | Sw | As | — | — | — | — | — | — | O | O |
| Publish | — | — | Ap | — | — | — | — | — | — | — | — | — |
| Export | Sw | Sw | Sw | As | — | — | — | — | — | — | O | O |
| Print Report Card | Sw | Sw | Sw | As | — | — | — | — | — | — | O | O |

---

## 11. Fees

| Capability | SA | AD | PR | TCH | HR | ACC | PM | LIB | REC | STF | PAR | STU |
|-----------|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| View Structure | Sw | Sw | Sw | — | — | Sw | — | — | — | — | O | O |
| Create Structure | Sw | Sw | — | — | — | C | — | — | — | — | — | — |
| Update Structure | Sw | Sw | — | — | — | U | — | — | — | — | — | — |
| Delete Structure | Sw | Sw | — | — | — | — | — | — | — | — | — | — |
| Collect Payment | — | — | — | — | — | C | — | — | — | — | — | — |
| View Transactions | Sw | Sw | — | — | — | Sw | — | — | — | — | O | O |
| Generate Receipt | Sw | Sw | — | — | — | C | — | — | — | — | — | — |
| Cancel Receipt | Sw | Ap | — | — | — | — | — | — | — | — | — | — |
| Approve Concession | Sw | Ap | — | — | — | — | — | — | — | — | — | — |
| Export Report | Sw | Sw | Sw | — | — | Sw | — | — | — | — | O | O |
| Print Receipt | Sw | Sw | — | — | — | C | — | — | — | — | O | O |

---

## 12. Payroll

| Capability | SA | AD | PR | TCH | HR | ACC | PM | LIB | REC | STF | PAR | STU |
|-----------|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| View Structure | Sw | Sw | — | O | — | — | Sw | — | — | O | — | — |
| Create Structure | Sw | Sw | — | — | — | — | C | — | — | — | — | — |
| Update Structure | Sw | Ap | — | — | — | — | U | — | — | — | — | — |
| Delete Structure | Sw | Sw | — | — | — | — | — | — | — | — | — | — |
| Run Payroll | Sw | Ap | — | — | — | — | C | — | — | — | — | — |
| View Payslips | Sw | Sw | Sw | O | — | — | Sw | — | — | O | — | — |
| Generate Payslip | Sw | Ap | — | — | — | — | C | — | — | — | — | — |
| Lock Payroll | Sw | — | — | — | — | — | Lk | — | — | — | — | — |
| Export Payroll | Sw | Sw | — | O | — | — | Ex | — | — | O | — | — |
| Print Payslip | Sw | Sw | — | O | — | — | C | — | — | O | — | — |

Lk = Can lock after processing
Ex = Can export

---

## 13. Leave Management

| Capability | SA | AD | PR | TCH | HR | ACC | PM | LIB | REC | STF | PAR | STU |
|-----------|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| View Own | Sw | Sw | Sw | O | O | O | O | O | O | O | — | — |
| View All | Sw | Sw | Sw | — | Sw | — | — | — | — | — | — | — |
| Apply | — | — | — | C | C | C | C | C | C | C | — | — |
| Approve | Sw | Sw | Ap | — | Rc | — | — | — | — | — | — | — |
| Update Own | — | — | — | O | O | O | O | O | O | O | — | — |
| Delete Own | — | — | — | O | O | O | O | O | O | O | — | — |
| Export | Sw | Sw | Sw | O | Sw | — | — | — | — | O | — | — |
| Configure Types | Sw | Sw | — | — | — | — | — | — | — | — | — | — |

Rc = HR can recommend approval (Principal makes final decision)

---

## 14. Library

| Capability | SA | AD | PR | TCH | HR | ACC | PM | LIB | REC | STF | PAR | STU |
|-----------|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| View Catalog | Sw | Sw | Sw | Sw | — | — | — | Sw | — | — | O* | O* |
| Add Book | Sw | Sw | — | — | — | — | — | C | — | — | — | — |
| Update Book | Sw | Sw | — | — | — | — | — | U | — | — | — | — |
| Remove Book | Sw | Sw | Ap | — | — | — | — | — | — | — | — | — |
| Issue Book | — | — | — | — | — | — | — | C | — | — | — | — |
| Return Book | — | — | — | — | — | — | — | C | — | — | — | — |
| Collect Fine | — | — | — | — | — | — | — | C | — | — | — | — |
| Waive Fine | — | — | Ap | — | — | — | — | Lmt | — | — | — | — |
| Export Catalog | Sw | Sw | Sw | — | — | — | — | Ex | — | — | — | — |
| View Issue History | Sw | Sw | — | — | — | — | — | Sw | — | — | O | O |

O* = Parent/Student can view catalog available for borrowing
Lmt = Librarian can waive fines up to defined limit; above limit requires Principal approval

---

## 15. Transport

| Capability | SA | AD | PR | TCH | HR | ACC | PM | LIB | REC | STF | PAR | STU |
|-----------|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| View Routes | Sw | Sw | Sw | — | — | Sw | — | — | — | — | O | O |
| Create Routes | Sw | Sw | — | — | — | — | — | — | — | — | — | — |
| Update Routes | Sw | Sw | — | — | — | — | — | — | — | — | — | — |
| Delete Routes | Sw | Sw | — | — | — | — | — | — | — | — | — | — |
| Assign Students | Sw | Sw | — | — | — | U | — | — | — | — | — | — |
| Manage Vehicles | Sw | Sw | — | — | — | — | — | — | — | — | — | — |
| Manage Drivers | Sw | Sw | — | — | — | — | — | — | — | — | — | — |
| Export Reports | Sw | Sw | Sw | — | — | Sw | — | — | — | — | — | — |
| View Live Tracking | — | Sw | — | — | — | — | — | — | — | — | O | O |

---

## 16. Documents (Student)

| Capability | SA | AD | PR | TCH | HR | ACC | PM | LIB | REC | STF | PAR | STU |
|-----------|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| View | Sw | Sw | Sw | As | — | — | — | — | — | — | O | O |
| Upload | Sw | Sw | — | As | — | — | — | — | — | — | — | O |
| Update | Sw | Sw | — | As | — | — | — | — | — | — | O | — |
| Delete | Sw | Sw | — | As | — | — | — | — | — | — | — | — |
| Verify | — | — | Ap | — | — | — | — | — | — | — | — | — |
| Download | Sw | Sw | Sw | As | — | — | — | — | — | — | O | O |
| Export | Sw | Sw | Sw | — | — | — | — | — | — | — | — | — |

---

## 17. Academic Calendar

| Capability | SA | AD | PR | TCH | HR | ACC | PM | LIB | REC | STF | PAR | STU |
|-----------|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| View | Sw | Sw | Sw | Sw | — | — | — | — | — | — | O | O |
| Create | Sw | Sw | Ap | — | — | — | — | — | — | — | — | — |
| Update | Sw | Sw | Ap | — | — | — | — | — | — | — | — | — |
| Delete | Sw | Sw | — | — | — | — | — | — | — | — | — | — |
| Publish | — | — | Ap | — | — | — | — | — | — | — | — | — |

---

## 18. Notifications

| Capability | SA | AD | PR | TCH | HR | ACC | PM | LIB | REC | STF | PAR | STU |
|-----------|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| View Inbox | Sw | Sw | Sw | O | O | O | O | O | O | O | O | O |
| Create (Send) | Sw | Sw | Sw | — | Sw | — | — | — | — | — | — | — |
| Create (Targeted) | Sw | Sw | Sw | As | Sw | — | — | — | — | — | — | — |
| Delete Own | Sw | Sw | Sw | — | — | — | — | — | — | — | — | — |
| Delete Any | Sw | Sw | — | — | — | — | — | — | — | — | — | — |
| Mark Read | Sw | Sw | Sw | O | O | O | O | O | O | O | O | O |
| Send to All | Sw | Sw | Sw | — | — | — | — | — | — | — | — | — |
| Send to Class | Sw | Sw | Sw | As | — | — | — | — | — | — | — | — |
| Send to Parents | Sw | Sw | Sw | As | Sw | — | — | — | — | — | — | — |

---

## 19. Reports

| Capability | SA | AD | PR | TCH | HR | ACC | PM | LIB | REC | STF | PAR | STU |
|-----------|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| Student Report | Sw | Sw | Sw | As | — | — | — | — | — | — | O | O |
| Attendance Report | Sw | Sw | Sw | As | Sw | — | — | — | — | O | O | O |
| Fee Report | Sw | Sw | Sw | — | — | Sw | — | — | — | — | O | O |
| Exam Report | Sw | Sw | Sw | As | — | — | — | — | — | — | O | O |
| Teacher Report | Sw | Sw | Sw | — | Sw | — | — | — | — | — | — | — |
| Payroll Report | Sw | Sw | — | — | — | — | Sw | — | — | O | — | — |
| Library Report | Sw | Sw | Sw | — | — | — | — | Sw | — | — | — | — |
| Transport Report | Sw | Sw | Sw | — | — | Sw | — | — | — | — | — | — |
| Custom Report | Sw | Sw | — | — | — | — | — | — | — | — | — | — |
| Export All Reports | Sw | Sw | Sw | As | Sw | Sw | Sw | Sw | — | O | O | O |
| Print All Reports | Sw | Sw | Sw | As | Sw | Sw | Sw | Sw | — | O | O | O |

---

## 20. Users (System Users)

| Capability | SA | AD | PR | TCH | HR | ACC | PM | LIB | REC | STF | PAR | STU |
|-----------|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| View Users | Sw | Sw | — | — | — | — | — | — | — | — | — | — |
| Create Users | Sw | Sw | — | — | — | — | — | — | — | — | — | — |
| Update Users | Sw | Sw | — | — | — | — | — | — | — | — | — | — |
| Delete Users | Sw | Sw | — | — | — | — | — | — | — | — | — | — |
| Reset Password | Sw | Sw | — | — | — | — | — | — | — | — | — | O |
| Assign Roles | Sw | Sw | — | — | — | — | — | — | — | — | — | — |
| Toggle Status | Sw | Sw | — | — | — | — | — | — | — | — | — | — |

---

## 21. Roles & Permissions

| Capability | SA | AD | PR | TCH | HR | ACC | PM | LIB | REC | STF | PAR | STU |
|-----------|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| View Roles | Sw | Sw | — | — | — | — | — | — | — | — | — | — |
| Create Roles | Sw | Sw* | — | — | — | — | — | — | — | — | — | — |
| Update Roles | Sw | Sw* | — | — | — | — | — | — | — | — | — | — |
| Delete Roles | Sw | — | — | — | — | — | — | — | — | — | — | — |
| View Permissions | Sw | Sw | — | — | — | — | — | — | — | — | — | — |
| Assign Permissions | Sw | Sw* | — | — | — | — | — | — | — | — | — | — |

*School Admin can create/update roles and assign permissions only within the predefined system role templates (cannot create system-level roles or modify Super Admin permissions)

---

## 22. Settings

| Capability | SA | AD | PR | TCH | HR | ACC | PM | LIB | REC | STF | PAR | STU |
|-----------|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| View Settings | Sw | Sw | — | — | — | — | — | — | — | — | — | — |
| Update Settings | Sw | Sw | — | — | — | — | — | — | — | — | — | — |

---

## 23. AI Workspace

| Capability | SA | AD | PR | TCH | HR | ACC | PM | LIB | REC | STF | PAR | STU |
|-----------|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| Ask ERP | Sw | Sw | Sw | Sw | Sw | Sw | Sw | Sw | Sw | Sw | O | O |
| Executive Copilot | Sw | Sw | Sw | — | — | — | — | — | — | — | — | — |
| AI Agents | Sw | Sw | — | — | — | — | — | — | — | — | — | — |
| View Execution History | Sw | Sw | — | — | — | — | — | — | — | — | — | — |

---

## Summary: Total Capabilities Per Role

| Role | C | R | U | D | Ap | Ex | Pr | Scope |
|------|:-:|:-:|:-:|:-:|:-:|:-:|:-:|:-----:|
| Super Admin | 26 | 35 | 25 | 19 | 12 | 19 | 12 | Sw |
| School Admin | 26 | 35 | 24 | 19 | 10 | 19 | 12 | Sw |
| Principal | 3 | 30 | 7 | 0 | 14 | 15 | 12 | Sw |
| Teacher | 11 | 13 | 7 | 2 | 0 | 8 | 6 | As |
| HR | 3 | 3 | 2 | 0 | 0 | 3 | 2 | Sw (limited) |
| Accountant | 6 | 6 | 3 | 0 | 0 | 5 | 2 | Sw (limited) |
| Payroll Manager | 5 | 5 | 2 | 0 | 1 | 4 | 1 | Sw (limited) |
| Librarian | 7 | 3 | 2 | 0 | 0 | 2 | 0 | Sw (limited) |
| Receptionist | 3 | 4 | 0 | 0 | 0 | 0 | 0 | Sw (limited) |
| Staff | 0 | 1 | 0 | 0 | 0 | 1 | 0 | O |
| Parent | 0 | 13 | 2 | 0 | 0 | 7 | 5 | O (children) |
| Student | 0 | 10 | 0 | 0 | 0 | 3 | 3 | O (self) |
