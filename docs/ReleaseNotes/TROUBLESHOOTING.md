# Troubleshooting

Version: 1.0.0

Revision date: 2026-07-08

## 1. Common Errors

### 403 Forbidden

- Check the active school context.
- Verify the user has the relevant role and permissions.

### 404 Not Found

- Verify the route and resource identifier.
- Confirm the resource exists for the active school context.

### 500 Internal Server Error

- Review Laravel logs.
- Check application configuration and database connectivity.

## 2. Permission Issues

- Verify role assignments.
- Confirm that the permission middleware is enabled for the route.

## 3. School Context Issues

- Ensure the school context is resolved from request, session, or user assignment data.
- Check the school_id or X-School-Id values.

## 4. Queue, Scheduler, and Cache

- Verify queue workers are running.
- Review scheduled jobs and Laravel caches after configuration changes.

## 5. Migration and Seeder Issues

- Run migrations in the correct order.
- Review the latest migration files for schema changes.

## 6. AI Issues

- Confirm the AI service configuration and intent resolution path.
- Review AI query logs for failed executions.

## 7. Performance Issues

- Check for N+1 queries in report and data-table endpoints.
- Review indexes and search-related migrations for large datasets.
