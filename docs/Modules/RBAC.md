# RBAC Module

Version: 1.0.0

Revision date: 2026-07-08

## Purpose

The RBAC module manages roles and permissions for the application.

## Architecture

Implemented through RoleController, PermissionController, RoleService, PermissionService, RoleRepository, PermissionRepository, and related policies.

## Database Tables

- roles
- permissions
- model_has_permissions
- model_has_roles
- role_has_permissions

## Controllers

- RoleController
- PermissionController

## Services

- RoleService
- PermissionService

## Routes

- /admin/roles
- /admin/permissions

## Policies

- RolePolicy
- PermissionPolicy

## Permissions

- roles.view
- roles.create
- roles.update
- roles.delete
- permissions.view
- permissions.create
- permissions.update
- permissions.delete

## Business Rules

- Roles and permissions are central to the application authorization model.
- Permissions are checked through middleware and policies.

## Workflow

1. Create or update a role.
2. Assign permissions to the role.
3. Assign the role to users and school contexts.

## Common Issues

- Access issues after role changes.
- Missing permission assignment for a user role.

## Troubleshooting

- Review the role and permission assignments in the admin UI.
- Confirm the user has the correct role and school context.
