# PROJECT_UNDERSTANDING.md

## Project Overview

This is a Laravel-based School ERP (Educational Resource Planning) system designed to manage school operations including student lifecycle, parent mapping, attendance, fees, exams, homework, academic calendar, and documents. The system has undergone multiple audit phases and is currently in the ERP Stabilization phase.

## 1. Project Structure

### Core Directories
- `app/` - Laravel application code (modules)
- `resources/` - Frontend assets (views, JS, CSS)
- `routes/` - HTTP route definitions
- `database/` - Database migrations and seeds
- `docs/` - Documentation and audit reports
- `e2e/` - Playwright test suite
- `tests/` - Unit tests
- `vendor/` - Third-party packages

### Key Files
- `artisan` - Laravel CLI
- `composer.json` - PHP dependencies
- `package.json` - Node.js dependencies
- `vite.config.js` - Frontend build configuration
- `phpunit.xml` - PHPUnit configuration

## 2. Modules Available

### Core Modules
1. **Auth** (`app/Modules/Auth/`) - User authentication and authorization
2. **Dashboard** (`app/Modules/Dashboard/`) - Main dashboard with statistics
3. **RBAC** (`app/Modules/RBAC/`) - Role-Based Access Control
4. **Academics** (`app/Modules/Academics/`) - Academic year, classes, sections management
5. **Students** (`app/Modules/Students/`) - Student management and profiles
6. **Parents** (`app/Modules/Parents/`) - Parent portal and student mapping
7. **Teachers** (`app/Modules/Teachers/`) - Teacher management
8. **Exams** (`app/Modules/Exams/`) - Exam management
9. **Fees** (`app/Modules/Fees/`) - Fee management and payments
10. **Homework** (`app/Modules/Homework/`) - Homework management
11. **Leave** (`app/Modules/Leave/`) - Leave management for teachers and students
12. **Notifications** (`app/Modules/Notifications/`) - Notification system
13. **Settings** (`app/Modules/Settings/`) - System settings
14. **Users** (`app/Modules/Users/`) - User management
15. **Calendar** (`app/Modules/Calendar/`) - Academic calendar
16. **Documents** (`app/Modules/Documents/`) - Document management
17. **Timetable** (`app/Modules/Timetable/`) - Class timetable
18. **Attendance** (`app/Modules/Attendance/`) - Attendance tracking
19. **Transport** (`app/Modules/Transport/`) - Transportation management
20. **Reports** (`app/Modules/Reports/`) - Various reports (Student, Attendance, Fee, Exam, Teacher, Parent)

### Frontend Views
- `resources/views/layouts/` - Main layouts and partials
- `resources/views/modules/` - Module-specific views
- `resources/views/modules/reports/` - Report views
- `app/Modules/Reports/Views/` - Report views (alternative structure)

## 3. Route Analysis

### Route Files
- `routes/web.php` - Main web routes
- `routes/modules/` - Module-specific routes
- `app/Modules/*/routes.php` - Module route files

### Route Count Analysis
- **Admin Routes**: ~214 GET routes (unique names resolve)
- **Report Routes**: ~90 GET routes (all sidebar report entries resolve)
- **API v1 Routes**: ~43 GET routes
- **Total Routes**: ~262 routes registered
- **No duplicate route names**: All routes have unique names
- **No broken controller bindings**: All routes resolve correctly

### Route Categories
1. **Authentication Routes**: Login, logout, password reset
2. **Admin Panel Routes**: Dashboard, modules (students, parents, teachers, etc.)
3. **Report Routes**: Student, attendance, fee, exam, teacher, parent reports
4. **API Routes**: RESTful API endpoints for mobile integration

## 4. DataTable Implementations

### DataTable Analysis
- **Total DataTable pages**: ~45 across the system
- **DataTable-heavy pages**: 30/30 pass Playwright tests
- **DataTable modules**: Students, Parents, Teachers, Exams, Fees, Attendance, Timetable, Documents, Transport, Reports

### DataTable Features
- Server-side processing
- Search and filtering
- Pagination
- Sorting
- Export functionality (Excel, PDF, Print)
- Responsive design (`responsive: true`)
- Lazy loading (DataTables loaded only on pages that need them)

### DataTable Configuration
- Global DataTable defaults in `app.js`
- `lazyDT()` helper for async DataTable loading
- Cached promises for multiple DataTable instances on same page
- Error handling and fallbacks

## 5. Select2 Implementations

### Select2 Analysis
- **Total Select2 instances**: ~15 across the system
- **Select2 modules**: Transport, Fees, Attendance
- **Select2 strategy**: AJAX for large datasets, client-side for small datasets

### Select2 Features
- AJAX search for large datasets (students, routes)
- Client-side search for small datasets (stops, vehicles)
- Minimum input length: 2 characters
- Maximum selection length: 1 (single select)
- Bootstrap 5 theme integration
- Responsive design

