# Data Visibility Matrix

## Purpose

This document defines what data each role can access through the AI Assistant. It ensures that users only see data they are authorized to view, enforcing role-based access control (RBAC) across all AI queries.

## Role Hierarchy

```
Super Admin ─── Full system access, no restrictions
School Admin ── Full school-level access
Principal ───── Full school-level access
Accountant ──── Financial + student + attendance data
Teacher ─────── Class-scoped data only
Parent ──────── Child-scoped data only
Student ─────── Self-scoped data only
Librarian ───── Library operations only
Staff ───────── Attendance + school summary
Receptionist ── Student records only
```

## Permission Matrix

| Role | Module | Permissions | Notes |
|------|--------|-------------|-------|
| **Super Admin** | All | Full access (`*`) | Bypasses all scope filtering |
| **School Admin** | All | Full access (`*`) | School-scoped via SchoolContext |
| **Principal** | All | Full access (`*`) | School-scoped via SchoolContext |
| **Accountant** | Fee | `fee.*` | All fee queries |
| | Student | `student.*` | All student queries |
| | Attendance | `attendance.*` | All attendance queries |
| | School | `school.*` | School-level summaries |
| **Teacher** | Attendance | `absent_today`, `monthly_percentage`, `below_75` | Scoped to assigned classes |
| | Student | `total`, `by_class` | Scoped to assigned classes |
| | Homework | `create` | For assigned classes |
| | Exam | `publish` | |
| | Notification | `send` | Class-scoped |
| | School | `summary` | |
| **Parent** | Attendance | `attendance.*` | Scoped to own children |
| | Student | `student.*` | Scoped to own children |
| | Fee | `fee.*` | Scoped to own children |
| | Exam | `exam.*` | Scoped to own children |
| | Homework | `homework.*` | Scoped to own children |
| | School | `summary` | |
| **Student** | Attendance | `attendance.*` | Self-scoped |
| | Exam | `exam.*` | Self-scoped |
| | Homework | `homework.*` | Self-scoped |
| | School | `summary` | |
| **Librarian** | Library | `library.*` | All library queries |
| | School | `summary` | |
| **Staff** | Attendance | `attendance.*` | |
| | School | `summary` | |
| **Receptionist** | Student | `student.*` | Student records only |

## Data Scoping Rules

When a role has scoped access, query parameters are automatically injected to filter results. The scoper maps authenticated user identity to database records.

### Teacher Scoping

| Filter | Source | Description |
|--------|--------|-------------|
| `class_section_ids` | `Teacher->classSections()` | All class sections the teacher is assigned to |
| `teacher_id` | `Teacher->id` | The teacher's own record ID |

### Parent Scoping

| Filter | Source | Description |
|--------|--------|-------------|
| `student_ids` | `Guardian->students()` | All children linked to the parent |

### Student Scoping

| Filter | Source | Description |
|--------|--------|-------------|
| `student_id` | `Student->id` | The student's own record ID |

## Enforcement Points

1. **Intent Resolution** — The `RoleDataScoper` checks if the resolved intent matches the user's allowed patterns.
2. **Query Execution** — Scope filters are merged into query parameters before handler execution.
3. **Audit Logging** — All queries, including denied ones, are logged to `ai_query_logs`.

## Denied Access Response

When a user attempts an unauthorized intent, the system returns a role-appropriate error message (e.g., "Teachers can only ask questions about their classes, students, attendance, homework, and exams.").

## Configuration

Role permissions are defined in `config/ai.php` under the `role_permissions` key. Data scoping rules are under the `data_scoping` key.
