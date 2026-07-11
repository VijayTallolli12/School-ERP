# Reports Module

Version: 1.0.0

Revision date: 2026-07-08

## Purpose

The reports module provides specialized reporting endpoints for teachers, students, parents, attendance, fees, and exams.

## Architecture

Reports are implemented through controller classes and repository/service pairs under app/Modules/Reports.

## Database Tables

- The reports module queries the underlying operational tables for students, attendance, fees, exams, and teachers.

## Models

- Report data is typically produced from existing module models rather than dedicated report models.

## Controllers

- TeacherReportController
- StudentReportController
- ParentReportController
- FeeReportController
- ExamReportController
- AttendanceReportController
- AbsentStudentReportController

## Services

- TeacherReportService
- StudentReportService
- ParentReportService
- ExamReportService
- AttendanceReportService
- AbsentStudentReportService

## Routes

- /reports/*
- /admin/reports/*

## Permissions

- reports.view

## Business Rules

- Reports are scoped by the active school context.
- Report endpoints return data tables or structured payloads used by UI views.

## Workflow

1. A report endpoint is invoked.
2. The report service gathers the necessary data.
3. The controller returns a report payload or view.

## Common Issues

- Mismatched or missing school context can return incomplete data.
- Report endpoints can be slow when underlying queries are dense.

## Troubleshooting

- Confirm the school context is set correctly before generating a report.
- Review the relevant service/repository to identify data gaps.
