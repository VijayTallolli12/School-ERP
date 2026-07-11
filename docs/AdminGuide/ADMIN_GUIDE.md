# Admin Guide

Version: 1.0.0

Revision date: 2026-07-08

## 1. Installation and Setup

Follow the developer guide for local installation. In production, deploy the Laravel application, configure the environment, and ensure the queue, storage, and database services are healthy.

## 2. School Setup

Admin users configure schools, academic years, classes, sections, subjects, and roles before daily operations begin.

## 3. Academic Configuration

- Create academic years.
- Create classes and sections.
- Add subjects and assign them where needed.

## 4. User Management

- Create users.
- Assign roles and permissions.
- Link users to the correct school context.

## 5. Teachers and Students

- Register teacher profiles.
- Register student profiles.
- Link guardian records where appropriate.

## 6. Fees, Payroll, Library, and Transport

Administrative users configure these modules and monitor operational workflows.

## 7. Backup and Restore

Maintain regular database and file-system backups. The deployment guide should be used for backup scheduling and rollback procedures.

## 8. Maintenance

- Clear caches when changing configuration.
- Review logs and notifications for operational issues.
- Validate permissions after role changes.
