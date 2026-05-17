@extends('modules.reports.reports_layout')

@section('title', 'Attendance Reports')
@section('report_title', 'Attendance Reports')

@section('content')
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="text-muted">Monitor student attendance patterns and trends</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('reports.attendance.daily') }}" class="btn btn-primary me-2">
                    <i class="ti-calendar me-2"></i>Daily Report
                </a>
            </div>
        </div>
    </div>

    {{-- Today's Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1">Present</p>
                            <h4 class="fw-semibold mb-0">{{ $todaySummary['present'] ?? 0 }}</h4>
                        </div>
                        <div class="fs-32 text-success">
                            <i class="ti-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1">Absent</p>
                            <h4 class="fw-semibold mb-0">{{ $todaySummary['absent'] ?? 0 }}</h4>
                        </div>
                        <div class="fs-32 text-danger">
                            <i class="ti-close"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1">Late</p>
                            <h4 class="fw-semibold mb-0">{{ $todaySummary['late'] ?? 0 }}</h4>
                        </div>
                        <div class="fs-32 text-warning">
                            <i class="ti-time"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1">Leave</p>
                            <h4 class="fw-semibold mb-0">{{ $todaySummary['leave'] ?? 0 }}</h4>
                        </div>
                        <div class="fs-32 text-info">
                            <i class="ti-clipboard"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Report Shortcuts --}}
    <div class="row">
        <div class="col-md-4">
            <div class="card hover-shadow cursor-pointer transition-all">
                <a href="{{ route('reports.attendance.daily') }}" class="card-link">
                    <div class="card-body text-center py-5">
                        <div class="fs-40 text-primary mb-3">
                            <i class="ti-calendar"></i>
                        </div>
                        <h5 class="fw-semibold card-title">Daily Report</h5>
                        <p class="text-muted">View attendance for a specific date</p>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card hover-shadow cursor-pointer transition-all">
                <a href="{{ route('reports.attendance.monthly') }}" class="card-link">
                    <div class="card-body text-center py-5">
                        <div class="fs-40 text-info mb-3">
                            <i class="ti-month"></i>
                        </div>
                        <h5 class="fw-semibold card-title">Monthly Report</h5>
                        <p class="text-muted">View attendance trends for a month</p>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card hover-shadow cursor-pointer transition-all">
                <a href="{{ route('reports.attendance.class_wise') }}" class="card-link">
                    <div class="card-body text-center py-5">
                        <div class="fs-40 text-warning mb-3">
                            <i class="ti-list"></i>
                        </div>
                        <h5 class="fw-semibold card-title">Class-wise Report</h5>
                        <p class="text-muted">Compare attendance across classes</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
@endsection

