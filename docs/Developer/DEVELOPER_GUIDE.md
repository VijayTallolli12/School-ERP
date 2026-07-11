# Developer Guide

Version: 1.0.0

Revision date: 2026-07-08

## 1. Environment Setup

### Prerequisites

- PHP 8.3+
- Composer
- Node.js and npm
- A supported database (SQLite is used by default in local development)

### Local Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

## 2. Configuration

Primary configuration files are under config/. Important settings include:

- database connection settings in .env
- services configuration for AI integrations
- app timezone and locale
- file storage locations

## 3. Coding Standards

- Follow Laravel conventions and PSR-12 style.
- Keep controllers thin and place business logic in services.
- Use repositories for data access when the logic becomes complex.
- Protect module routes with permissions.
- Use policies for resource authorization.
- Use services for module orchestration.

## 4. Folder Structure Conventions

```text
app/Modules/<ModuleName>/
  Controllers/
  Services/
  Repositories/
  Policies/
  Models/
  Requests/
  Resources/
```

## 5. Creating a New Module

1. Create the module folder under app/Modules.
2. Add route definitions under routes/modules.
3. Register the route file from routes/web.php or routes/api.php.
4. Add a controller, service, policy, repository, and model as needed.
5. Add migration(s) under database/migrations.
6. Add permissions and role wiring in the RBAC layer.
7. Add tests under tests/Feature or tests/Unit.

## 6. Creating Controllers

Controllers should be lightweight and delegate to services. Route actions should remain small and focused on request handling.

## 7. Creating Services

Services contain module business logic and orchestrate complex flows. Where data access is repeated, move it into a repository.

## 8. Creating Policies

Policies should evaluate user capability based on role and resource context. Use them from controllers or route middleware where appropriate.

## 9. Creating Builders and Collectors

- Builders create dashboard or UI structures.
- Collectors gather role-based dashboard data.
- Keep them deterministic and permission-aware.

## 10. Adding Permissions

Permissions are registered and enforced through Spatie Permission middleware and policy checks. New permissions should be added in the relevant module and exposed through the RBAC management UI.

## 11. Testing

Use the Laravel test suite and feature tests for routing and workflow validation.

```bash
php artisan test
```

## 12. Debugging and Logging

- Use Laravel log channels for application errors.
- Inspect AI execution and intent resolution logs where needed.
- Use the existing debug helpers in the project root when tracing routes or context issues.

## 13. Performance Guidelines

- Use eager loading where relationships are accessed repeatedly.
- Keep data-table endpoints efficient and filtered by school context.
- Avoid N+1 queries in list views and reports.

## 14. Common Mistakes

- Forgetting to set school context before using role/permission checks.
- Bypassing policy checks in controllers.
- Adding business logic directly into controllers.
- Adding routes without permission guards.
