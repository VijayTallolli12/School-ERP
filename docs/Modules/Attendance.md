# Attendance Module

Version: 1.0.0

Revision date: 2026-07-08

## Purpose

The attendance module manages student attendance records and reports.

## Architecture

The module uses AttendanceController, AttendanceService, AttendanceRepository, and attendance policies.

## Database Tables

- attendances
- students
- class_section

## Models

- App\Modules\Attendance\Models\Attendance

## Controllers

- AttendanceController

## Services

- AttendanceService

## Routes

- /admin/attendance
- /admin/attendance/data

## Policies

- AttendancePolicy

## Permissions

- attendance.view
- attendance.create
- attendance.update
- attendance.delete

## Business Rules

- Attendance is recorded for students within the current school and school year context.
- Attendance data feeds reports and AI summary workflows.

## Workflow

1. Attendance is recorded for a class section or student set.
2. Records are stored against the current school context.
3. Reports and dashboards query the attendance data.

## Common Issues

- Missing school context leads to empty or incorrect results.
- Permission errors occur when the user lacks attendance permissions.

## Troubleshooting

- Verify the school context is set correctly.
- Ensure the relevant user role has attendance permissions.
