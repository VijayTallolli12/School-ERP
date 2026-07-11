# School ERP Documentation

Version: 1.0.0

Revision date: 2026-07-08

This documentation set describes the current School ERP implementation in this repository. It is based on the live Laravel application structure, route definitions, controllers, services, middleware, policies, migrations, and supporting reports in the project.

## Documentation Map

- [Architecture](Architecture/SYSTEM_ARCHITECTURE.md)
- [Developer Guide](Developer/DEVELOPER_GUIDE.md)
- [API Reference](API/API_REFERENCE.md)
- [Database Schema](Database/DATABASE_SCHEMA.md)
- [Module Guides](Modules/)
- [User Guides](UserGuide/)
- [Admin Guide](AdminGuide/ADMIN_GUIDE.md)
- [Deployment Guide](Deployment/DEPLOYMENT_GUIDE.md)
- [Testing Guide](Testing/TESTING_GUIDE.md)
- [AI Guide](AI/AI_GUIDE.md)
- [Business Workflows](Business/BUSINESS_WORKFLOWS.md)
- [Security Guide](Security/SECURITY_GUIDE.md)
- [Troubleshooting & Changelog](ReleaseNotes/)

## Supported Roles

The product currently exposes role-based experiences for:

- Super Admin / School Admin
- Principal
- Teacher
- HR
- Accountant
- Librarian
- Receptionist
- Staff
- Parent
- Student

## System Overview

The application is a modular Laravel 12 ERP built around a multi-school context, role-based access control, and module-specific services. Core capabilities include authentication, student and teacher management, attendance, examinations, homework, fees, payroll, library, transport, notifications, calendar, documents, and AI-assisted workflows.

## Documentation Principles

- Documentation is aligned with implementation, not roadmap assumptions.
- Features described here are based on code and route definitions present in the repository.
- Screenshots placeholders are included where the UI is role-based and implementation-specific.

## Quick Start

1. Review the architecture overview.
2. Configure the environment using the developer guide.
3. Review the module documentation for the business area you support.
4. Use the admin and user guides for day-to-day operations.

---

## Revision History

| Version | Date | Summary |
| --- | --- | --- |
| 1.0.0 | 2026-07-08 | Initial implementation-aligned documentation set created. |
