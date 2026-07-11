# Notifications Module

Version: 1.0.0

Revision date: 2026-07-08

## Purpose

The notifications module provides in-app notification management, read-state handling, dashboard insights, and notification dispatch workflows.

## Architecture

The module uses NotificationController, NotificationService, NotificationRepository, NotificationPolicy, and route definitions.

## Database Tables

- notifications
- notification_user

## Models

- App\Modules\Notifications\Models\Notification

## Controllers

- NotificationController

## Services

- NotificationService

## Routes

- /notifications/bell
- /notifications/mark-read
- /notifications/mark-all-read
- /admin/notifications

## Policies

- NotificationPolicy

## Permissions

- notifications.view
- notifications.create
- notifications.update
- notifications.delete

## Business Rules

- Notifications are linked to users and can be marked as read.
- Notification operations are permission-aware.

## Workflow

1. A notification is created or triggered.
2. The target users receive the notification.
3. Users can read or clear notifications from the UI.

## Common Issues

- Notifications may not appear if the user is not linked through the notification_user table.
- Permission errors can block notification management actions.

## Troubleshooting

- Check the relevant notification record and user association.
- Verify the current role has the required notification permissions.
