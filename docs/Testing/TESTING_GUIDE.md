# Testing Guide

Version: 1.0.0

Revision date: 2026-07-08

## 1. Testing Strategy

The repository includes Laravel tests and Playwright-based validations. Use automated tests for regression coverage and manual checks for workflow validation.

## 2. Unit Testing

Add unit tests for service and domain-level logic where appropriate.

## 3. Feature Testing

Use feature tests for routes, authentication, permissions, and module workflows.

## 4. Playwright

Playwright is configured for browser-based validations. Use it to validate login, dashboards, module navigation, and role-based access flows.

## 5. Regression Testing

Run regressions after changes to core modules such as authentication, fees, exams, attendance, and AI workflows.

## 6. Manual Testing Checklist

- Login and logout
- Dashboard rendering
- Student and teacher management
- Attendance recording
- Fee collection and receipts
- Payroll run generation
- Library operations
- Transport assignment flows
- AI assistant execution

## 7. Test Data and Seeders

Use the existing seeders and factories to create realistic test data. Keep test data isolated from production environments.
