# Authentication Module

Version: 1.0.0

Revision date: 2026-07-08

## Purpose

The authentication module provides login, logout, password recovery, and school-context-aware session handling for users.

## Architecture

The module is implemented with dedicated controllers and request validation under app/Modules/Auth and wired through routes/modules/auth.php.

## Database Tables

- users
- login_activities
- schools
- school_user

## Models

- App\Models\User
- App\Models\School
- App\Modules\Auth\Services\LoginActivityService

## Controllers

- LoginController
- ForgotPasswordController
- ResetPasswordController
- ApiAuthController

## Services

- LoginActivityService

## Routes

- /login
- /logout
- /forgot-password
- /reset-password

## Business Rules

- Login is throttled after repeated failures.
- School context is applied before role-based routing and permission checks.
- Parent users are redirected to the parent portal dashboard after login.

## Workflow

1. User submits credentials.
2. LoginRequest validates the request.
3. Auth::attempt authenticates the user.
4. Login activity is recorded.
5. School context is applied.
6. User is redirected to the correct dashboard.

## Common Issues

- Missing school context can cause permission or role resolution failures.
- Invalid credentials trigger throttling.

## Troubleshooting

- Verify the database has a valid school record.
- Confirm the user has an active assignment in a school context.
