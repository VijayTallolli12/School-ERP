# Academics Module

Version: 1.0.0

Revision date: 2026-07-08

## Purpose

The academics module manages academic years, classes, sections, subjects, and class-subject assignments.

## Architecture

Implemented via AcademicController, AcademicService, AcademicRepository, and academic policies.

## Database Tables

- academic_years
- classes
- sections
- class_section
- subjects
- class_subjects
- academic_terms

## Models

- App\Modules\Academics\Models\AcademicYear
- App\Modules\Academics\Models\SchoolClass
- App\Modules\Academics\Models\Section
- App\Modules\Academics\Models\Subject

## Controllers

- AcademicController

## Services

- AcademicService
- ClassesService

## Routes

- /admin/academics

## Policies

- AcademicYearPolicy
- SchoolClassPolicy
- SectionPolicy
- ClassSectionPolicy
- SubjectPolicy

## Permissions

- academics.view
- academics.create
- academics.update
- academics.delete

## Business Rules

- Academic structures are school-scoped.
- Students and teachers rely on these records for class, section, subject, and timetable flows.

## Workflow

1. Create academic year and school classes.
2. Add sections and subjects.
3. Link classes and subjects to support other modules.

## Common Issues

- Missing academic context can break student, teacher, timetable, and examination workflows.

## Troubleshooting

- Verify the relevant academic year, class, and section records exist.
- Confirm the user has the proper academics permissions.
