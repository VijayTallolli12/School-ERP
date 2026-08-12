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
            <li class="nav-item me-2">
                <button class="btn btn-sm btn-primary d-flex align-items-center gap-1 px-3" type="button" data-bs-toggle="modal" data-bs-target="#askErpModal" style="border-radius:var(--erp-btn-radius);">
                    <i class="ti ti-sparkles"></i>
                    <span class="d-none d-sm-inline fw-semibold">Ask ERP</span>
                </button>
            </li>
            @role('Super Admin|School Admin|Principal|Teacher|Accountant|Librarian|Payroll Manager|Receptionist|HR|Staff')
                <li class="nav-item ms-1">
                    <a class="btn btn-link nav-link" href="{{ route('admin.mobile-apps.index') }}" title="Mobile Apps" aria-label="Mobile Apps">
                        <i class="ti ti-device-mobile"></i>
                    </a>
                </li>
            @endrole
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
            <li class="nav-item dropdown ms-2">
                <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 p-1 pe-2 rounded hover-bg-light" data-bs-toggle="dropdown" href="#" role="button" style="transition: background-color 0.2s;">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary fw-bold" style="width:36px;height:36px;font-size:.9rem;">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                    </span>
                    <span class="d-none d-md-block text-start lh-sm ms-1">
                        <div class="fw-semibold text-dark" style="font-size: 0.875rem;">{{ auth()->user()->name }}</div>
                        <div class="text-muted" style="font-size: 0.72rem; letter-spacing: 0.02em;">{{ auth()->user()->roles->first()?->name ?? 'User' }}</div>
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width:240px; border-radius: 0.75rem;">
                    @if($logo = setting('school_logo'))
                        <div class="px-4 pt-4 pb-2 text-center">
                            <img src="{{ $logo }}" alt="{{ setting('school_name') }}" style="width:64px;height:64px;object-fit:cover;border-radius:0.75rem;" class="shadow-sm mb-3">
                            <h6 class="fw-bold mb-0 text-dark">{{ setting('school_name') }}</h6>
                        </div>
                        <div class="dropdown-divider mb-2"></div>
                    @endif
                    <div class="px-4 py-2">
                        <div class="fw-semibold text-dark">{{ auth()->user()->name }}</div>
                        <div class="text-muted small text-truncate">{{ auth()->user()->email }}</div>
                    </div>
                    <div class="dropdown-divider my-2"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item d-flex align-items-center gap-2 px-4 py-2 text-danger fw-medium" type="submit">
                            <i class="ti ti-logout"></i> Logout
                        </button>
                    </form>
                </div>
            </li>
        </ul>
    </div>
</nav>
