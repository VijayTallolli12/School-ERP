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
                @can('dashboard.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link @if(request()->routeIs('admin.dashboard')) active @endif">
                            <i class="nav-icon ti ti-gauge"></i>
                            <p>Dashboard</p>
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

                <li class="nav-header">Modules</li>
                @foreach ([
                    ['notifications.view', 'Notifications', 'bell', 'admin.notifications.index'],
                    ['fees.view', 'Fees', 'receipt', 'admin.fees.index'],
                    ['settings.view', 'Settings', 'settings', 'admin.settings.index'],
                ] as [$permission, $label, $icon, $route])
                    @can($permission)
                        <li class="nav-item">
                            <a href="{{ route($route) }}" class="nav-link @if(request()->routeIs($route.'*')) active @endif">
                                <i class="nav-icon ti ti-{{ $icon }}"></i>
                                <p>{{ $label }}</p>
                            </a>
                        </li>
                    @endcan
                @endforeach

                @can('reports.view')
                    <li class="nav-item @if(request()->routeIs('reports.*')) menu-open @endif">
                        <a href="#" class="nav-link @if(request()->routeIs('reports.*')) active @endif">
                            <i class="nav-icon ti ti-chart-bar"></i>
                            <p>Reports <i class="nav-arrow ti ti-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('reports.students.index') }}" class="nav-link @if(request()->routeIs('reports.students.*')) active @endif">
                                    <i class="nav-icon ti ti-school"></i>
                                    <p>Student Reports</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('reports.attendance.index') }}" class="nav-link @if(request()->routeIs('reports.attendance.*')) active @endif">
                                    <i class="nav-icon ti ti-calendar-check"></i>
                                    <p>Attendance Reports</p>
                                </a>
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
                                                <p>Paid Fees Report</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.fees.pending') }}" class="nav-link @if(request()->routeIs('reports.fees.pending')) active @endif">
                                                <i class="nav-icon ti ti-circle"></i>
                                                <p>Pending Fees Report</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.fees.overdue') }}" class="nav-link @if(request()->routeIs('reports.fees.overdue')) active @endif">
                                                <i class="nav-icon ti ti-circle"></i>
                                                <p>Overdue Fees Report</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.fees.collection_summary') }}" class="nav-link @if(request()->routeIs('reports.fees.collection_summary')) active @endif">
                                                <i class="nav-icon ti ti-circle"></i>
                                                <p>Collection Summary</p>
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
                                                <p>Exam Results Report</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.exams.class_performance') }}" class="nav-link @if(request()->routeIs('reports.exams.class_performance')) active @endif">
                                                <i class="nav-icon ti ti-circle"></i>
                                                <p>Class Performance Report</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.exams.subject_performance') }}" class="nav-link @if(request()->routeIs('reports.exams.subject_performance')) active @endif">
                                                <i class="nav-icon ti ti-circle"></i>
                                                <p>Subject Performance Report</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.exams.student_summary') }}" class="nav-link @if(request()->routeIs('reports.exams.student_summary')) active @endif">
                                                <i class="nav-icon ti ti-circle"></i>
                                                <p>Student Result Summary</p>
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
                                                <p>Class Teacher Mapping</p>
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

                @can('academics.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.academics.index') }}" class="nav-link @if(request()->routeIs('admin.academics.*')) active @endif">
                            <i class="nav-icon ti ti-book"></i>
                            <p>Academic</p>
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

                @can('users.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.users.index') }}" class="nav-link @if(request()->routeIs('admin.users.*')) active @endif">
                            <i class="nav-icon ti ti-users-group"></i>
                            <p>Users</p>
                        </a>
                    </li>
                @endcan
            </ul>
        </nav>
    </div>
</aside>
