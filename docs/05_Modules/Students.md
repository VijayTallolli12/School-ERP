# Students Module

Version: 1.0.0

Revision date: 2026-07-08

## Purpose

The students module provides student profile management, search, and student-related workflows.

## Architecture

The module uses a controller, service, repository, policy, and routes under app/Modules/Students.

## Database Tables

- students
- student_guardians
- student_documents
- student_sessions
- class_section
- classes
- sections

## Models

- App\Modules\Students\Models\Student
- App\Modules\Parents\Models\Guardian

## Controllers

- StudentController

## Services

- StudentService

## Routes

- /admin/students
- /admin/students/data
- /admin/students/search

## Policies

- StudentPolicy

## Permissions

- students.view
- students.create
- students.update
- students.delete

## Business Rules

- Students belong to a school context.
- Student records can be linked to guardians and documents.
- Student data is used across attendance, fees, exams, and transport workflows.

## Workflow

1. School admin or authorized user creates or updates the student profile.
2. The student is assigned to a class/section.
3. Related records such as guardians and documents can be managed.
4. Other modules consume student data.

## Common Issues

- Missing school context during search or data listing.
- Student lookup failing due to missing class/section assignments.

## Troubleshooting

- Validate the student has an active class section assignment.
- Verify the user has the required permission for student actions.
