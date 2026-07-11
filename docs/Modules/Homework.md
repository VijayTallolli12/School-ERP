# Homework Module

Version: 1.0.0

Revision date: 2026-07-08

## Purpose

The homework module supports assignment creation, retrieval, update, and related class/subject selection.

## Architecture

Implemented via HomeworkController, HomeworkService, HomeworkRepository, HomeworkPolicy, and module routes.

## Database Tables

- homework
- class_section
- subjects

## Models

- App\Modules\Homework\Models\Homework

## Controllers

- HomeworkController

## Services

- HomeworkService

## Routes

- /admin/homework
- /admin/homework/subjects/by-class

## Policies

- HomeworkPolicy

## Permissions

- homework.view
- homework.create
- homework.update
- homework.delete

## Business Rules

- Homework is assigned within the active school context.
- Teachers and admins can create or modify assignments depending on permissions.

## Workflow

1. An assignment is created for a class/section and subject.
2. The homework record is stored.
3. The assignment is available to relevant roles and reports.

## Common Issues

- Class-subject lookups may fail if the academic data is incomplete.
- Permission errors can surface when write actions are attempted without access.

## Troubleshooting

- Confirm the class section and subject exist.
- Validate the user can access homework permissions.
