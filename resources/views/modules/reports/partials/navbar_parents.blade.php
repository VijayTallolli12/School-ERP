<ul class="nav nav-pills mb-4 gap-2">
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('reports.parents.index')) active @endif" href="{{ route('reports.parents.index') }}">Dashboard</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('reports.parents.list')) active @endif" href="{{ route('reports.parents.list') }}">Parent List</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('reports.parents.mapping')) active @endif" href="{{ route('reports.parents.mapping') }}">Parent-Student Mapping</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('reports.parents.activity_summary')) active @endif" href="{{ route('reports.parents.activity_summary') }}">Activity Summary</a>
    </li>
</ul>