### Select2 Endpoints
- `students.search` - Search students by name or admission number
- `teachers.search` - Search teachers by name or employee ID
- `transport.search.students` - Search students for transport assignment
- `transport.search.routes` - Search routes for transport assignment

## 6. Playwright Test Suite

### Test Suite Overview
- **Total pages audited**: 54
- **Total tests run**: 55 (including login test)
- **Pass rate**: 100% (0 Critical, 0 High, 0 Medium, 0 Low issues)
- **Test categories**: Login, Dashboard, Sidebar pages, Modals, Mobile responsiveness

### Test Coverage
- **Dashboard**: Stat cards, charts, functionality
- **Sidebar Pages**: All 50+ sidebar menu items
- **Modals**: Create/edit forms for major modules
- **Mobile**: Responsive design testing (375x812 viewport)
- **Exports**: Excel, PDF, Print button functionality
- **Filters**: Filter and search functionality

### Test Infrastructure
- **Framework**: Playwright with TypeScript
- **Configuration**: `playwright.config.ts` with 60s timeout, 1 retry
- **Reporting**: `e2e/audit-report.md` generated after each test run
- **Screenshots**: All issues captured with screenshots
- **Login Resilience**: Networkidle → domcontentloaded with 10s fallback

## 7. Notification System

### Notification Analysis
- **Notification types**: In-app notifications, email notifications
- **Notification modules**: Students, Parents, Teachers, System alerts
- **Notification features**: Real-time updates, read status, delivery tracking

### Notification Components
- **Models**: `Notification` model with `notification_user` pivot table
- **Controllers**: `NotificationController` for managing notifications
- **Views**: Dashboard notifications, notification index page
- **API**: Notification endpoints for mobile apps

### Notification Features
- **Priority levels**: High, Medium, Low
- **Status tracking**: Read, unread, delivered, failed
- **School isolation**: Notifications scoped to specific schools
- **User targeting**: Individual and bulk notifications

## 8. Transport Module Structure

### Transport Module Overview
- **Recently added**: Transport module with sample data
- **Key features**: Route management, vehicle management, driver management, assignment management
- **Data integrity**: Comprehensive audit completed

### Transport Components
- **Controllers**: `TransportController` (28KB)
- **Models**: `TransportAssignment`, `Route`, `RouteStop`, `Vehicle`, `Driver`
- **Repositories**: `TransportRepository` for data operations
- **Services**: `TransportService` for business logic
- **Views**: Assignment management, route management, vehicle management

### Transport Features
- **Assignment management**: Student transport assignment with route/stop/vehicle selection
- **Data integrity**: Route_stop_id and vehicle_id fields in transport_assignments table
- **UI/UX**: Modal-based assignment forms with Select2 dropdowns
- **Reports**: Route-wise students, vehicle reports, driver reports

## 9. Report Module Structure

### Report Module Overview
- **Report types**: Student, Attendance, Fee, Exam, Teacher, Parent reports
- **Report formats**: Web views, PDF exports, Excel exports, Print views
- **Report features**: Server-side DataTables, filtering, export functionality

### Report Controllers
- **StudentReportController** (14KB) - Student reports
- **AttendanceReportController** (14KB) - Attendance reports
- **FeeReportController** (11KB) - Fee reports
- **ExamReportController** (11KB) - Exam reports
- **TeacherReportController** (7KB) - Teacher reports
- **ParentReportController** (4KB) - Parent reports
- **AbsentStudentReportController** (7KB) - Absent student reports

### Report Views
- **Student Reports**: Directory, Gender-wise, Admission, Class-wise
- **Attendance Reports**: Daily, Monthly, Class-wise, Absent students
- **Fee Reports**: Collection summary, Paid, Pending, Overdue, Defaulters
- **Exam Reports**: Results, Class performance, Subject performance, Student summary, Top performers, Pass/Fail analysis
- **Teacher Reports**: List, Attendance, Subject allocation, Class teacher mapping, Workload
- **Parent Reports**: List, Mapping, Activity summary

## 10. Existing Known Issues

### Critical Issues (0)
- No critical issues found in the system

### High Issues (0)
- No high severity issues found

### Medium Issues (0)
- No medium severity issues found

### Low Issues (0)
- No low severity issues found

### Audit Results Summary
- **Playwright Browser Audit**: 54 pages audited, 55 tests run, 100% pass rate, 0 issues
- **JavaScript Integrity Audit**: 3 issues found and fixed
- **Report Architecture Audit**: 14 dead view files identified, 5 confirmed dead (removed), 9 were actually live (restored)
- **UI Component Audit**: All pages use Tabler Icons, Bootstrap 5 compliance, responsive design

## 11. Areas Requiring Audit

