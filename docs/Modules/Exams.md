# Exams Module

Version: 1.0.0

Revision date: 2026-07-08

## Purpose

The exams module manages exams, grade scales, schedules, result entry, bulk result saving, and publishing.

## Architecture

The module combines controllers for exams, grade scales, schedules, and marks with services and policies.

## Database Tables

- exams
- exam_schedules
- exam_marks
- grade_scales
- class_section
- students

## Models

- App\Modules\Exams\Models\Exam
- App\Modules\Exams\Models\ExamSchedule
- App\Modules\Exams\Models\ExamMark
- App\Modules\Exams\Models\GradeScale

## Controllers

- ExamController
- GradeScaleController
- ExamScheduleController
- ExamMarkController

## Services

- ExamService
- GradingService

## Routes

- /admin/exams
- /admin/exams/results
- /admin/exams/results/bulk
- /admin/exams/{exam}/publish

## Policies

- ExamPolicy
- ExamSchedulePolicy
- ExamMarkPolicy
- GradeScalePolicy

## Permissions

- exams.view
- exams.create
- exams.update
- exams.delete
- exams.publish

## Business Rules

- Exams are created per school context.
- Results can be entered individually or in bulk.
- Publishing actions are permission-protected.

## Workflow

1. Define the exam and grade scale.
2. Create a schedule for the relevant class section.
3. Enter marks.
4. Publish the results.

## Common Issues

- Bulk save may fail if the selected class section or student set is incomplete.
- Publishing requires both a valid exam and sufficient permissions.

## Troubleshooting

- Review the exam schedule and student mappings.
- Validate the user has exams.publish permission.
