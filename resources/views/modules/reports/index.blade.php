@extends('layouts.admin')

@section('title', 'Analytics Hub')
@section('page-title', 'Analytics Hub')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Analytics Hub</li>
@endsection

@section('content')
<div class="row g-4">
    @can('students.reports')
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm" style="border-radius: var(--erp-card-radius);">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center mb-3">
                    <div class="d-flex align-items-center justify-content-center rounded" style="width: 48px; height: 48px; background: var(--erp-primary-light); color: var(--erp-primary);">
                        <i class="ti ti-school fs-3"></i>
                    </div>
                    <h5 class="ms-3 mb-0 fw-bold">Student Reports</h5>
                </div>
                <p class="text-muted mb-4">View comprehensive reports regarding student admissions, class-wise distribution, directories, and gender-wise analysis.</p>
                <div class="mt-auto">
                    <a href="{{ route('reports.students.index') }}" class="btn btn-outline-primary w-100 fw-semibold text-center d-flex justify-content-center align-items-center" style="border-radius: var(--erp-btn-radius);">
                        View Report
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endcan

    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm" style="border-radius: var(--erp-card-radius);">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center mb-3">
                    <div class="d-flex align-items-center justify-content-center rounded" style="width: 48px; height: 48px; background: var(--erp-primary-light); color: var(--erp-primary);">
                        <i class="ti ti-calendar-check fs-3"></i>
                    </div>
                    <h5 class="ms-3 mb-0 fw-bold">Attendance Reports</h5>
                </div>
                <p class="text-muted mb-4">Analyze daily, monthly, and class-wise attendance statistics along with detailed absent student tracking.</p>
                <div class="mt-auto">
                    <a href="{{ route('reports.attendance.index') }}" class="btn btn-outline-primary w-100 fw-semibold text-center d-flex justify-content-center align-items-center" style="border-radius: var(--erp-btn-radius);">
                        View Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    @can('fees.reports')
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm" style="border-radius: var(--erp-card-radius);">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center mb-3">
                    <div class="d-flex align-items-center justify-content-center rounded" style="width: 48px; height: 48px; background: var(--erp-primary-light); color: var(--erp-primary);">
                        <i class="ti ti-cash fs-3"></i>
                    </div>
                    <h5 class="ms-3 mb-0 fw-bold">Fee Reports</h5>
                </div>
                <p class="text-muted mb-4">Track fee collections, paid vs pending fee status, overdue collections, and detailed list of fee defaulters.</p>
                <div class="mt-auto">
                    <a href="{{ route('reports.fees.index') }}" class="btn btn-outline-primary w-100 fw-semibold text-center d-flex justify-content-center align-items-center" style="border-radius: var(--erp-btn-radius);">
                        View Report
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endcan

    @can('exams.reports')
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm" style="border-radius: var(--erp-card-radius);">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center mb-3">
                    <div class="d-flex align-items-center justify-content-center rounded" style="width: 48px; height: 48px; background: var(--erp-primary-light); color: var(--erp-primary);">
                        <i class="ti ti-file-pencil fs-3"></i>
                    </div>
                    <h5 class="ms-3 mb-0 fw-bold">Exam Reports</h5>
                </div>
                <p class="text-muted mb-4">Explore exam results, class and subject performance analysis, top performers, and overall pass/fail metrics.</p>
                <div class="mt-auto">
                    <a href="{{ route('reports.exams.index') }}" class="btn btn-outline-primary w-100 fw-semibold text-center d-flex justify-content-center align-items-center" style="border-radius: var(--erp-btn-radius);">
                        View Report
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endcan

    @can('teachers.reports')
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm" style="border-radius: var(--erp-card-radius);">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center mb-3">
                    <div class="d-flex align-items-center justify-content-center rounded" style="width: 48px; height: 48px; background: var(--erp-primary-light); color: var(--erp-primary);">
                        <i class="ti ti-presentation fs-3"></i>
                    </div>
                    <h5 class="ms-3 mb-0 fw-bold">Teacher Reports</h5>
                </div>
                <p class="text-muted mb-4">View teacher list, staff attendance, subject allocations, class teacher mappings, and overall workload reports.</p>
                <div class="mt-auto">
                    <a href="{{ route('reports.teachers.index') }}" class="btn btn-outline-primary w-100 fw-semibold text-center d-flex justify-content-center align-items-center" style="border-radius: var(--erp-btn-radius);">
                        View Report
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endcan

    @can('parents.reports')
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm" style="border-radius: var(--erp-card-radius);">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center mb-3">
                    <div class="d-flex align-items-center justify-content-center rounded" style="width: 48px; height: 48px; background: var(--erp-primary-light); color: var(--erp-primary);">
                        <i class="ti ti-users fs-3"></i>
                    </div>
                    <h5 class="ms-3 mb-0 fw-bold">Parent Reports</h5>
                </div>
                <p class="text-muted mb-4">Access parent directory, parent-student mappings, and comprehensive parent engagement activity summaries.</p>
                <div class="mt-auto">
                    <a href="{{ route('reports.parents.index') }}" class="btn btn-outline-primary w-100 fw-semibold text-center d-flex justify-content-center align-items-center" style="border-radius: var(--erp-btn-radius);">
                        View Report
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endcan
</div>
@endsection
