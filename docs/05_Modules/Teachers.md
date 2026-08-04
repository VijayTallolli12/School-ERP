# Teachers Module

Version: 1.0.0

Revision date: 2026-07-08

## Purpose

The teachers module supports teacher profile management, attendance tracking, leave requests, reports, and document handling.

## Architecture

The module is implemented with controller, service, repository, policy, and route definitions under app/Modules/Teachers.

## Database Tables

- teachers
- teacher_documents
- attendances
- leave_requests
- leave_types

## Models

- App\Modules\Teachers\Models\Teacher
- App\Modules\Teachers\Models\TeacherDocument

## Controllers

- TeacherController

## Services

- TeacherService

## Routes

- /admin/teachers
- /admin/teachers/attendance
- /admin/teachers/leaves
- /admin/teachers/reports/subjects
- /admin/teachers/reports/attendance

## Policies

- TeacherPolicy
- TeacherDocumentPolicy

## Permissions

- teachers.view
- teachers.create
- teachers.update
- teachers.delete

## Business Rules

- Teacher records are school-scoped.
- Teachers can have attendance and leave records.
- Teacher reports are available through dedicated endpoints.

## Workflow

1. Create or update teacher profile.
2. Link teacher to academic assignments if present.
3. Record attendance and leave events.
4. Generate teacher-specific reports.

## Common Issues

- Teacher access may fail if the school context is not resolved.
- Attendance and leave records may be restricted by permissions.

## Troubleshooting

- Confirm the teacher has a valid user account and school assignment.
- Review permissions and route middleware.
