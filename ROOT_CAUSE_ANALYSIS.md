# ROOT_CAUSE_ANALYSIS.md

**Phase:** 1 — ERP Functional Stability & Critical Bug Elimination  
**Date:** 2026-06-18  
**Auditor:** Automated static analysis + Playwright browser audit (56 pages)

---

## Executive Summary

| Category | Issues Found | Root Causes Fixed | Remaining |
|----------|-------------|-------------------|-----------|
| Console / JS errors | 0 | 0 | **0** |
| DataTable errors | 0 | 0 (prior jQuery shim fix verified) | **0** |
| Route errors | 8 | 8 | **0** |
| Broken buttons | 1 (false positive) | 0 | **0** |
| Select2 issues | 0 | 0 | **0** |
| Playwright timeouts | 5 flaky tests | 1 (navigation wait strategy) | **0** |

---

## Issues Identified & Resolved

### 1. Parent Portal — Route Name Mismatch

| Field | Detail |
|-------|--------|
| **Page** | Parent Portal (dashboard, attendance, fees, homework, exam-results, timetable, notifications) |
| **Issue** | `Route [parent-portal.dashboard] not defined` when parent users log in or navigate portal |
| **Root Cause** | `routes/modules/parents.php` registers routes inside the `admin.` prefix group (`routes/web.php` line 14–16). Actual registered names are `admin.parent-portal.*`, but 8 Blade views and `LoginController` referenced `parent-portal.*` without the `admin.` prefix. |
| **Fix Applied** | Updated all `route('parent-portal.*')` calls to `route('admin.parent-portal.*')` |
| **Verification** | `php artisan route:list --name=admin.parent-portal` confirms 7 routes; grep confirms zero remaining `route('parent-portal.` references |

**Files modified:**
- `app/Modules/Auth/Controllers/LoginController.php`
- `resources/views/modules/parents/dashboard.blade.php`
- `resources/views/modules/parents/attendance.blade.php`
- `resources/views/modules/parents/fees.blade.php`
- `resources/views/modules/parents/homework.blade.php`
- `resources/views/modules/parents/exam_results.blade.php`
- `resources/views/modules/parents/notifications.blade.php`
- `resources/views/modules/parents/timetable.blade.php`

---

### 2. Teacher Attendance Report — Wrong Form Action Route

| Field | Detail |
|-------|--------|
| **Page** | Teacher Attendance Report (`/admin/teachers/reports/attendance`) |
| **Issue** | Filter form submits to non-existent route `admin.teachers.attendance.report` |
| **Root Cause** | Route registered as `admin.teachers.reports.attendance` in `routes/modules/teachers.php` line 31, but Blade used `admin.teachers.attendance.report` |
| **Fix Applied** | Changed form `action` to `route('admin.teachers.reports.attendance')` |
| **Verification** | `php artisan route:list --name=admin.teachers.reports.attendance` resolves; Playwright Reports > Teachers > Teacher Attendance passes |

**Files modified:**
- `resources/views/modules/teachers/reports/attendance.blade.php`

---

### 3. Playwright Audit — False Timeout Failures on DataTable-Heavy Pages

| Field | Detail |
|-------|--------|
| **Page** | Fees, Academic, Student Documents, Permissions, Exams |
| **Issue** | `TimeoutError: page.goto: Timeout 15000ms exceeded` waiting for `networkidle` |
| **Root Cause** | Pages with 4–7 concurrent server-side DataTable AJAX requests never reach `networkidle` within 15 s. This is a test harness issue, not an application bug. Pages loaded and functioned correctly on retry. |
| **Fix Applied** | Added `waitForPageSettle()` helper: `domcontentloaded` (30 s) → optional `networkidle` (10 s) fallback. Applied to sidebar page navigation and `analyzePage()`. |
| **Verification** | Re-run: **57/57 Playwright tests pass** with zero timeouts |

