# Route Report – Phase 06: Parent Student Portal

## New Routes
None. No new route definitions were added in Phase 06.

## Routes Reused

### Parent Portal (existing `parent-portal.*` named routes)
```
parent-portal.dashboard
parent-portal.attendance
parent-portal.fees
parent-portal.exam-results
parent-portal.timetable
parent-portal.homework
parent-portal.notifications
```

### Student Portal (existing `admin.*` named routes)
```
admin.dashboard
admin.attendance.index
admin.timetable.index
admin.exams.index
```

## Notes
- Parent sidebar items link to `parent-portal.*` routes (defined in prior phases).
- Student sidebar items link to `admin.*` routes (shared with admin/teacher/staff roles, but visibility is restricted in the sidebar blade).
- The `DashboardFactory` and `SidebarBuilder` route generation uses these existing named routes — no routing changes were necessary.
