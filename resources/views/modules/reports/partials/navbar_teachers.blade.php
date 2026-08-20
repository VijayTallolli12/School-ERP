<ul class="nav nav-pills mb-4 gap-2">
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('reports.teachers.index')) active @endif" href="{{ route('reports.teachers.index') }}">Dashboard</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('reports.teachers.list')) active @endif" href="{{ route('reports.teachers.list') }}">Teacher List</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('reports.teachers.attendance')) active @endif" href="{{ route('reports.teachers.attendance') }}">Attendance</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('reports.teachers.subject_allocation')) active @endif" href="{{ route('reports.teachers.subject_allocation') }}">Subject Allocation</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('reports.teachers.class_teacher_mapping')) active @endif" href="{{ route('reports.teachers.class_teacher_mapping') }}">Class Teacher Map</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('reports.teachers.workload')) active @endif" href="{{ route('reports.teachers.workload') }}">Workload</a>
    </li>
</ul>