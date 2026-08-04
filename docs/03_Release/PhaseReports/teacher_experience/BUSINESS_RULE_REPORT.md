# Business Rule Report - Phase 02: Teacher Experience Refactor

## 1. Assigned Classes/Sections/Subjects

| Rule ID | Rule | Enforcement Point | Policy/Code |
|---------|------|-------------------|-------------|
| BR-01 | Teacher can only see class sections they are assigned to | `TeacherDashboardBuilder`, `TeacherDashboardCollector` | `$teacher->classSections()->pluck('class_section_id')` |
| BR-02 | Teacher dashboard only shows data for their assigned class sections | `TeacherDashboardCollector` (all methods) | `whereIn('class_section_id', $classSectionIds)` in all queries |
| BR-03 | Teacher timetable is scoped to their own schedule | `TeacherDashboardCollector::todaySchedule()` | `where('teacher_id', $teacherId)` |

---

## 2. Attendance

| Rule ID | Rule | Enforcement Point | Policy/Code |
|---------|------|-------------------|-------------|
| BR-04 | Teacher can only mark attendance for own class sections | `AttendancePolicy::create()` | `$teacher->classSections->pluck('id')->contains($attendance->class_section_id)` |
| BR-05 | Teacher can only view attendance for own class sections | `AttendancePolicy::view()` | Same class section ownership check |
| BR-06 | Teacher can only update attendance for own class sections | `AttendancePolicy::update()` | Same class section ownership check |
| BR-07 | Teacher can only delete attendance for own class sections | `AttendancePolicy::delete()` | Same class section ownership check |
| BR-08 | Attendance cannot be backdated beyond 7 days | Request validation | Configurable in Leave/Attendance settings |
| BR-09 | Cannot mark attendance for future dates | Request validation | Standard validation |
| BR-10 | At least one student must be marked before submission | Request validation | Standard validation |

---

## 3. Homework

| Rule ID | Rule | Enforcement Point | Policy/Code |
|---------|------|-------------------|-------------|
| BR-11 | Teacher can only view homework for own class sections | `HomeworkPolicy::view()` | `$teacher->classSections->pluck('id')->contains($homework->class_section_id)` |
| BR-12 | Teacher can only edit homework they created | `HomeworkPolicy::update()` | `$homework->created_by === $user->id` |
| BR-13 | Teacher can only delete homework they created | `HomeworkPolicy::delete()` | `$homework->created_by === $user->id` |
| BR-14 | Due date must be a future date | Request validation | Standard validation |
| BR-15 | Attachment is optional, max 10MB (PDF, DOC, DOCX, images) | Request validation | Standard file validation |

---

## 4. Students

| Rule ID | Rule | Enforcement Point | Policy/Code |
|---------|------|-------------------|-------------|
| BR-16 | Teacher can only view students in their assigned class sections | `StudentPolicy::view()` | `$student->sessions()->whereIn('class_section_id', $assignedIds)->where('status', 'active')->exists()` |
| BR-17 | Teacher can only update students in their assigned class sections | `StudentPolicy::update()` | Same active session check |
| BR-18 | Teacher can only delete students in their assigned class sections | `StudentPolicy::delete()` | Same active session check |
| BR-19 | Teacher cannot view students not in their class sections | `StudentPolicy::view()` | Returns false if no matching active session |

---

## 5. Exams / Marks

| Rule ID | Rule | Enforcement Point | Policy/Code |
|---------|------|-------------------|-------------|
| BR-20 | Teacher can only view exams for own class sections | `ExamPolicy::view()` | `$teacher->classSections->pluck('id')->contains($exam->class_section_id)` |
| BR-21 | Teacher can only enter marks for own exams | `ExamPolicy::create()` | Same class section ownership check |
| BR-22 | Teacher can only update marks for own exams | `ExamPolicy::update()` | Same class section ownership check |
| BR-23 | Teacher cannot publish exam results | `ExamPolicy::publish()` | Returns `false` for Teacher role |
| BR-24 | Teacher cannot delete exams | `ExamPolicy::delete()` | Returns `false` for Teacher role |
| BR-25 | Marks must be between 0 and maximum marks defined for the exam | Request validation | Standard validation |
| BR-26 | Published results are visible to students and parents | Permission gating | Standard visibility rules |

---

## 6. Leave