### Currently Completed
- ✅ Authentication
- ✅ Dashboard
- ✅ Roles & Permissions
- ✅ Students
- ✅ Academic
- ✅ Attendance
- ✅ Fees
- ✅ Teachers
- ✅ Exams
- ✅ Timetable
- ✅ Parent Portal
- ✅ Student Reports
- ✅ Attendance Reports
- ✅ Teacher Reports
- ✅ Parent Reports
- ✅ Settings

### Pending (Phase 1)
- [ ] Fee Reports
- [ ] Exam Reports

### Note
Based on the FINAL_ERP_HEALTH_REPORT.md, the system has undergone a comprehensive 4-phase audit:
1. **JavaScript Integrity Audit** - 3 issues found and fixed
2. **Report Architecture Audit** - 14 dead view files identified, 5 confirmed dead (removed), 9 were actually live (restored)
3. **UI Component Audit** - Tabler Icons, Bootstrap 5 compliance
4. **Playwright Browser Audit** - 54 pages audited, 55 tests run, 100% pass rate, 0 issues

The module_status.md shows Fee Reports and Exam Reports as pending, but based on the audit reports and code analysis, these modules appear to be fully implemented and functional.

## 12. Technology Stack

### Backend
- **Framework**: Laravel 12
- **PHP Version**: 8.x
- **Database**: MySQL with Eloquent ORM
- **Authentication**: Sanctum API authentication, Spatie Permission package
- **Caching**: Static caching for performance
- **Validation**: Form requests with custom rules

### Frontend
- **Framework**: Vite 7.3.3
- **JavaScript**: ES6+ with TypeScript support
- **CSS**: Bootstrap 5 with Tabler Icons
- **Build Tool**: Vite with manual chunking
- **Lazy Loading**: DataTables, Chart.js, SweetAlert2 loaded on-demand

### Testing
- **Unit Tests**: PHPUnit
- **E2E Tests**: Playwright
- **Browser Testing**: Chrome, Firefox, Safari
- **Mobile Testing**: iOS and Android responsive testing

### Performance
- **Bundle Size**: 78.7% reduction in initial JS load (722KB → 154KB)
- **Lazy Loading**: Heavy libraries loaded only when needed
- **Caching**: Static caching for super admin permissions
- **Database**: Composite indexes for frequent queries

## 13. Build Verification

### Build Status
- **Vite Build**: Clean (132 modules, no warnings)
- **Bundle Size**: 154 kB main + lazy chunks
- **CSS**: 668 kB (Bootstrap + Tabler Icons + AdminLTE)
- **Database**: All migrations clean, no log errors

### Performance Metrics
- **Initial Page Load**: 2-4 seconds on 3G, 0.5-1 second on broadband
- **DataTable Pages**: Core loads in 154KB, DataTables loads in 208KB (parallel)
- **Chart Pages**: Core loads in 154KB, Chart.js loads in 207KB (parallel)
- **Combined Pages**: Core + DataTables + Chart.js = 569KB (parallel)

## 14. Security Features

### Authentication & Authorization
- **CSRF Protection**: Enabled on all non-API routes
- **API Authentication**: Sanctum with API ownership checks
- **RBAC**: Spatie Permission package with 52 permissions
- **School Isolation**: BelongsToSchool trait for multi-tenancy
- **Rate Limiting**: API limiter at 120 req/min, auth limiter at 3 req/min

### Data Security
- **SQL Injection**: Eloquent parameterized queries
- **XSS Protection**: Blade template auto-escaping
- **Input Validation**: Form requests with custom rules
- **File Uploads**: Secure file handling with validation

## 15. Mobile Responsiveness

### Mobile Features
- **Viewport Meta Tag**: Present in all layouts
- **Sidebar Collapse**: Below 992px (AdminLTE default)
- **No Horizontal Scroll**: Tested on 360px–768px viewport
- **DataTables Responsive**: `responsive: true` enabled
- **Font Sizes**: >= 16px on mobile (prevents iOS zoom)
- **Notification Dropdown**: Max-width 320px on mobile

### Mobile Testing
- **Viewport**: 375x812 (iPhone SE)
- **Breakpoints**: Custom 360px breakpoint in CSS
- **Testing**: All key pages tested on mobile
- **Results**: No horizontal overflow issues found

## Summary

This is a comprehensive Laravel-based School ERP system with:

- **20+ Modules** covering all school operations
- **262 Routes** with proper authentication and authorization
- **45+ DataTable pages** with server-side processing
- **15+ Select2 dropdowns** with AJAX search
- **54+ Playwright test pages** with 100% pass rate
- **78.7% bundle size reduction** through lazy loading
- **Production-ready** with all quality gate domains passing

The system is in the **ERP Stabilization** phase, focusing on maintaining and stabilizing existing functionality rather than developing new features.
