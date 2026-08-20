<ul class="nav nav-pills mb-4 gap-2">
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('reports.exams.index')) active @endif" href="{{ route('reports.exams.index') }}">Dashboard</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('reports.exams.results')) active @endif" href="{{ route('reports.exams.results') }}">Exam Results</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('reports.exams.class_performance')) active @endif" href="{{ route('reports.exams.class_performance') }}">Class Performance</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('reports.exams.subject_performance')) active @endif" href="{{ route('reports.exams.subject_performance') }}">Subject Performance</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('reports.exams.student_summary')) active @endif" href="{{ route('reports.exams.student_summary') }}">Student Summary</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('reports.exams.top_performers')) active @endif" href="{{ route('reports.exams.top_performers') }}">Top Performers</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('reports.exams.pass_fail_analysis')) active @endif" href="{{ route('reports.exams.pass_fail_analysis') }}">Pass/Fail Analysis</a>
    </li>
</ul>