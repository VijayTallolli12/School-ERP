<ul class="nav nav-pills mb-4 gap-2">
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('reports.attendance.index')) active @endif" href="{{ route('reports.attendance.index') }}">Dashboard</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('reports.attendance.daily') && !request()->routeIs('reports.attendance.daily_list')) active @endif" href="{{ route('reports.attendance.daily') }}">Daily Attendance</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('reports.attendance.monthly')) active @endif" href="{{ route('reports.attendance.monthly') }}">Monthly Attendance</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('reports.attendance.class_wise')) active @endif" href="{{ route('reports.attendance.class_wise') }}">Class-wise Attendance</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('reports.attendance.absent_students*')) active @endif" href="{{ route('reports.attendance.absent_students') }}">Absent Students</a>
    </li>
</ul>