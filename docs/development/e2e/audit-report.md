# School ERP — UI/UX/Functional Audit Report

**Date:** 2026-06-22
**Pages Audited:** 56
**Total Issues:** 15

## Summary

| Severity | Count |
|----------|-------|
| Critical | 0 |
| High | 8 |
| Medium | 0 |
| Low | 7 |

## Issues

| # | Page | Category | Severity | Issue | Screenshot | Root Cause | Recommended Fix |
|---|------|----------|----------|-------|------------|------------|------------------|
| 1 | Reports > Fees > Paid Fees Report | Functional | High | Export button "Export Excel" has no working href | — | href="#" placeholder not wired up | Add proper export route |
| 2 | Reports > Fees > Paid Fees Report | Functional | High | Export button "Export PDF" has no working href | — | href="#" placeholder not wired up | Add proper export route |
| 3 | Reports > Fees > Pending Fees Report | Functional | High | Export button "Export Excel" has no working href | — | href="#" placeholder not wired up | Add proper export route |
| 4 | Reports > Fees > Pending Fees Report | Functional | High | Export button "Export PDF" has no working href | — | href="#" placeholder not wired up | Add proper export route |
| 5 | Reports > Fees > Overdue Fees Report | Functional | High | Export button "Export Excel" has no working href | — | href="#" placeholder not wired up | Add proper export route |
| 6 | Reports > Fees > Overdue Fees Report | Functional | High | Export button "Export PDF" has no working href | — | href="#" placeholder not wired up | Add proper export route |
| 7 | Reports > Fees > Fee Defaulters | Functional | High | Export button "Export Excel" has no working href | — | href="#" placeholder not wired up | Add proper export route |
| 8 | Reports > Fees > Fee Defaulters | Functional | High | Export button "Export PDF" has no working href | — | href="#" placeholder not wired up | Add proper export route |
| 9 | Login | Setup | Low | Login successful — audit session started | [Screenshot](screenshots/001_login_success.png) | — | — |
| 10 | Reports > Attendance > Daily Attendance | Data Integrity | Low | DataTable shows empty state — may indicate missing seed data | — | No records in database table | Verify seed data or check query filters |
| 11 | Reports > Attendance > Absent Students Report | Data Integrity | Low | DataTable shows empty state — may indicate missing seed data | — | No records in database table | Verify seed data or check query filters |
| 12 | Reports > Fees > Overdue Fees Report | Data Integrity | Low | DataTable shows empty state — may indicate missing seed data | — | No records in database table | Verify seed data or check query filters |
| 13 | Reports > Fees > Collection Summary | Data Integrity | Low | DataTable shows empty state — may indicate missing seed data | — | No records in database table | Verify seed data or check query filters |
| 14 | Reports > Exams > Student Result Summary | Data Integrity | Low | DataTable shows empty state — may indicate missing seed data | — | No records in database table | Verify seed data or check query filters |
| 15 | Modules > Exams | Data Integrity | Low | DataTable shows empty state — may indicate missing seed data | — | No records in database table | Verify seed data or check query filters |

---

## Pages Audited

| # | Category | Page | URL |
|---|----------|------|-----|
| 1 | Dashboard | Dashboard | /admin/dashboard |
| 2 | Access Control | Roles | /admin/roles |
| 3 | Access Control | Permissions | /admin/permissions |
| 4 | Modules | Notifications | /admin/notifications |
| 5 | Modules | Fees | /admin/fees |
| 6 | Modules | Settings | /admin/settings |
| 7 | Reports > Students | Student Reports Dashboard | /reports/students |
| 8 | Reports > Students | Student Directory | /reports/students/directory |
| 9 | Reports > Students | Gender-wise Report | /reports/students/gender-wise |
| 10 | Reports > Attendance | Attendance Reports Dashboard | /reports/attendance |
| 11 | Reports > Attendance | Daily Attendance | /reports/attendance/daily |
| 12 | Reports > Attendance | Monthly Attendance | /reports/attendance/monthly |
| 13 | Reports > Attendance | Class-wise Attendance | /reports/attendance/class-wise |
| 14 | Reports > Attendance | Absent Students Report | /reports/attendance/absent-students |
| 15 | Reports > Fees | Fee Reports Dashboard | /reports/fees |
| 16 | Reports > Fees | Paid Fees Report | /reports/fees/paid |
| 17 | Reports > Fees | Pending Fees Report | /reports/fees/pending |
| 18 | Reports > Fees | Overdue Fees Report | /reports/fees/overdue |
| 19 | Reports > Fees | Collection Summary | /reports/fees/collection-summary |
| 20 | Reports > Fees | Fee Defaulters | /reports/fees/defaulters |
| 21 | Reports > Exams | Exam Reports Dashboard | /reports/exams |
| 22 | Reports > Exams | Exam Results Report | /reports/exams/results |
| 23 | Reports > Exams | Class Performance Report | /reports/exams/class-performance |
| 24 | Reports > Exams | Subject Performance Report | /reports/exams/subject-performance |
| 25 | Reports > Exams | Student Result Summary | /reports/exams/student-summary |
| 26 | Reports > Exams | Top Performers | /reports/exams/top-performers |
| 27 | Reports > Exams | Pass/Fail Analysis | /reports/exams/pass-fail-analysis |
| 28 | Reports > Teachers | Teacher Reports Dashboard | /reports/teachers |
| 29 | Reports > Teachers | Teacher List | /reports/teachers/list |
| 30 | Reports > Teachers | Teacher Attendance | /reports/teachers/attendance |
| 31 | Reports > Teachers | Subject Allocation | /reports/teachers/subject-allocation |
| 32 | Reports > Teachers | Class Teacher Mapping | /reports/teachers/class-teacher-mapping |
| 33 | Reports > Teachers | Workload | /reports/teachers/workload |
| 34 | Reports > Parents | Parent Reports Dashboard | /reports/parents |
| 35 | Reports > Parents | Parent List | /reports/parents/list |
| 36 | Reports > Parents | Parent-Student Mapping | /reports/parents/mapping |
| 37 | Reports > Parents | Activity Summary | /reports/parents/activity-summary |
| 38 | Modules | Students | /admin/students |
| 39 | Modules | Parents | /admin/parents |
| 40 | Modules | Teachers | /admin/teachers |
| 41 | Modules | Exams | /admin/exams |
| 42 | Modules | Homework | /admin/homework |
| 43 | Leave Management | Leave Types | /admin/leave-types |
| 44 | Leave Management | Leave Requests | /admin/leave-requests |
| 45 | Modules | Academic | /admin/academics |
| 46 | Modules | Timetable | /admin/timetable |
| 47 | Modules | Attendance | /admin/attendance |
| 48 | Modules | Student Documents | /admin/documents |
| 49 | Modules | Academic Calendar | /admin/calendar |
| 50 | Modules | Users | /admin/users |
| 51 | Modules | Transport | /admin/transport |
| 52 | Modules | Transport Reports | /admin/transport/reports |