**Files modified:**
- `e2e/erp-audit.spec.ts`

---

### 4. Transport Module — Missing from Audit Scope

| Field | Detail |
|-------|--------|
| **Page** | Transport (`/admin/transport`), Transport Reports (`/admin/transport/reports`) |
| **Issue** | Transport module not included in Playwright `SIDEBAR_PAGES` array |
| **Root Cause** | Audit spec written before Transport module was added to sidebar |
| **Fix Applied** | Added Transport and Transport Reports to `SIDEBAR_PAGES` |
| **Verification** | Playwright tests 53–54 pass; 0 console errors, 0 network 4xx/5xx |

**Files modified:**
- `e2e/erp-audit.spec.ts`

---

## Verified — No Action Required

### DataTable Architecture (Prior Fix Confirmed)

| Field | Detail |
|-------|--------|
| **Issue** | `DataTable is not a function` / dual jQuery instances |
| **Root Cause** (historical) | npm jQuery bundled in datatables chunk vs CDN jQuery in `<head>` |
| **Fix** (already in place) | `resources/js/jquery-shim.js` + Vite alias; `lazyDT()` guards in `app.js` |
| **Verification** | All 45 DataTable pages use `await window.lazyDT()` before `.DataTable()`. Build output: datatables chunk 119.73 kB (no duplicate jQuery). Playwright: 0 DataTable JS errors across all audited pages. |

### Select2

| Field | Detail |
|-------|--------|
| **Pages checked** | Transport assignments, Attendance mark form, Fees collection, all `searchable-select` fields |
| **Finding** | `App.initSearchableSelects()` handles AJAX, modal `dropdownParent`, edit-mode option injection (Transport assignment edit) |
| **Verification** | Transport modal test passes; no Select2 console errors in audit |

### JavaScript Syntax

| Field | Detail |
|-------|--------|
| **Scope** | 162 Blade files with `@push('scripts')`, `resources/js/app.js` |
| **Finding** | No unclosed braces, no pages calling `.DataTable()` without `lazyDT()`, no `$ is not defined` errors |
| **Verification** | Playwright `pageerror` and `console.error` listeners: 0 Critical/High JS issues across 56 pages |

---

## False Positives (Not Bugs)

| Page | Reported Issue | Actual Cause |
|------|----------------|--------------|
| Modules > Fees | `href="#"` PDF button | `#collectionPdfBtn` has JS click handler (`$('#collectionPdfBtn').on('click', ...)`) that opens PDF via `window.open()`. Excluded from audit false-positive detector. |
| 8 report pages | DataTable empty state | No seed data in dev database — tables render correctly with "No records available" message |
| Login | "Login successful" logged as Low issue | Audit setup confirmation, not a defect |

---

## Module Audit Coverage

| Module | Pages Audited | Console Errors | Status |
|--------|--------------|----------------|--------|
| Dashboard | 1 | 0 | ✅ Pass |
| Notifications | 1 | 0 | ✅ Pass |
| Fees | 1 + 6 reports | 0 | ✅ Pass |
| Students | 1 + 4 reports | 0 | ✅ Pass |
| Parents | 1 + 4 reports | 0 | ✅ Pass |
| Teachers | 1 + 6 reports | 0 | ✅ Pass |
| Exams | 1 + 7 reports | 0 | ✅ Pass |
| Homework | 1 | 0 | ✅ Pass |
| Attendance | 1 + 5 reports | 0 | ✅ Pass |
| Academic | 1 | 0 | ✅ Pass |
| Transport | 2 | 0 | ✅ Pass |
| Reports (all) | 32 | 0 | ✅ Pass |
| Settings | 1 | 0 | ✅ Pass |
| Access Control | 2 | 0 | ✅ Pass |
| Calendar, Documents, Timetable, Users, Leave | 5 | 0 | ✅ Pass |

**Total pages audited:** 56  
**Total Playwright tests:** 57 (including modals + mobile)
