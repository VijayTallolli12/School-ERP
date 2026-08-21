<style>
/* Navbar and Header styling */
.app-header {
    background-color: #ffffff !important;
    border-bottom: 1px solid rgba(0,0,0,0.05) !important;
    min-height: 64px;
    padding-top: 8px !important;
    padding-bottom: 8px !important;
}
.navbar-breadcrumb {
    font-size: 14px;
    font-weight: 500;
    color: #6b7280;
    margin-left: 8px;
}
.navbar-breadcrumb-active {
    color: #111827;
    font-weight: 600;
}
</style>
<nav class="app-header navbar navbar-expand bg-body sticky-top" style="z-index: 1040;">
    <div class="container-fluid">
        <ul class="navbar-nav align-items-center">
            <!-- Sidebar toggle -->
            <li class="nav-item d-flex align-items-center">
                <a class="nav-link p-0 d-flex align-items-center justify-content-center" data-lte-toggle="sidebar" href="#" role="button" aria-label="Toggle sidebar" style="color: #6b7280; height: 32px; width: 32px;">
                    <i class="ti ti-menu-2" style="font-size: 1.4rem; line-height: 1;"></i>
                </a>
            </li>
            <li class="nav-item d-none d-md-flex align-items-center navbar-breadcrumb h-100">
                <ol class="breadcrumb m-0 bg-transparent p-0 d-flex align-items-center">
                    @hasSection('breadcrumbs')
                        @yield('breadcrumbs')
                    @else
                        <li class="breadcrumb-item active text-dark fw-semibold d-flex align-items-center" style="line-height: 1; padding-top: 2px;">@yield('page-title', 'Dashboard')</li>
                    @endif
                </ol>
            </li>
        </ul>

        <ul class="navbar-nav ms-auto align-items-center gap-1">
            @include('layouts.partials._bell')
            <li class="nav-item">
                <a class="nav-link text-muted" href="{{ route('admin.mobile-apps.index') }}" title="Mobile Apps">
                    <i class="ti ti-device-mobile" style="font-size: 1.4rem;"></i>
                </a>
            </li>
            <div class="vr mx-2 bg-secondary opacity-25" style="height: 32px; align-self: center;"></div>
            <li class="nav-item dropdown ms-1">
                <a class="nav-link d-flex align-items-center gap-2 p-1 pe-2 rounded hover-bg-light text-decoration-none" data-bs-toggle="dropdown" href="#" role="button" style="transition: background-color 0.2s;">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold" style="width:36px;height:36px;font-size:.9rem; overflow:hidden; background-color: var(--erp-primary-light); color: var(--erp-primary);">
                        @if(auth()->user()->profile_photo_url ?? null)
                            <img src="{{ auth()->user()->profile_photo_url }}" alt="avatar" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                        @endif
                    </span>
                    <span class="d-none d-md-block text-start lh-sm ms-1">
                        <div class="fw-bold text-dark" style="font-size: 0.85rem;">{{ auth()->user()->name }}</div>
                        <div class="text-muted" style="font-size: 0.72rem; letter-spacing: 0.02em;">{{ auth()->user()->roles->first()?->name ?? 'System Admin' }}</div>
                    </span>
                    <i class="ti ti-chevron-down text-muted ms-1" style="font-size: 12px;"></i>
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
                        <button class="dropdown-item d-flex align-items-center gap-2 px-4 py-2 text-danger fw-medium" type="submit">Logout</button>
                    </form>
                </div>
            </li>
        </ul>
    </div>
</nav>
