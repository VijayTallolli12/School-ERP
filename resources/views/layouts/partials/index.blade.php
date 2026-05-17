@extends('modules.reports.reports_layout')

@section('title', 'Fee Reports')
@section('report_title', 'Fee Reports Overview')

@section('content')
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-12">
                <p class="text-muted">Monitor and analyze school fee collections, pending payments, and overall revenue.</p>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-success bg-opacity-10 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1">Total Collected</p>
                            <h4 class="mb-0">{{ number_format($stats['collected'] ?? 0, 2) }}</h4>
                        </div>
                        <div class="fs-32 text-success">
                            <i class="ti ti-cash"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning bg-opacity-10 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1">Total Pending</p>
                            <h4 class="mb-0">{{ number_format($stats['pending'] ?? 0, 2) }}</h4>
                        </div>
                        <div class="fs-32 text-warning">
                            <i class="ti ti-history"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger bg-opacity-10 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1">Total Overdue</p>
                            <h4 class="mb-0">{{ number_format($stats['overdue'] ?? 0, 2) }}</h4>
                        </div>
                        <div class="fs-32 text-danger">
                            <i class="ti ti-alert-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @php
            $reports = [
                ['route' => 'reports.fees.collection_summary', 'icon' => 'chart-bar', 'color' => 'text-primary', 'title' => 'Collection Summary', 'desc' => 'Class-wise fee expected, collected, and pending summary.'],
                ['route' => 'reports.fees.paid', 'icon' => 'file-invoice', 'color' => 'text-success', 'title' => 'Paid Fees Report', 'desc' => 'Detailed records of all fee payments received.'],
                ['route' => 'reports.fees.pending', 'icon' => 'hourglass', 'color' => 'text-warning', 'title' => 'Pending Fees Report', 'desc' => 'Students with outstanding fee balances.'],
                ['route' => 'reports.fees.overdue', 'icon' => 'alert-triangle', 'color' => 'text-danger', 'title' => 'Overdue Fees Report', 'desc' => 'Students with past-due fee balances.'],
            ];
        @endphp

        @foreach($reports as $report)
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card hover-shadow cursor-pointer h-100" style="transition: all 0.3s ease;">
                    <a href="{{ route($report['route']) }}" class="card-link text-decoration-none text-dark">
                        <div class="card-body text-center py-4">
                            <div class="fs-40 {{ $report['color'] }} mb-3">
                                <i class="ti ti-{{ $report['icon'] }}"></i>
                            </div>
                            <h5 class="card-title w-100">{{ $report['title'] }}</h5>
                            <p class="text-muted small mt-2 mb-0">{{ $report['desc'] }}</p>
                        </div>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@endsection