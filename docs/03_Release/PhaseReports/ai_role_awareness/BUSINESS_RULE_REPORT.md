# Business Rule Report — Phase 08: AI Role Awareness

## Enforced Business Rules

| # | Rule | Enforcement | Source |
|---|------|-------------|--------|
| 1 | **Super Admin / School Admin / Principal** have full AI access to all modules | `*` wildcard pattern in `role_permissions` | `config/ai.php:8-10` |
| 2 | **Teacher** restricted to class-related queries (attendance, own students, homework, exams, notifications, school summary) | Explicit intent allow-list in `role_permissions.Teacher`; data scoped to `class_section_ids` + `teacher_id` | `config/ai.php:12-18`, `RoleDataScoper.php:80-93` |
| 3 | **Parent** can see own children's data (attendance, student, fee, exam, homework, school summary) | `role_permissions.Parent` patterns; data scoped to `student_ids` via guardian relationship | `config/ai.php:20-23`, `RoleDataScoper.php:96-108` |
| 4 | **Student** can see own data (attendance, exam, homework, school summary) | `role_permissions.Student` patterns; data scoped to `student_id` | `config/ai.php:24-27`, `RoleDataScoper.php:111-122` |
| 5 | **Accountant** has fee, student, attendance, and school summary queries | Explicit patterns `fee.*`, `student.*`, `attendance.*`, `school.*` | `config/ai.php:11` |
| 6 | **Librarian** has library queries and school summary | `library.*`, `school.summary` | `config/ai.php:28` |
| 7 | **Staff** can query attendance and school summary | `attendance.*`, `school.summary` | `config/ai.php:29` |
| 8 | **Receptionist** can query student records only | `student.*` | `config/ai.php:30` |
| 9 | **All queries are logged** for audit trail regardless of status (success, error, denied) | `logQuery()` called on every request path in AIService | `AIService.php:608-625` |

## Error Messaging

Each restricted role receives a tailored error message explaining their allowed scope via `RoleDataScoper::getErrorMessage()` (`RoleDataScoper.php:63-78`).
