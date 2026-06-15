@extends('layouts.admin')

@section("title", "Fee Reports Dashboard")
@section("page-title", "Fee Reports Dashboard")

@section("content")
    <!-- Hero KPI Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="erp-hero-card">
                <div>
                    <div class="hero-value">{{ number_format($stats['total_collected'] ?? 0, 2) }}</div>
                    <div class="hero-label">Total Collected</div>
                    <div class="hero-trend trend-up">
                        <i class="ti ti-trending-up"></i> Lifetime
                    </div>
                </div>
                <div class="hero-icon" style="background:rgba(22,163,74,.1);color:#16a34a;">
                    <i class="ti ti-wallet"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="erp-hero-card">
                <div>
                    <div class="hero-value">{{ number_format($stats['pending_fees'] ?? 0, 2) }}</div>
                    <div class="hero-label">Pending Fees</div>
                    <div class="hero-trend trend-down">
                        <i class="ti ti-trending-down"></i> Outstanding
                    </div>
                </div>
                <div class="hero-icon" style="background:rgba(245,158,11,.12);color:#d97706;">
                    <i class="ti ti-hourglass"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="erp-hero-card">
                <div>
                    <div class="hero-value">{{ number_format($stats['monthly_collection'] ?? 0, 2) }}</div>
                    <div class="hero-label">This Month</div>
                    <div class="hero-trend trend-up">
                        <i class="ti ti-calendar-event"></i> {{ now()->format('M Y') }}
                    </div>
                </div>
                <div class="hero-icon" style="background:rgba(37,99,235,.1);color:#2563eb;">
                    <i class="ti ti-calendar-stats"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Collection Efficiency (visual progress) -->
    @php
        $total = ($stats['total_collected'] ?? 0) + ($stats['pending_fees'] ?? 0);
        $collectedPct = $total > 0 ? round(($stats['total_collected'] / $total) * 100, 1) : 0;
        $pendingPct = $total > 0 ? round(($stats['pending_fees'] / $total) * 100, 1) : 0;
        $monthlyPct = $stats['total_collected'] > 0 ? round(($stats['monthly_collection'] / $stats['total_collected']) * 100, 1) : 0;
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="card-title mb-0"><i class="ti ti-chart-line text-primary me-2"></i>Collection Efficiency</h3>
                        <span class="badge bg-success-subtle text-success fs-13 fw-semibold">{{ $collectedPct }}% collected</span>
                    </div>
                    <div class="mini-progress mb-3">
                        <div class="mini-progress-bar">
                            <div class="mp-fill" style="width:{{ $collectedPct }}%;background:#16a34a;"></div>
                        </div>
                        <span class="mini-progress-text">{{ $stats['total_collected'] > 0 ? number_format($stats['total_collected'] / 1000, 0) : 0 }}k</span>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2">
                                <span class="stat-inline-dot" style="background:#16a34a;"></span>
                                <div>
                                    <div class="small fw-semibold">{{ number_format($stats['total_collected'] ?? 0, 2) }}</div>
                                    <div class="small text-secondary">Collected</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2">
                                <span class="stat-inline-dot" style="background:#d97706;"></span>
                                <div>
                                    <div class="small fw-semibold">{{ number_format($stats['pending_fees'] ?? 0, 2) }}</div>
                                    <div class="small text-secondary">Pending</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="card-title mb-0"><i class="ti ti-chart-bar text-primary me-2"></i>Monthly Contribution</h3>
                        <span class="badge bg-primary-subtle text-primary fs-13 fw-semibold">{{ $monthlyPct }}% of total</span>
                    </div>
                    <div class="d-flex align-items-end gap-3">
                        <div>
                            <div class="hero-value" style="font-size:1.75rem;">{{ number_format($stats['monthly_collection'] ?? 0, 2) }}</div>
                            <div class="hero-label" style="font-size:0.78rem;">{{ now()->format('F Y') }}</div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="mini-progress mb-1">
                                <div class="mini-progress-bar" style="height:10px;border-radius:5px;">
                                    <div class="mp-fill" style="width:{{ $monthlyPct }}%;background:#2563eb;border-radius:5px;"></div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between small text-secondary">
                                <span>Monthly vs Total</span>
                                <span>{{ $monthlyPct }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Navigation -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0"><i class="ti ti-list text-primary me-2"></i>Fee Reports</h3>
        </div>
        <div class="card-body p-0">
            <div class="row g-0">
                <div class="col-md-6 col-lg-3 border-end border-bottom">
                    <a href="{{ route('reports.fees.paid') }}" class="d-block p-3 text-decoration-none hover-bg-light transition-bg">
                        <div class="d-flex align-items-center gap-3">
                            <div class="nc-icon" style="background:rgba(22,163,74,.1);color:#16a34a;width:44px;height:44px;border-radius:0.75rem;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;">
                                <i class="ti ti-receipt"></i>
                            </div>
                            <div>
                                <div class="nc-title">Collection Report</div>
                                <div class="nc-sub">View all fee payments collected</div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-6 col-lg-3 border-end border-bottom">
                    <a href="{{ route('reports.fees.pending') }}" class="d-block p-3 text-decoration-none hover-bg-light transition-bg">
                        <div class="d-flex align-items-center gap-3">
                            <div class="nc-icon" style="background:rgba(245,158,11,.12);color:#d97706;width:44px;height:44px;border-radius:0.75rem;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;">
                                <i class="ti ti-hourglass"></i>
                            </div>
                            <div>
                                <div class="nc-title">Pending Fees</div>
                                <div class="nc-sub">Track outstanding fee balances</div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-6 col-lg-3 border-end border-bottom">
                    <a href="{{ route('reports.fees.overdue') }}" class="d-block p-3 text-decoration-none hover-bg-light transition-bg">
                        <div class="d-flex align-items-center gap-3">
                            <div class="nc-icon" style="background:rgba(220,38,38,.1);color:#dc2626;width:44px;height:44px;border-radius:0.75rem;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;">
                                <i class="ti ti-alert-triangle"></i>
                            </div>
                            <div>
                                <div class="nc-title">Overdue Fees</div>
                                <div class="nc-sub">Identify past-due assignments</div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-6 col-lg-3 border-bottom">
                    <a href="{{ route('reports.fees.collection_summary') }}" class="d-block p-3 text-decoration-none hover-bg-light transition-bg">
                        <div class="d-flex align-items-center gap-3">
                            <div class="nc-icon" style="background:rgba(37,99,235,.1);color:#2563eb;width:44px;height:44px;border-radius:0.75rem;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;">
                                <i class="ti ti-chart-bar"></i>
                            </div>
                            <div>
                                <div class="nc-title">Collection Summary</div>
                                <div class="nc-sub">Aggregated view by class</div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-6 col-lg-3 border-end">
                    <a href="{{ route('reports.fees.defaulters') }}" class="d-block p-3 text-decoration-none hover-bg-light transition-bg">
                        <div class="d-flex align-items-center gap-3">
                            <div class="nc-icon" style="background:rgba(220,38,38,.1);color:#dc2626;width:44px;height:44px;border-radius:0.75rem;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;">
                                <i class="ti ti-users-x"></i>
                            </div>
                            <div>
                                <div class="nc-title">Fee Defaulters</div>
                                <div class="nc-sub">Track defaulters &amp; contacts</div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