| Rule ID | Rule | Enforcement Point | Policy/Code |
|---------|------|-------------------|-------------|
| BR-27 | Leave request is automatically linked to the teacher's user ID | `LeaveRequestController@store` | Auto-assigns `user_id` from authenticated user |
| BR-28 | Teacher can view only their own leave requests | `LeaveRequestPolicy::view()` | `$leaveRequest->user_id === $user->id` |
| BR-29 | Teacher can only delete own pending leave requests | `LeaveRequestPolicy::delete()` | `$leaveRequest->user_id === $user->id && $leaveRequest->status === 'pending'` |
| BR-30 | Teacher cannot approve or reject leave requests | `LeaveRequestPolicy::approve()` | Returns `false` for Teacher role |
| BR-31 | Cannot apply for dates in the past | Request validation | Standard validation |
| BR-32 | Maximum consecutive days configurable per leave type | Leave type configuration | Configurable in `leave_types` table |
| BR-33 | Teacher self-service route (`my-leaves`) auto-filters by user | `LeaveRequestController::myLeavesData()` | `where('leave_requests.user_id', auth()->id())` |
| BR-34 | Admin leave management route also scopes for teachers | `LeaveRequestController::data()` | `if (auth()->user()->hasRole('Teacher')) { $query->where('leave_requests.user_id', auth()->id()); }` |

---

## 7. Documents (Teacher Self-Service)

| Rule ID | Rule | Enforcement Point | Policy/Code |
|---------|------|-------------------|-------------|
| BR-35 | Teacher can only view their own documents | `TeacherDocumentController::index()` | `$teacher->documents()->latest()->get()` |
| BR-36 | Teacher can only download their own documents | `TeacherDocumentController::download()` | `$document->teacher_id !== $teacher->id` => 403 |
| BR-37 | Teacher cannot view documents of other teachers | `TeacherDocumentController::index()` | Always scoped by authenticated user's teacher record |
| BR-38 | Documents are read-only for teachers (no upload/delete) | `TeacherDocumentPolicy` | No `create`/`update`/`delete` permissions for Teacher role |

---

## 8. Payslips (Self-Service)

| Rule ID | Rule | Enforcement Point | Policy/Code |
|---------|------|-------------------|-------------|
| BR-39 | Teacher can only view their own payslips | `PayrollController::myPayslipsData()` | `where('employee_type', 'teacher')->where('employee_id', $teacher->id)` |
| BR-40 | Teacher cannot view all payslips (admin function) | Route middleware | `my-payslips` routes outside `permission:payroll.view` gate |
| BR-41 | Teacher cannot access payroll management | Route middleware | All `payroll.*` management routes require `payroll.view` permission |
| BR-42 | Payslip visibility is read-only for teachers | `PayrollPolicy` | No `create`/`update`/`delete` permissions via self-service |
| BR-43 | Teacher can download/print own payslip PDF | `PayrollController` | Uses existing `payslips.pdf` and `payslips.print` routes |

---

## 9. AI / Ask ERP

| Rule ID | Rule | Enforcement Point | Policy/Code |
|---------|------|-------------------|-------------|
| BR-44 | Teachers can ONLY use "Ask ERP" (not Executive Copilot, AI Agents, Execution History) | Sidebar (`sidebar.blade.php`, `SidebarBuilder`) | Teacher sidebar section only includes Ask ERP |
| BR-45 | Teachers can only ask about: attendance, students, homework, exams, notifications, school summary | `AIService::isTeacherAuthorized()` | `TEACHER_ALLOWED_INTENTS` = 8 predefined intents |
| BR-46 | Questions about fees, transport, library, payroll are BLOCKED | `AIService::ask()` | Returns error message: "Teachers can only ask questions about their classes, students, attendance, homework, and exams." |
| BR-47 | All AI responses scoped to teacher's assigned class sections | `AIService::scopeToTeacherData()` | Injects `class_section_ids` and `teacher_id` into intent parameters |
| BR-48 | Maximum 500 characters per question | Request validation | Standard validation |
| BR-49 | Unknown intents are allowed (pass through) | `AIService::isTeacherAuthorized()` | `if ($intent === 'unknown') { return true; }` |
| BR-50 | Non-teacher roles retain full AI access | `AIService::isTeacherAuthorized()` | `if (!$user->hasRole('Teacher')) { return true; }` |

---

## 10. Dashboard

| Rule ID | Rule | Enforcement Point | Policy/Code |
|---------|------|-------------------|-------------|
| BR-51 | Dashboard shows no Finance/Payroll data | `TeacherDashboardBuilder` | All finance/payroll queries removed |
| BR-52 | Dashboard shows no Transport/Library data | `TeacherDashboardBuilder` | Never present in builder |
| BR-53 | Dashboard shows no School Analytics/Reports | `TeacherDashboardBuilder` | Charts and recent activity return empty arrays |
| BR-54 | Only active teachers can access dashboard | `Teacher::status` check | `Teacher::query()->where('user_id', $this->user->getKey())->first()` - null check returns empty |
| BR-55 | Dashboard stat cards navigate to relevant sections | `TeacherDashboardBuilder::buildStatCards()` | Each card has a route link |

---

## Rule Summary

| Category | Rule Count |
|----------|-----------|
| Assigned Classes/Sections/Subjects | 3 |
| Attendance | 7 |
| Homework | 5 |
| Students | 4 |
| Exams / Marks | 7 |
| Leave | 8 |
| Documents | 4 |
| Payslips | 5 |
| AI / Ask ERP | 7 |
| Dashboard | 5 |
| **Total** | **55** |
