<ul class="nav nav-pills mb-4 gap-2">
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('reports.students.index')) active @endif" href="{{ route('reports.students.index') }}">Student List</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('reports.students.directory')) active @endif" href="{{ route('reports.students.directory') }}">Student Directory</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('reports.students.gender_wise')) active @endif" href="{{ route('reports.students.gender_wise') }}">Gender-wise Report</a>
    </li>
</ul>