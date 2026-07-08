# Business Rule Report – Phase 06: Parent Student Portal

## Business Rules Implemented

### Student Portal
| Rule | Description |
|------|-------------|
| SR-01 | Student sees **own** attendance percentage (present + late + half_day / total days). |
| SR-02 | Student sees **homework count** assigned to their active class sections. |
| SR-03 | Student sees **upcoming exam count** for their active class sections with `exam_date >= today`. |
| SR-04 | Student sees **active session count** (sessions with status = 'active'). |
| SR-05 | Student uses the **admin layout** (`layouts/admin`) with a **limited sidebar** (Dashboard, Attendance, Timetable, Exams only). |
| SR-06 | Student stat cards are read-only — no quick actions are rendered. |

### Parent Portal (Enhancements)
| Rule | Description |
|------|-------------|
| PR-01 | Parent sees **aggregated** data across all children via `ParentService`. |
| PR-02 | Parent stat cards show: Attendance %, Pending Fees, Average Exam Score %, Active Homework count. |
| PR-03 | Parent quick actions link to: View Attendance, View Fees, Homework, Exam Results. |
| PR-04 | Parent portal now includes **sidebar navigation** for the first time (7 items). |
| PR-05 | Parent layout (`parent.blade.php`) now includes sidebar, announcement banner, and AI assistant modal. |

## Rule Sources
- SR-01–SR-06: `app/Modules/Dashboard/Services/Builders/StudentDashboardBuilder.php`
- PR-01–PR-03: `app/Modules/Dashboard/Services/Builders/ParentDashboardBuilder.php`
- PR-04: `app/Modules/Dashboard/Services/SidebarBuilder.php` (`buildForParent`)
- PR-05: `resources/views/layouts/parent.blade.php`
