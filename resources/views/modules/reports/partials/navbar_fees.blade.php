<ul class="nav nav-pills mb-4 gap-2">
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('reports.fees.index')) active @endif" href="{{ route('reports.fees.index') }}">Dashboard</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('reports.fees.paid')) active @endif" href="{{ route('reports.fees.paid') }}">Paid Fees</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('reports.fees.pending')) active @endif" href="{{ route('reports.fees.pending') }}">Pending Fees</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('reports.fees.overdue')) active @endif" href="{{ route('reports.fees.overdue') }}">Overdue Fees</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('reports.fees.collection_summary')) active @endif" href="{{ route('reports.fees.collection_summary') }}">Collection Summary</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('reports.fees.defaulters')) active @endif" href="{{ route('reports.fees.defaulters') }}">Fee Defaulters</a>
    </li>
</ul>