# Security Guide

Version: 1.0.0

Revision date: 2026-07-08

## 1. Authentication

Authentication is handled through the Laravel auth system and the custom login request flow. Login attempts are throttled, and login activity is recorded.

## 2. Authorization and RBAC

Permissions and roles are implemented with Spatie Permission. Role-aware routes and policies enforce access control.

## 3. Multi-school Isolation

The SchoolContext middleware and PermissionRegistrar team ID help isolate data by school context. Requests must resolve the correct school ID before actions are executed.

## 4. Common Web Security Controls

- CSRF protection is expected through the Laravel framework.
- Input validation is performed through FormRequest classes.
- File uploads and document workflows should be reviewed for storage and permission restrictions before broad production use.

## 5. Audit Logging

Activity logging and login activity logging are implemented to help track changes and access events.

## 6. AI Security

The AI subsystem performs role checks and logs actions. Sensitive or destructive intents may require confirmation and should be reviewed in logs.

## 7. Deployment Security

Use HTTPS, strong secrets, correct file permissions, and minimal service accounts for production deployment.
