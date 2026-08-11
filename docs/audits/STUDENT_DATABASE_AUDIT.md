# Student Database Audit

> Generated against the live MySQL demo dataset (`school_erp`)
> Scope: student auth / linkage tables

---

## 1. Tables Checked

| Table | Purpose |
|-------|---------|
| `users` | Login accounts |
| `students` | Student profiles (linked via `user_id`) |
| `student_sessions` | Enrollment per academic year / class / section |
| `parent_student` | Guardian ↔ Student pivot |
| `academic_years` | Academic years (active flag) |
| `schools` | Tenant schools |
| `school_user` | User ↔ School pivot |
| `model_has_roles` | Role assignments (team-scoped by school) |

---

## 2. Findings

### Schools
| id | code | name | status |
|----|------|------|--------|
| 1 | DEMO | Scholastic Insight | active |

### Academic Years
| id | school_id | name | is_active | status |
|----|-----------|------|-----------|--------|
| 1 | 1 | 2026-2027 | 1 | active |

### Users with the `Student` role
12 users (ids 7–18), all `status=active`, all `current_school_id=1`:

| id | email | name |
|----|-------|------|
| 7 | student.arjun.verma@example.com | Arjun Verma |
| 8 | student.priya.patel@example.com | Priya Patel |
| 9 | student.rohit.sharma@example.com | Rohit Sharma |
| 10 | student.sneha.reddy@example.com | Sneha Reddy |
| 11 | student.amit.singh@example.com | Amit Singh |
| 12 | student.neha.gupta@example.com | Neha Gupta |
| 13 | student.vikram.joshi@example.com | Vikram Joshi |
| 14 | student.pooja.nair@example.com | Pooja Nair |
| 15 | student.karan.mehta@example.com | Karan Mehta |
| 16 | student.divya.kapoor@example.com | Divya Kapoor |
| 17 | student.ravi.desai@example.com | Ravi Desai |
| 18 | student.anjali.menon@example.com | Anjali Menon |

### Students
12 records (ids 1–12). Every record has `school_id=1`, a populated `user_id` (7–18), `status=active`, none soft-deleted.

### Student Sessions
12 active sessions (`status=active`), one per student, all `academic_year_id=1` and `school_id=1`.

### Guardian–Student pivot (`parent_student`)
4 rows: parent 1 ↔ students 1,2 · parent 2 ↔ students 3,4.

---

## 3. Consistency Checks

| Check | Result |
|-------|--------|
| Orphan student records (`students.user_id IS NULL`) | ✅ 0 |
| Student records referencing a missing user (broken FK) | ✅ 0 |
| Duplicate `user_id` links (one user → many students) | ✅ 0 |
| Orphan student users (has `Student` role, no student record) | ✅ 0 |
| Student records whose user lacks the `Student` role | ✅ 0 |
| Cross-school linkage (`students.school_id != users.current_school_id`) | ✅ 0 |
| `school_user` pivot rows for student users | ❌ **0 rows before repair** |

---

## 4. Issue Found & Resolution

### Issue DB-1 — Missing `school_user` pivot rows
**Severity:** Low (latent)
**Detail:** The golden seeder created student (and teacher/parent) Users and assigned roles, but never attached them to the school via the `school_user` pivot. School context resolution currently succeeds via `users.current_school_id` and `model_has_roles`, but the pivot gap is a fragile single point of failure if `current_school_id` is ever NULL.

**Resolution:**
1. **Runtime self-healing** — `StudentAuthService::repairSchoolPivot()` now restores the pivot on login/request when it is missing.
2. **Seeder fixed** — `GoldenSchoolSeeder` now attaches every created user to the school pivot.
3. **One-time live repair** — 15 pivot rows attached for the demo school (11 remaining students + 4 teachers/parents; user 7 was already repaired by the runtime mechanism during verification).

---

## 5. Verified Live Relationship Matrix (student `Arjun Verma`)

| Relationship | Status |
|--------------|--------|
| User → Student (hasOne) | ✅ |
| Student → User (belongsTo) | ✅ |
| Student → School | ✅ |
| School → Active Academic Year | ✅ |
| Student → Current Session | ✅ |
| Session → Academic Year | ✅ |
| Session → ClassSection → SchoolClass | ✅ (Class 1) |
| ClassSection → Section | ✅ (Section A) |
| Student → Guardian | ✅ (1 parent) |
| Student → Transport | ✅ (assignment present) |
| Student → Documents | ⚠️ 0 (no seed data — non-blocking, relationship itself is correct) |
| Student → Attendance | ✅ (65 records) |
| Student → Fees | ✅ (1 fee assignment) |
| Student → Homework (via class) | ✅ (5 active) |
| Student → Results | ✅ (5 results) |
| Student → Timetable | ✅ (3 slots) |

> Documents is the only empty relationship; it is a seed-data gap, not a linkage defect.
