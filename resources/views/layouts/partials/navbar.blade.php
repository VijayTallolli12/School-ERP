<nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="Toggle sidebar">
                    <i class="ti ti-menu-2"></i>
                </a>
            </li>
        </ul>

        <ul class="navbar-nav ms-auto align-items-center">
            @include('layouts.partials._bell')
            <li class="nav-item dropdown">
                <button class="btn btn-link nav-link dropdown-toggle" data-bs-toggle="dropdown" type="button" aria-label="Toggle theme">
                    <i class="ti ti-sun-moon"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow-sm">
                    <h6 class="dropdown-header text-uppercase">Theme</h6>
                    <button class="dropdown-item d-flex align-items-center gap-2" type="button" data-bs-theme-value="light">
                        <i class="ti ti-sun text-warning"></i> Light
                    </button>
                    <button class="dropdown-item d-flex align-items-center gap-2" type="button" data-bs-theme-value="dark">
                        <i class="ti ti-moon text-info"></i> Dark
                    </button>
                </div>
            </li>
            <li class="nav-item dropdown ms-1">
                <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown" href="#" role="button">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary" style="width:32px;height:32px;font-size:.85rem;">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                    </span>
                    <span class="d-none d-md-inline fw-medium">{{ auth()->user()->name }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width:220px;">
                    @if($logo = setting('school_logo'))
                        <div class="px-3 pt-3 pb-1 text-center">
                            <img src="{{ $logo }}" alt="{{ setting('school_name') }}" style="width:60px;height:60px;object-fit:cover;border-radius:0.625rem;" class="shadow-sm">
                            <div class="small fw-semibold mt-2 text-secondary">{{ setting('school_name') }}</div>
                        </div>
                        <div class="dropdown-divider"></div>
                    @endif
                    <h6 class="dropdown-header text-truncate">{{ auth()->user()->email }}</h6>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item d-flex align-items-center gap-2" type="submit">
                            <i class="ti ti-logout"></i> Logout
                        </button>
                    </form>
                </div>
            </li>
        </ul>
    </div>
</nav>
