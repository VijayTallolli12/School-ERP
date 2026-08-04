# Business Rule Report — Supporting Roles (Phase 07)

## Accountant

| Rule | Implementation | Reference |
|------|----------------|-----------|
| Accountant sees today's fee collection amount | `FeePayment::whereDate('payment_date', today())->sum('amount')` rendered as `₹` formatted card | `AccountantDashboardBuilder.php:26` |
| Accountant sees total collected fees (all time) | `FeeCollector::dashboardStats($schoolId)['total_collected']` | `AccountantDashboardBuilder.php:24` |
| Accountant sees count of pending fees | `StudentFee::where('status', 'pending')->count()` | `AccountantDashboardBuilder.php:27` |
| Accountant sees count of overdue fees | `StudentFee::where('status', 'overdue')->count()` | `AccountantDashboardBuilder.php:28` |
| Accountant has quick action to collect fee | Links to `admin.fees.index` | `AccountantDashboardBuilder.php:46` |
| Accountant has quick action for fee reports | Links to `admin.fees.reports` | `AccountantDashboardBuilder.php:47` |

## Librarian

| Rule | Implementation | Reference |
|------|----------------|-----------|
| Librarian sees total book count | `Book::count()` | `LibrarianDashboardBuilder.php:22` |
| Librarian sees currently issued book count | `BookIssue::whereNull('returned_at')->count()` | `LibrarianDashboardBuilder.php:23` |
| Librarian sees overdue book count | `BookIssue::whereNull('returned_at')->where('due_date', '<', now())->count()` | `LibrarianDashboardBuilder.php:24` |
| Librarian sees available book count | Computed as `Total − Issued` | `LibrarianDashboardBuilder.php:25` |
| Librarian has quick action to manage books | Links to `admin.library.index` | `LibrarianDashboardBuilder.php:43` |
| Librarian has quick action to issue a book | Links to `admin.library.index` | `LibrarianDashboardBuilder.php:44` |

## Receptionist

| Rule | Implementation | Reference |
|------|----------------|-----------|
| Receptionist sees total student count | `Student::count()` | `ReceptionistDashboardBuilder.php:21` |
| Receptionist sees count of students registered today | `Student::whereDate('created_at', today())->count()` | `ReceptionistDashboardBuilder.php:22` |
| Receptionist has quick action to add student | Links to `admin.students.index` | `ReceptionistDashboardBuilder.php:38` |
| Receptionist has quick action to add parent | Links to `admin.parents.index` | `ReceptionistDashboardBuilder.php:39` |

## Staff (unchanged, included for completeness)

| Rule | Implementation |
|------|----------------|
| Staff sees today's schedule count | `CalendarCollector::todaySchedulesCount($schoolId)` |
| Staff sees today's attendance rate | `AttendanceCollector::todayAttendanceRate($schoolId)` |
| Staff sees pending leave request count | `LeaveRequest::where('status', 'pending')->count()` |
| Staff sees active class count | `CalendarCollector::activeClassCount($schoolId)` |
| Staff has quick action for attendance, leave, timetable | Links to respective module routes |
