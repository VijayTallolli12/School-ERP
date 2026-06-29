<aside class="app-sidebar bg-white">
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
                            <p>Academic Calendar</p>
                        </a>
                    </li>
                @endcan

                @can('student_documents.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.documents.index') }}" class="nav-link @if(request()->routeIs('admin.documents.*')) active @endif">
                            <i class="nav-icon ti ti-file-text"></i>
                            <p>Student Documents</p>
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

                {{-- ========== ADMINISTRATION ========== --}}
                <li class="nav-header">
                    <span class="nav-header-label">Administration</span>
                </li>

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

                @can('settings.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.settings.index') }}" class="nav-link @if(request()->routeIs('admin.settings.*')) active @endif">
                            <i class="nav-icon ti ti-settings"></i>
                            <p>Settings</p>
                        </a>
                    </li>
                @endcan

                @can('reports.view')
                    <li class="nav-item @if(request()->routeIs('reports.*')) menu-open @endif">
                        <a href="#" class="nav-link @if(request()->routeIs('reports.*')) active @endif">
                            <i class="nav-icon ti ti-chart-bar"></i>
                            <p>Analytics <i class="nav-arrow ti ti-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item @if(request()->routeIs('reports.students.*')) menu-open @endif">
                                <a href="#" class="nav-link @if(request()->routeIs('reports.students.*')) active @endif">
                                    <i class="nav-icon ti ti-school"></i>
                                    <p>Student Reports <i class="nav-arrow ti ti-chevron-right"></i></p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ route('reports.students.index') }}" class="nav-link @if(request()->routeIs('reports.students.index')) active @endif">
                                            <i class="nav-icon ti ti-circle"></i>
                                            <p>Dashboard</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('reports.students.directory') }}" class="nav-link @if(request()->routeIs('reports.students.directory')) active @endif">
                                            <i class="nav-icon ti ti-circle"></i>
                                            <p>Student Directory</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('reports.students.gender_wise') }}" class="nav-link @if(request()->routeIs('reports.students.gender_wise')) active @endif">
                                            <i class="nav-icon ti ti-circle"></i>
                                            <p>Gender-wise Report</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-item @if(request()->routeIs('reports.attendance.*')) menu-open @endif">
                                <a href="#" class="nav-link @if(request()->routeIs('reports.attendance.*')) active @endif">
                                    <i class="nav-icon ti ti-calendar-check"></i>
                                    <p>Attendance Reports <i class="nav-arrow ti ti-chevron-right"></i></p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ route('reports.attendance.index') }}" class="nav-link @if(request()->routeIs('reports.attendance.index')) active @endif">
                                            <i class="nav-icon ti ti-circle"></i>
                                            <p>Dashboard</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('reports.attendance.daily') }}" class="nav-link @if(request()->routeIs('reports.attendance.daily') && !request()->routeIs('reports.attendance.daily_list')) active @endif">
                                            <i class="nav-icon ti ti-circle"></i>
                                            <p>Daily Attendance</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('reports.attendance.monthly') }}" class="nav-link @if(request()->routeIs('reports.attendance.monthly')) active @endif">
                                            <i class="nav-icon ti ti-circle"></i>
                                            <p>Monthly Attendance</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('reports.attendance.class_wise') }}" class="nav-link @if(request()->routeIs('reports.attendance.class_wise')) active @endif">
                                            <i class="nav-icon ti ti-circle"></i>
                                            <p>Class-wise Attendance</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('reports.attendance.absent_students') }}" class="nav-link @if(request()->routeIs('reports.attendance.absent_students*')) active @endif">
                                            <i class="nav-icon ti ti-circle"></i>
                                            <p>Absent Students</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            @can('fees.reports')
                                <li class="nav-item @if(request()->routeIs('reports.fees.*')) menu-open @endif">
                                    <a href="#" class="nav-link @if(request()->routeIs('reports.fees.*')) active @endif">
                                        <i class="nav-icon ti ti-cash"></i>
                                        <p>Fee Reports <i class="nav-arrow ti ti-chevron-right"></i></p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        <li class="nav-item">
                                            <a href="{{ route('reports.fees.index') }}" class="nav-link @if(request()->routeIs('reports.fees.index')) active @endif">
                                                <i class="nav-icon ti ti-circle"></i>
                                                <p>Dashboard</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.fees.paid') }}" class="nav-link @if(request()->routeIs('reports.fees.paid')) active @endif">
                                                <i class="nav-icon ti ti-circle"></i>
                                                <p>Paid Fees</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.fees.pending') }}" class="nav-link @if(request()->routeIs('reports.fees.pending')) active @endif">
                                                <i class="nav-icon ti ti-circle"></i>
                                                <p>Pending Fees</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.fees.overdue') }}" class="nav-link @if(request()->routeIs('reports.fees.overdue')) active @endif">
                                                <i class="nav-icon ti ti-circle"></i>
                                                <p>Overdue Fees</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.fees.collection_summary') }}" class="nav-link @if(request()->routeIs('reports.fees.collection_summary')) active @endif">
                                                <i class="nav-icon ti ti-circle"></i>
                                                <p>Collection Summary</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.fees.defaulters') }}" class="nav-link @if(request()->routeIs('reports.fees.defaulters')) active @endif">
                                                <i class="nav-icon ti ti-circle"></i>
                                                <p>Fee Defaulters</p>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @endcan
                            @can('exams.reports')
                                <li class="nav-item @if(request()->routeIs('reports.exams.*')) menu-open @endif">
                                    <a href="#" class="nav-link @if(request()->routeIs('reports.exams.*')) active @endif">
                                        <i class="nav-icon ti ti-file-pencil"></i>
                                        <p>Exam Reports <i class="nav-arrow ti ti-chevron-right"></i></p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        <li class="nav-item">
                                            <a href="{{ route('reports.exams.index') }}" class="nav-link @if(request()->routeIs('reports.exams.index')) active @endif">
                                                <i class="nav-icon ti ti-circle"></i>
                                                <p>Dashboard</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.exams.results') }}" class="nav-link @if(request()->routeIs('reports.exams.results')) active @endif">
                                                <i class="nav-icon ti ti-circle"></i>
                                                <p>Exam Results</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.exams.class_performance') }}" class="nav-link @if(request()->routeIs('reports.exams.class_performance')) active @endif">
                                                <i class="nav-icon ti ti-circle"></i>
                                                <p>Class Performance</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.exams.subject_performance') }}" class="nav-link @if(request()->routeIs('reports.exams.subject_performance')) active @endif">
                                                <i class="nav-icon ti ti-circle"></i>
                                                <p>Subject Performance</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.exams.student_summary') }}" class="nav-link @if(request()->routeIs('reports.exams.student_summary')) active @endif">
                                                <i class="nav-icon ti ti-circle"></i>
                                                <p>Student Summary</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.exams.top_performers') }}" class="nav-link @if(request()->routeIs('reports.exams.top_performers')) active @endif">
                                                <i class="nav-icon ti ti-trophy"></i>
                                                <p>Top Performers</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.exams.pass_fail_analysis') }}" class="nav-link @if(request()->routeIs('reports.exams.pass_fail_analysis')) active @endif">
                                                <i class="nav-icon ti ti-checkup-list"></i>
                                                <p>Pass/Fail Analysis</p>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @endcan
                            @can('teachers.reports')
                                <li class="nav-item @if(request()->routeIs('reports.teachers.*')) menu-open @endif">
                                    <a href="#" class="nav-link @if(request()->routeIs('reports.teachers.*')) active @endif">
                                        <i class="nav-icon ti ti-presentation"></i>
                                        <p>Teacher Reports <i class="nav-arrow ti ti-chevron-right"></i></p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        <li class="nav-item">
                                            <a href="{{ route('reports.teachers.index') }}" class="nav-link @if(request()->routeIs('reports.teachers.index')) active @endif">
                                                <i class="nav-icon ti ti-circle"></i>
                                                <p>Dashboard</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.teachers.list') }}" class="nav-link @if(request()->routeIs('reports.teachers.list')) active @endif">
                                                <i class="nav-icon ti ti-circle"></i>
                                                <p>Teacher List</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.teachers.attendance') }}" class="nav-link @if(request()->routeIs('reports.teachers.attendance')) active @endif">
                                                <i class="nav-icon ti ti-circle"></i>
                                                <p>Attendance</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.teachers.subject_allocation') }}" class="nav-link @if(request()->routeIs('reports.teachers.subject_allocation')) active @endif">
                                                <i class="nav-icon ti ti-circle"></i>
                                                <p>Subject Allocation</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.teachers.class_teacher_mapping') }}" class="nav-link @if(request()->routeIs('reports.teachers.class_teacher_mapping')) active @endif">
                                                <i class="nav-icon ti ti-circle"></i>
                                                <p>Class Teacher Map</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.teachers.workload') }}" class="nav-link @if(request()->routeIs('reports.teachers.workload')) active @endif">
                                                <i class="nav-icon ti ti-circle"></i>
                                                <p>Workload</p>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @endcan
                            @can('parents.reports')
                                <li class="nav-item @if(request()->routeIs('reports.parents.*')) menu-open @endif">
                                    <a href="#" class="nav-link @if(request()->routeIs('reports.parents.*')) active @endif">
                                        <i class="nav-icon ti ti-users"></i>
                                        <p>Parent Reports <i class="nav-arrow ti ti-chevron-right"></i></p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        <li class="nav-item">
                                            <a href="{{ route('reports.parents.index') }}" class="nav-link @if(request()->routeIs('reports.parents.index')) active @endif">
                                                <i class="nav-icon ti ti-circle"></i>
                                                <p>Dashboard</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.parents.list') }}" class="nav-link @if(request()->routeIs('reports.parents.list')) active @endif">
                                                <i class="nav-icon ti ti-circle"></i>
                                                <p>Parent List</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.parents.mapping') }}" class="nav-link @if(request()->routeIs('reports.parents.mapping')) active @endif">
                                                <i class="nav-icon ti ti-circle"></i>
                                                <p>Parent-Student Mapping</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.parents.activity_summary') }}" class="nav-link @if(request()->routeIs('reports.parents.activity_summary')) active @endif">
                                                <i class="nav-icon ti ti-circle"></i>
                                                <p>Activity Summary</p>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcan

            </ul>
        </nav>
    </div>
</aside>
