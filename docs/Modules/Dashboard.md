# Dashboard Module

Version: 1.0.0

Revision date: 2026-07-08

## Purpose

The dashboard module provides role-based landing pages and sidebar navigation for administrators, teachers, students, parents, HR, accountants, librarians, receptionists, principals, and staff.

## Architecture

DashboardService builds a dashboard view from DashboardFactory and the appropriate role-specific builder. SidebarBuilder generates permission-aware menu structure.

## Database Tables

The dashboard consumes data from the underlying module tables including attendance, fees, exams, homework, notifications, students, and teachers.

## Models

The dashboard uses the current user and school context rather than a dedicated dashboard model.

## Controllers

- DashboardController

## Services

- DashboardService
- DashboardFactory
- SidebarBuilder

## Builders

- AdminDashboardBuilder
- TeacherDashboardBuilder
- StudentDashboardBuilder
- ParentDashboardBuilder
- PrincipalDashboardBuilder
- HRDashboardBuilder
- AccountantDashboardBuilder
- LibrarianDashboardBuilder
- ReceptionistDashboardBuilder
- StaffDashboardBuilder

## Permissions

- dashboard.view

## Business Rules

- Dashboard content is role-based and school-context aware.
- The sidebar only shows navigation items that the current user can access.

## Workflow

1. User authenticates and school context is resolved.
2. DashboardService selects the appropriate builder.
3. The builder creates the dashboard view payload.
4. The dashboard view is rendered for the user.

## Common Issues

- 403 errors may appear if the role cannot access a dashboard.
- Missing school context can cause dashboard data to be incomplete.

## Troubleshooting

- Verify the user has the appropriate role.
- Confirm school context is resolved for the current request.
