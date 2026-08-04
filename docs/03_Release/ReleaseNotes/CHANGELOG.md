# Changelog

Version: 1.0.0

Revision date: 2026-07-08

## 1. Major Features

- Multi-school aware authentication and routing
- Modular Laravel ERP for academics, students, teachers, attendance, exams, homework, fees, payroll, library, transport, HR, notifications, reports, and AI
- Role-based dashboards and sidebar generation
- AI intent handling with role and school scoping

## 2. Architecture

- Introduced modular service and repository structure under app/Modules
- Added policy-based authorization patterns
- Added dashboard builder, collector, and factory pattern implementations

## 3. Security

- Added school-context middleware and permission-aware route guards
- Added login activity logging and AI query logging

## 4. Performance and UI

- Added index and search-related migrations for larger data sets
- Added dashboard builders and role-specific navigation experiences

## 5. AI and Business Workflows

- Added AI intent routing and executive-copilot planning services
- Added module workflows for attendance, exams, fees, library, transport, payroll, and notifications
