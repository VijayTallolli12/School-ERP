<style>
/* Sidebar customizations to match reference design */
.app-sidebar {
    background-color: #ffffff !important;
    border-right: 1px solid rgba(0,0,0,0.05);
}
.sidebar-menu .nav-item {
    margin-bottom: 2px;
}
.sidebar-menu .nav-link {
    color: #4b5563 !important;
    font-weight: 500;
    padding: 10px 16px;
    border-radius: 8px;
    margin: 0 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}
.sidebar-menu .nav-link:hover {
    background-color: #f9fafb !important;
    color: #111827 !important;
}
.sidebar-menu .nav-link.active {
    background-color: var(--erp-primary-light, #f3f0ff) !important;
    color: var(--erp-primary, #7755CC) !important;
}
.sidebar-menu .nav-link.active i {
    color: var(--erp-primary, #7755CC) !important;
}
.sidebar-menu .nav-header {
    padding: 24px 16px 8px 24px;
}
.sidebar-menu .nav-header-label, .sidebar-menu .nav-header {
    color: #9ca3af !important;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.sidebar-brand {
    border-bottom: none !important;
    padding: 16px 20px;
}
.brand-text {
    color: #111827 !important;
    font-weight: 700 !important;
    font-size: 18px !important;
}
</style>
<aside class="app-sidebar bg-white shadow-sm border-0">
    <div class="sidebar-brand">
        <a href="{{ route('admin.dashboard') }}" class="brand-link d-flex align-items-center gap-2 text-decoration-none">
            @if($logo = setting('school_logo'))
                <img src="{{ $logo }}" alt="{{ setting('school_name', 'School ERP') }}" class="brand-image img-circle elevation-1" style="width:32px;height:32px;object-fit:cover;">
            @endif
            <span class="brand-text fw-semibold fs-6">{{ setting('school_name', 'School ERP') }}</span>
        </a>
    </div>
    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu">

            @if(auth()->user()->hasRole('Teacher'))

                {{-- ========== TEACHER SIDEBAR ========== --}}
                <li class="nav-header">
                    <span class="nav-header-label">Teacher</span>
                </li>

                @can('dashboard.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link @if(request()->routeIs('admin.dashboard')) active @endif">
                            <i class="nav-icon ti ti-gauge"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                @endcan

                @can('timetable.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.timetable.index') }}" class="nav-link @if(request()->routeIs('admin.timetable.*')) active @endif">
                            <i class="nav-icon ti ti-table"></i>
                            <p>My Timetable</p>
                        </a>
                    </li>
                @endcan

                @can('attendance.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.attendance.index') }}" class="nav-link @if(request()->routeIs('admin.attendance.*')) active @endif">
                            <i class="nav-icon ti ti-calendar-check"></i>
                            <p>Attendance</p>
                        </a>
                    </li>
                @endcan

                @can('homework.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.homework.index') }}" class="nav-link @if(request()->routeIs('admin.homework.*')) active @endif">
                            <i class="nav-icon ti ti-books"></i>
                            <p>Homework</p>
                        </a>
                    </li>
                @endcan

                @can('students.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.students.index') }}" class="nav-link @if(request()->routeIs('admin.students.*')) active @endif">
                            <i class="nav-icon ti ti-school"></i>
                            <p>My Students</p>
                        </a>
                    </li>
                @endcan

                @can('exams.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.exams.index') }}" class="nav-link @if(request()->routeIs('admin.exams.*')) active @endif">
                            <i class="nav-icon ti ti-chart-arrows-vertical"></i>
                            <p>Marks</p>
                        </a>
                    </li>
                @endcan

                <li class="nav-item">
                    <a href="{{ route('admin.leave-requests.index') }}" class="nav-link @if(request()->routeIs('admin.leave-requests.*')) active @endif">
                        <i class="nav-icon ti ti-calendar-minus"></i>
                        <p>Leave</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.teacher-documents.index') }}" class="nav-link @if(request()->routeIs('admin.teacher-documents.*')) active @endif">
                        <i class="nav-icon ti ti-file-text"></i>
                        <p>My Documents</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.payroll.payslips.my') }}" class="nav-link @if(request()->routeIs('admin.payroll.payslips.my')) active @endif">
                        <i class="nav-icon ti ti-cash"></i>
                        <p>My Payslips</p>
                    </a>
                </li>

                @can('notifications.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.notifications.index') }}" class="nav-link @if(request()->routeIs('admin.notifications.*')) active @endif">
                            <i class="nav-icon ti ti-bell"></i>
                            <p>Notifications</p>
                        </a>
                    </li>
                @endcan

                @can('academic_calendar.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.calendar.index') }}" class="nav-link @if(request()->routeIs('admin.calendar.*')) active @endif">
                            <i class="nav-icon ti ti-calendar-event"></i>
                            <p>Calendar</p>
                        </a>
                    </li>
                @endcan

                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#askErpModal">
                        <i class="nav-icon ti ti-message"></i>
                        <p>Ask ERP</p>
                    </a>
                </li>

            @elseif(auth()->user()->hasRole('HR'))

                {{-- ========== HR ========== --}}
                <li class="nav-header">
                    <span class="nav-header-label">HR</span>
                </li>

                @can('dashboard.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link @if(request()->routeIs('admin.dashboard')) active @endif">
                            <i class="nav-icon ti ti-gauge"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                @endcan

                @can('hr.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.hr.index') }}" class="nav-link @if(request()->routeIs('admin.hr.index')) active @endif">
                            <i class="nav-icon ti ti-users"></i>
                            <p>Employees</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.hr.documents.index') }}" class="nav-link @if(request()->routeIs('admin.hr.documents.*')) active @endif">
                            <i class="nav-icon ti ti-file-text"></i>
                            <p>Documents</p>
                        </a>
                    </li>
                @endcan

                @can('notifications.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.notifications.index') }}" class="nav-link @if(request()->routeIs('admin.notifications.*')) active @endif">
                            <i class="nav-icon ti ti-bell"></i>
                            <p>Notifications</p>
                        </a>
                    </li>
                @endcan

                {{-- ========== AI WORKSPACE ========== --}}
                <li class="nav-header">
                    <span class="nav-header-label">AI Workspace</span>
                </li>

                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#askErpModal">
                        <i class="nav-icon ti ti-message"></i>
                        <p>Ask ERP</p>
                    </a>
                </li>

            @elseif(auth()->user()->hasRole('Principal'))

                {{-- ========== PRINCIPAL ========== --}}
                <li class="nav-header">
                    <span class="nav-header-label">Principal</span>
                </li>

                @can('dashboard.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link @if(request()->routeIs('admin.dashboard')) active @endif">
                            <i class="nav-icon ti ti-gauge"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                @endcan

                @can('attendance.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.attendance.index') }}" class="nav-link @if(request()->routeIs('admin.attendance.*')) active @endif">
                            <i class="nav-icon ti ti-calendar-check"></i>
                            <p>Attendance</p>
                        </a>
                    </li>
                @endcan

                @can('timetable.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.timetable.index') }}" class="nav-link @if(request()->routeIs('admin.timetable.*')) active @endif">
                            <i class="nav-icon ti ti-table"></i>
                            <p>Timetable</p>
                        </a>
                    </li>
                @endcan

                @can('exams.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.exams.index') }}" class="nav-link @if(request()->routeIs('admin.exams.*')) active @endif">
                            <i class="nav-icon ti ti-chart-arrows-vertical"></i>
                            <p>Exams</p>
                        </a>
                    </li>
                @endcan

                @can('students.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.students.index') }}" class="nav-link @if(request()->routeIs('admin.students.*')) active @endif">
                            <i class="nav-icon ti ti-school"></i>
                            <p>Students</p>
                        </a>
                    </li>
                @endcan

                @can('student_lifecycle.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.lifecycle.index') }}" class="nav-link @if(request()->routeIs('admin.lifecycle.*')) active @endif">
                            <i class="nav-icon ti ti-arrows-left-right"></i>
                            <p>Student Lifecycle</p>
                        </a>
                    </li>
                @endcan

                @can('teachers.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.teachers.index') }}" class="nav-link @if(request()->routeIs('admin.teachers.*')) active @endif">
                            <i class="nav-icon ti ti-presentation"></i>
                            <p>Teachers</p>
                        </a>
                    </li>
                @endcan

                @can('homework.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.homework.index') }}" class="nav-link @if(request()->routeIs('admin.homework.*')) active @endif">
                            <i class="nav-icon ti ti-books"></i>
                            <p>Homework</p>
                        </a>
                    </li>
                @endcan

                @can('academic_calendar.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.calendar.index') }}" class="nav-link @if(request()->routeIs('admin.calendar.*')) active @endif">
                            <i class="nav-icon ti ti-calendar-event"></i>
                            <p>Calendar</p>
                        </a>
                    </li>
                @endcan

                @can('fees.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.fees.index') }}" class="nav-link @if(request()->routeIs('admin.fees.*')) active @endif">
                            <i class="nav-icon ti ti-receipt"></i>
                            <p>Fees</p>
                        </a>
                    </li>
                @endcan

                @can('reports.view')
                    <li class="nav-item">
                        <a href="{{ route('reports.attendance.index') }}" class="nav-link @if(request()->routeIs('reports.*')) active @endif">
                            <i class="nav-icon ti ti-chart-bar"></i>
                            <p>Reports</p>
                        </a>
                    </li>
                @endcan

                @can('leave_management.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.leave-requests.index') }}" class="nav-link @if(request()->routeIs('admin.leave-requests.*')) active @endif">
                            <i class="nav-icon ti ti-list-check"></i>
                            <p>Leave Approvals</p>
                        </a>
                    </li>
                @endcan

                @can('notifications.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.notifications.index') }}" class="nav-link @if(request()->routeIs('admin.notifications.*')) active @endif">
                            <i class="nav-icon ti ti-bell"></i>
                            <p>Notifications</p>
                        </a>
                    </li>
                @endcan

                {{-- ========== AI WORKSPACE ========== --}}
                <li class="nav-header">
                    <span class="nav-header-label">AI Workspace</span>
                </li>

                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#askErpModal">
                        <i class="nav-icon ti ti-message"></i>
                        <p>Ask ERP</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.ai.dashboard') }}" class="nav-link @if(request()->routeIs('admin.ai.dashboard')) active @endif">
                        <i class="nav-icon ti ti-sparkles"></i>
                        <p>Executive Gemini</p>
                    </a>
                </li>

            @elseif(auth()->user()->hasRole('Accountant'))

                {{-- ========== ACCOUNTANT ========== --}}
                <li class="nav-header">
                    <span class="nav-header-label">Finance</span>
                </li>

                @can('dashboard.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link @if(request()->routeIs('admin.dashboard')) active @endif">
                            <i class="nav-icon ti ti-gauge"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                @endcan

                @can('fees.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.fees.index') }}" class="nav-link @if(request()->routeIs('admin.fees.*')) active @endif">
                            <i class="nav-icon ti ti-receipt"></i>
                            <p>Fees</p>
                        </a>
                    </li>
                @endcan

                @can('transport.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.transport.index') }}" class="nav-link @if(request()->routeIs('admin.transport.*')) active @endif">
                            <i class="nav-icon ti ti-bus"></i>
                            <p>Transport</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.transport.sos.index') }}" class="nav-link @if(request()->routeIs('admin.transport.sos.*')) active @endif">
                            <i class="nav-icon ti ti-alert-octagon text-danger"></i>
                            <p>SOS Alerts</p>
                        </a>
                    </li>
                @endcan

                @can('fees.reports')
                    <li class="nav-item">
                        <a href="{{ route('reports.fees.index') }}" class="nav-link @if(request()->routeIs('reports.fees.*')) active @endif">
                            <i class="nav-icon ti ti-chart-bar"></i>
                            <p>Reports</p>
                        </a>
                    </li>
                @endcan

                @can('notifications.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.notifications.index') }}" class="nav-link @if(request()->routeIs('admin.notifications.*')) active @endif">
                            <i class="nav-icon ti ti-bell"></i>
                            <p>Notifications</p>
                        </a>
                    </li>
                @endcan

            @elseif(auth()->user()->hasRole('Librarian'))

                {{-- ========== LIBRARIAN ========== --}}
                <li class="nav-header">
                    <span class="nav-header-label">Library</span>
                </li>

                @can('dashboard.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link @if(request()->routeIs('admin.dashboard')) active @endif">
                            <i class="nav-icon ti ti-gauge"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                @endcan

                @can('library.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.library.index') }}" class="nav-link @if(request()->routeIs('admin.library.*')) active @endif">
                            <i class="nav-icon ti ti-books"></i>
                            <p>Library</p>
                        </a>
                    </li>
                @endcan

                @can('reports.view')
                    <li class="nav-item">
                        <a href="{{ route('reports.attendance.index') }}" class="nav-link @if(request()->routeIs('reports.*')) active @endif">
                            <i class="nav-icon ti ti-chart-bar"></i>
                            <p>Reports</p>
                        </a>
                    </li>
                @endcan

                @can('notifications.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.notifications.index') }}" class="nav-link @if(request()->routeIs('admin.notifications.*')) active @endif">
                            <i class="nav-icon ti ti-bell"></i>
                            <p>Notifications</p>
                        </a>
                    </li>
                @endcan

            @elseif(auth()->user()->hasRole('Receptionist'))

                {{-- ========== RECEPTIONIST ========== --}}
                <li class="nav-header">
                    <span class="nav-header-label">Reception</span>
                </li>

                @can('dashboard.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link @if(request()->routeIs('admin.dashboard')) active @endif">
                            <i class="nav-icon ti ti-gauge"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                @endcan

                @can('students.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.students.index') }}" class="nav-link @if(request()->routeIs('admin.students.*')) active @endif">
                            <i class="nav-icon ti ti-school"></i>
                            <p>Students</p>
                        </a>
                    </li>
                @endcan

                @can('parents.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.parents.index') }}" class="nav-link @if(request()->routeIs('admin.parents.*')) active @endif">
                            <i class="nav-icon ti ti-users"></i>
                            <p>Parents</p>
                        </a>
                    </li>
                @endcan

                @can('notifications.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.notifications.index') }}" class="nav-link @if(request()->routeIs('admin.notifications.*')) active @endif">
                            <i class="nav-icon ti ti-bell"></i>
                            <p>Notifications</p>
                        </a>
                    </li>
                @endcan

            @elseif(auth()->user()->hasRole('Staff'))

                {{-- ========== STAFF ========== --}}
                <li class="nav-header">
                    <span class="nav-header-label">Staff</span>
                </li>

                @can('dashboard.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link @if(request()->routeIs('admin.dashboard')) active @endif">
                            <i class="nav-icon ti ti-gauge"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                @endcan

                @can('timetable.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.timetable.index') }}" class="nav-link @if(request()->routeIs('admin.timetable.*')) active @endif">
                            <i class="nav-icon ti ti-table"></i>
                            <p>Timetable</p>
                        </a>
                    </li>
                @endcan

                @can('attendance.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.attendance.index') }}" class="nav-link @if(request()->routeIs('admin.attendance.*')) active @endif">
                            <i class="nav-icon ti ti-calendar-check"></i>
                            <p>Attendance</p>
                        </a>
                    </li>
                @endcan

                @can('notifications.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.notifications.index') }}" class="nav-link @if(request()->routeIs('admin.notifications.*')) active @endif">
                            <i class="nav-icon ti ti-bell"></i>
                            <p>Notifications</p>
                        </a>
                    </li>
                @endcan

            @elseif(auth()->user()->hasRole('Payroll Manager'))

                {{-- ========== PAYROLL MANAGER ========== --}}
                <li class="nav-header">
                    <span class="nav-header-label">Payroll</span>
                </li>

                @can('dashboard.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link @if(request()->routeIs('admin.dashboard')) active @endif">
                            <i class="nav-icon ti ti-gauge"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                @endcan

                @can('payroll.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.payroll.index') }}" class="nav-link @if(request()->routeIs('admin.payroll.*')) active @endif">
                            <i class="nav-icon ti ti-cash"></i>
                            <p>Payroll</p>
                        </a>
                    </li>
                @endcan

                @can('reports.view')
                    <li class="nav-item">
                        <a href="{{ route('reports.fees.index') }}" class="nav-link @if(request()->routeIs('reports.*')) active @endif">
                            <i class="nav-icon ti ti-chart-bar"></i>
                            <p>Reports</p>
                        </a>
                    </li>
                @endcan

                @can('notifications.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.notifications.index') }}" class="nav-link @if(request()->routeIs('admin.notifications.*')) active @endif">
                            <i class="nav-icon ti ti-bell"></i>
                            <p>Notifications</p>
                        </a>
                    </li>
                @endcan

            @elseif(auth()->user()->hasRole('Driver'))

                {{-- ========== DRIVER ========== --}}
                <li class="nav-header">
                    <span class="nav-header-label">Transport</span>
                </li>

                @can('dashboard.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link @if(request()->routeIs('admin.dashboard')) active @endif">
                            <i class="nav-icon ti ti-gauge"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                @endcan

                @can('transport.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.transport.index') }}" class="nav-link @if(request()->routeIs('admin.transport.*')) active @endif">
                            <i class="nav-icon ti ti-bus"></i>
                            <p>Transportation</p>
                        </a>
                    </li>
                @endcan

                @can('notifications.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.notifications.index') }}" class="nav-link @if(request()->routeIs('admin.notifications.*')) active @endif">
                            <i class="nav-icon ti ti-bell"></i>
                            <p>Notifications</p>
                        </a>
                    </li>
                @endcan

            @else

                {{-- ========== OPERATIONS ========== --}}
                <li class="nav-header">
                    <span class="nav-header-label">Operations</span>
                </li>

                @can('dashboard.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link @if(request()->routeIs('admin.dashboard')) active @endif">
                            <i class="nav-icon ti ti-gauge"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                @endcan

                @can('admissions.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.admissions.index') }}" class="nav-link @if(request()->routeIs('admin.admissions.*')) active @endif">
                            <i class="nav-icon ti ti-clipboard-list"></i>
                            <p>Admissions</p>
                        </a>
                    </li>
                @endcan

                @can('attendance.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.attendance.index') }}" class="nav-link @if(request()->routeIs('admin.attendance.*')) active @endif">
                            <i class="nav-icon ti ti-calendar-check"></i>
                            <p>Attendance</p>
                        </a>
                    </li>
                @endcan

                @can('timetable.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.timetable.index') }}" class="nav-link @if(request()->routeIs('admin.timetable.*')) active @endif">
                            <i class="nav-icon ti ti-table"></i>
                            <p>Timetable</p>
                        </a>
                    </li>
                @endcan

                @can('academic_calendar.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.calendar.index') }}" class="nav-link @if(request()->routeIs('admin.calendar.*')) active @endif">
                            <i class="nav-icon ti ti-calendar-event"></i>
                            <p>Event Calendar</p>
                        </a>
                    </li>
                @endcan



                @can('transport.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.transport.index') }}" class="nav-link @if(request()->routeIs('admin.transport.*')) active @endif">
                            <i class="nav-icon ti ti-bus"></i>
                            <p>Transportation</p>
                        </a>
                    </li>
                @endcan

                {{-- ========== ACADEMICS ========== --}}
                <li class="nav-header">
                    <span class="nav-header-label">Academics</span>
                </li>

                @can('students.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.students.index') }}" class="nav-link @if(request()->routeIs('admin.students.*')) active @endif">
                            <i class="nav-icon ti ti-school"></i>
                            <p>Students</p>
                        </a>
                    </li>
                @endcan

                @can('parents.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.parents.index') }}" class="nav-link @if(request()->routeIs('admin.parents.*')) active @endif">
                            <i class="nav-icon ti ti-users"></i>
                            <p>Parents</p>
                        </a>
                    </li>
                @endcan

                @can('teachers.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.teachers.index') }}" class="nav-link @if(request()->routeIs('admin.teachers.*')) active @endif">
                            <i class="nav-icon ti ti-presentation"></i>
                            <p>Teachers</p>
                        </a>
                    </li>
                @endcan

                @can('exams.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.exams.index') }}" class="nav-link @if(request()->routeIs('admin.exams.*')) active @endif">
                            <i class="nav-icon ti ti-chart-arrows-vertical"></i>
                            <p>Exams</p>
                        </a>
                    </li>
                @endcan

                @can('homework.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.homework.index') }}" class="nav-link @if(request()->routeIs('admin.homework.*')) active @endif">
                            <i class="nav-icon ti ti-books"></i>
                            <p>Homework</p>
                        </a>
                    </li>
                @endcan

                @can('academics.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.academics.index') }}" class="nav-link @if(request()->routeIs('admin.academics.*')) active @endif">
                            <i class="nav-icon ti ti-book-2"></i>
                            <p>Academic</p>
                        </a>
                    </li>
                @endcan

                @can('student_lifecycle.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.lifecycle.index') }}" class="nav-link @if(request()->routeIs('admin.lifecycle.*')) active @endif">
                            <i class="nav-icon ti ti-arrows-left-right"></i>
                            <p>Student Lifecycle</p>
                        </a>
                    </li>
                @endcan

                @can('library.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.library.index') }}" class="nav-link @if(request()->routeIs('admin.library.*')) active @endif">
                            <i class="nav-icon ti ti-books"></i>
                            <p>Library</p>
                        </a>
                    </li>
                @endcan

                {{-- ========== FINANCE ========== --}}
                <li class="nav-header">
                    <span class="nav-header-label">Finance</span>
                </li>

                @can('fees.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.fees.index') }}" class="nav-link @if(request()->routeIs('admin.fees.*')) active @endif">
                            <i class="nav-icon ti ti-receipt"></i>
                            <p>Fees</p>
                        </a>
                    </li>
                @endcan

                @can('payroll.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.payroll.index') }}" class="nav-link @if(request()->routeIs('admin.payroll.*')) active @endif">
                            <i class="nav-icon ti ti-cash"></i>
                            <p>Payroll</p>
                        </a>
                    </li>
                @endcan

                {{-- ========== COMMUNICATION ========== --}}
                @can('notifications.view')
                    <li class="nav-header">
                        <span class="nav-header-label">Communication</span>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.notifications.index') }}" class="nav-link @if(request()->routeIs('admin.notifications.*')) active @endif">
                            <i class="nav-icon ti ti-bell"></i>
                            <p>Notifications</p>
                        </a>
                    </li>
                @endcan

                {{-- ========== AI WORKSPACE ========== --}}
                <li class="nav-header">
                    <span class="nav-header-label">AI Workspace</span>
                </li>

                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#askErpModal">
                        <i class="nav-icon ti ti-message"></i>
                        <p>Ask ERP</p>
                    </a>
                </li>

                @role('Super Admin|School Admin|Principal|HR')
                    <li class="nav-item">
                        <a href="{{ route('admin.ai.dashboard') }}" class="nav-link @if(request()->routeIs('admin.ai.dashboard')) active @endif">
                            <i class="nav-icon ti ti-sparkles"></i>
                            <p>Executive Gemini</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.agents.index') }}" class="nav-link @if(request()->routeIs('admin.agents.index') && !request()->routeIs('admin.agents.history')) active @endif">
                            <i class="nav-icon ti ti-robot"></i>
                            <p>AI Agents</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.agents.history') }}" class="nav-link @if(request()->routeIs('admin.agents.history*')) active @endif">
                            <i class="nav-icon ti ti-clock"></i>
                            <p>Execution History</p>
                        </a>
                    </li>
                @endrole

                {{-- ========== ADMINISTRATION ========== --}}
                <li class="nav-header">
                    <span class="nav-header-label">Administration</span>
                </li>



                @can('leave_management.view')
                    <li class="nav-item @if(request()->routeIs('admin.leave-*')) menu-open @endif">
                        <a href="#" class="nav-link @if(request()->routeIs('admin.leave-*')) active @endif">
                            <i class="nav-icon ti ti-calendar-stats"></i>
                            <p>Leave Management <i class="nav-arrow ti ti-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('admin.leave-types.index') }}" class="nav-link @if(request()->routeIs('admin.leave-types.*')) active @endif">
                                    <i class="nav-icon ti ti-category"></i>
                                    <p>Leave Types</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.leave-requests.index') }}" class="nav-link @if(request()->routeIs('admin.leave-requests.*')) active @endif">
                                    <i class="nav-icon ti ti-list-check"></i>
                                    <p>Leave Requests</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endcan

                @can('users.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.users.index') }}" class="nav-link @if(request()->routeIs('admin.users.*')) active @endif">
                            <i class="nav-icon ti ti-users-group"></i>
                            <p>Users</p>
                        </a>
                    </li>
                @endcan

                @canany(['settings.view', 'roles.view', 'permissions.view'])
                    <li class="nav-item @if(request()->routeIs('admin.settings.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*')) menu-open @endif">
                        <a href="#" class="nav-link @if(request()->routeIs('admin.settings.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*')) active @endif">
                            <i class="nav-icon ti ti-settings"></i>
                            <p>Settings <i class="nav-arrow ti ti-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('settings.view')
                                <li class="nav-item">
                                    <a href="{{ route('admin.settings.index') }}" class="nav-link @if(request()->routeIs('admin.settings.index')) active @endif">
                                        <i class="nav-icon ti ti-adjustments-horizontal"></i>
                                        <p>General Settings</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.settings.mobile.branding') }}" class="nav-link @if(request()->routeIs('admin.settings.mobile.*')) active @endif">
                                        <i class="nav-icon ti ti-palette"></i>
                                        <p>Mobile Branding</p>
                                    </a>
                                </li>
                            @endcan
                            @canany(['roles.view', 'permissions.view'])
                                <li class="nav-item @if(request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*')) menu-open @endif">
                                    <a href="#" class="nav-link @if(request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*')) active @endif">
                                        <i class="nav-icon ti ti-shield-lock"></i>
                                        <p>Access Control <i class="nav-arrow ti ti-chevron-right"></i></p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        @can('roles.view')
                                            <li class="nav-item">
                                                <a href="{{ route('admin.roles.index') }}" class="nav-link @if(request()->routeIs('admin.roles.*')) active @endif">
                                                    <i class="nav-icon ti ti-id-badge"></i>
                                                    <p>Roles</p>
                                                </a>
                                            </li>
                                        @endcan
                                        @can('permissions.view')
                                            <li class="nav-item">
                                                <a href="{{ route('admin.permissions.index') }}" class="nav-link @if(request()->routeIs('admin.permissions.*')) active @endif">
                                                    <i class="nav-icon ti ti-key"></i>
                                                    <p>Permissions</p>
                                                </a>
                                            </li>
                                        @endcan
                                    </ul>
                                </li>
                            @endcanany
                        </ul>
                    </li>
                @endcanany

                @can('reports.view')
                    <li class="nav-item">
                        <a href="{{ route('reports.index') }}" class="nav-link @if(request()->routeIs('reports.*')) active @endif">
                            <i class="nav-icon ti ti-chart-bar"></i>
                            <p>Analytics</p>
                        </a>
                    </li>
                @endcan

            @endif

            </ul>
        </nav>
    </div>
</aside>
