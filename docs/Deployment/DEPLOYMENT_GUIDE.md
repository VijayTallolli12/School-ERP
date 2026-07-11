# Deployment Guide

Version: 1.0.0

Revision date: 2026-07-08

## 1. Requirements

- PHP 8.3+
- Composer
- Node.js and npm
- Database server compatible with Laravel
- Queue worker support
- Web server with PHP-FPM support
- Storage write access

## 2. Runtime Components

The application relies on Laravel, Spatie permissions, Sanctum, DataTables, Excel, DomPDF, and image processing libraries.

## 3. Environment Variables

Set the application environment variables for database, cache, queue, mail, and AI services before deployment.

## 4. Production Checklist

- Install dependencies with Composer and npm.
- Generate the application key.
- Run migrations.
- Build front-end assets.
- Configure queue workers and scheduler.
- Ensure storage permissions are correct.
- Configure SSL and secure headers.

## 5. Backups and Rollback

- Schedule database backups.
- Backup application storage and uploaded files.
- Keep a rollback plan that restores the last known-good release and database backup.

## 6. Monitoring and Health Checks

Monitor queue processing, application logs, web server health, and database connectivity. Health checks should include application availability and critical module access.
