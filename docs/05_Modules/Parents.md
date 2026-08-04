# Parents Module

Version: 1.0.0

Revision date: 2026-07-08

## Purpose

The parents module supports parent profile management, student-linking, and parent portal data access.

## Architecture

Implemented via ParentController, ParentService, ParentRepository, and ParentPolicy.

## Database Tables

- parents
- parent_student
- parent_notifications

## Models

- App\Modules\Parents\Models\Guardian
- App\Modules\Parents\Models\ParentStudent

## Controllers

- ParentController

## Services

- ParentService

## Routes

- /admin/parents

## Policies

- ParentPolicy

## Permissions

- parents.view
- parents.create
- parents.update
- parents.delete

## Business Rules

- Parent records are school-scoped and connected to one or more students.
- Parent users can access portal-oriented views through the active school context.

## Workflow

1. Create or update parent profile.
2. Link the parent to a student record.
3. Use the parent portal views for attendance, fees, homework, exams, and notifications.

## Common Issues

- Missing student-link records can reduce portal visibility.
- Permission errors can block parent management.

## Troubleshooting

- Check the guardian/student relationship records.
- Verify the user has the correct parent permissions.
