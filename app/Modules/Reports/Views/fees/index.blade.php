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
    <div class="mb-0">
        <h5 class="fw-semibold mb-3" style="font-size:0.95rem;"><i class="ti ti-list me-1 text-primary"></i> Fee Reports</h5>
        <div class="report-grid">
            <div class="report-card-saas" onclick="location.href='{{ route('reports.fees.paid') }}'">
                <div class="d-flex align-items-start gap-2 mb-2">
                    <div class="rcs-icon" style="background:rgba(22,163,74,.08);color:#16a34a;"><i class="ti ti-receipt"></i></div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="rcs-title">Collection Report</div>
                        <p class="rcs-desc">View all fee payments collected</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="rcs-stat"><i class="ti ti-wallet"></i>₹{{ number_format($stats['total_collected'] ?? 0, 0) }} collected</span>
                </div>
                <div class="rcs-footer">
                    <span>View Report</span>
                    <span class="rcs-arrow">→</span>
                </div>
            </div>
            <div class="report-card-saas" onclick="location.href='{{ route('reports.fees.pending') }}'">
                <div class="d-flex align-items-start gap-2 mb-2">
                    <div class="rcs-icon" style="background:rgba(245,158,11,.08);color:#d97706;"><i class="ti ti-hourglass"></i></div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="rcs-title">Pending Fees</div>
                        <p class="rcs-desc">Track outstanding fee balances</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="rcs-stat"><i class="ti ti-clock"></i>₹{{ number_format($stats['pending_fees'] ?? 0, 0) }} pending</span>
                </div>
                <div class="rcs-footer">
                    <span>View Report</span>
                    <span class="rcs-arrow">→</span>
                </div>
            </div>
            <div class="report-card-saas" onclick="location.href='{{ route('reports.fees.overdue') }}'">
                <div class="d-flex align-items-start gap-2 mb-2">
                    <div class="rcs-icon" style="background:rgba(220,38,38,.08);color:#dc2626;"><i class="ti ti-alert-triangle"></i></div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="rcs-title">Overdue Fees</div>
                        <p class="rcs-desc">Identify past-due assignments</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="rcs-stat"><i class="ti ti-alert-circle"></i>Track overdue</span>
                </div>
                <div class="rcs-footer">
                    <span>View Report</span>
                    <span class="rcs-arrow">→</span>
                </div>
            </div>
            <div class="report-card-saas" onclick="location.href='{{ route('reports.fees.collection_summary') }}'">
                <div class="d-flex align-items-start gap-2 mb-2">
                    <div class="rcs-icon" style="background:rgba(37,99,235,.08);color:#2563eb;"><i class="ti ti-chart-bar"></i></div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="rcs-title">Collection Summary</div>
                        <p class="rcs-desc">Aggregated view by class</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="rcs-stat"><i class="ti ti-database"></i>{{ number_format($stats['monthly_collection'] ?? 0, 0) }} monthly</span>
                </div>
                <div class="rcs-footer">
                    <span>View Report</span>
                    <span class="rcs-arrow">→</span>
                </div>
            </div>
            <div class="report-card-saas" onclick="location.href='{{ route('reports.fees.defaulters') }}'">
                <div class="d-flex align-items-start gap-2 mb-2">
                    <div class="rcs-icon" style="background:rgba(220,38,38,.08);color:#dc2626;"><i class="ti ti-users-x"></i></div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="rcs-title">Fee Defaulters</div>
                        <p class="rcs-desc">Track defaulters &amp; contacts</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="rcs-stat"><i class="ti ti-user-cancel"></i>Track defaulters</span>
                </div>
                <div class="rcs-footer">
                    <span>View Report</span>
                    <span class="rcs-arrow">→</span>
                </div>
            </div>
        </div>
    </div>
@endsection
