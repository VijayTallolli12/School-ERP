@extends('layouts.admin')

@section('page-title', 'Attendance Reports')
@section('title', 'Attendance Reports')

@section('breadcrumbs')
    <li class="breadcrumb-item active">Attendance Reports</li>
@endsection

@section('content')
    <!-- KPI Row -->
    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-md-6">
            <div class="erp-hero-card">
                <div>
                    <div class="hero-value">{{ $todaySummary['present'] ?? 0 }}</div>
                    <div class="hero-label">Present Today</div>
                    <div class="hero-trend trend-up">
                        <i class="ti ti-arrow-up"></i> {{ $todaySummary['present_percent'] ?? 0 }}% of total
                    </div>
                </div>
                <div class="hero-icon" style="background:rgba(22,163,74,.1);color:#16a34a;">
                    <i class="ti ti-circle-check"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="erp-hero-card">
                <div>
                    <div class="hero-value">{{ $todaySummary['absent'] ?? 0 }}</div>
                    <div class="hero-label">Absent Today</div>
                    <div class="hero-trend trend-down">
                        <i class="ti ti-arrow-down"></i> {{ $todaySummary['absent_percent'] ?? 0 }}% of total
                    </div>
                </div>
                <div class="hero-icon" style="background:rgba(220,38,38,.1);color:#dc2626;">
                    <i class="ti ti-circle-x"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="erp-hero-card">
                <div>
                    <div class="hero-value">{{ $todaySummary['late'] ?? 0 }}</div>
                    <div class="hero-label">Late Arrivals</div>
                    <div class="hero-trend trend-down" style="color:#d97706;">
                        <i class="ti ti-clock"></i> {{ $todaySummary['late_percent'] ?? 0 }}% of total
                    </div>
                </div>
                <div class="hero-icon" style="background:rgba(245,158,11,.12);color:#d97706;">
                    <i class="ti ti-clock"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="erp-hero-card">
                <div>
                    <div class="hero-value">{{ $todaySummary['leave'] ?? 0 }}</div>
                    <div class="hero-label">On Leave</div>
                    <div class="hero-trend trend-neutral">
                        <i class="ti ti-clipboard-list"></i> {{ $todaySummary['leave_percent'] ?? 0 }}% of total
                    </div>
                </div>
                <div class="hero-icon" style="background:rgba(14,165,233,.1);color:#0ea5e9;">
                    <i class="ti ti-clipboard-list"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-3 mb-3">
        <div class="col-xl-7">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0"><i class="ti ti-chart-line text-primary me-2"></i>Attendance Trend</h3>
                    <span class="badge bg-secondary-subtle text-secondary fs-13">Last 30 Days</span>
                </div>
                <div class="card-body" style="height:240px;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0"><i class="ti ti-chart-donut text-primary me-2"></i>Today's Distribution</h3>
                    <span class="badge bg-secondary-subtle text-secondary fs-13">{{ ($todaySummary['present'] ?? 0) + ($todaySummary['absent'] ?? 0) + ($todaySummary['late'] ?? 0) + ($todaySummary['leave'] ?? 0) }} total</span>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center" style="min-height:240px;">
                    <div class="position-relative">
                        <canvas id="distributionChart" height="220" width="220"></canvas>
                        <div class="donut-center">
                            <div class="donut-value">{{ $todaySummary['present_percent'] ?? 0 }}%</div>
                            <div class="donut-label">Present</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Class-wise Attendance -->
    <div class="row g-3 mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0"><i class="ti ti-chart-bar text-primary me-2"></i>Class-wise Attendance</h3>
                    <span class="badge bg-secondary-subtle text-secondary fs-13">Today</span>
                </div>
                <div class="card-body" style="height:200px;">
                    <canvas id="classWiseChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Navigation Cards -->
    <div class="mb-0">
        <h5 class="fw-semibold mb-3" style="font-size:0.95rem;"><i class="ti ti-list me-1 text-primary"></i> Attendance Reports</h5>
        <div class="report-grid">
            <div class="report-card-saas" onclick="location.href='{{ route('reports.attendance.daily') }}'">
                <div class="d-flex align-items-start gap-2 mb-2">
                    <div class="rcs-icon" style="background:rgba(37,99,235,.08);color:#2563eb;"><i class="ti ti-calendar-check"></i></div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="rcs-title">Daily Report</div>
                        <p class="rcs-desc">Attendance for a date</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="rcs-stat"><i class="ti ti-circle-check"></i>{{ $todaySummary['present'] ?? 0 }} present today</span>
                </div>
                <div class="rcs-footer">
                    <span>View Report</span>
                    <span class="rcs-arrow">→</span>
                </div>
            </div>
            <div class="report-card-saas" onclick="location.href='{{ route('reports.attendance.monthly') }}'">
                <div class="d-flex align-items-start gap-2 mb-2">
                    <div class="rcs-icon" style="background:rgba(14,165,233,.08);color:#0ea5e9;"><i class="ti ti-calendar-month"></i></div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="rcs-title">Monthly Report</div>
                        <p class="rcs-desc">Trends for a month</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="rcs-stat"><i class="ti ti-trending-up"></i>{{ count($trendData ?? []) }} days tracked</span>
                </div>
                <div class="rcs-footer">
                    <span>View Report</span>
                    <span class="rcs-arrow">→</span>
                </div>
            </div>
            <div class="report-card-saas" onclick="location.href='{{ route('reports.attendance.class_wise') }}'">
                <div class="d-flex align-items-start gap-2 mb-2">
                    <div class="rcs-icon" style="background:rgba(245,158,11,.08);color:#d97706;"><i class="ti ti-chart-bar"></i></div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="rcs-title">Class-wise Report</div>
                        <p class="rcs-desc">Across classes</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="rcs-stat"><i class="ti ti-database"></i>{{ count($classWiseData ?? []) }} classes</span>
                </div>
                <div class="rcs-footer">
                    <span>View Report</span>
                    <span class="rcs-arrow">→</span>
                </div>
            </div>
            <div class="report-card-saas" onclick="location.href='{{ route('reports.attendance.absent_students') }}'">
                <div class="d-flex align-items-start gap-2 mb-2">
                    <div class="rcs-icon" style="background:rgba(220,38,38,.08);color:#dc2626;"><i class="ti ti-user-cancel"></i></div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="rcs-title">Absent Students</div>
                        <p class="rcs-desc">Track absent patterns</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="rcs-stat"><i class="ti ti-alert-circle"></i>{{ $todaySummary['absent'] ?? 0 }} absent today</span>
                </div>
                <div class="rcs-footer">
                    <span>View Report</span>
                    <span class="rcs-arrow">→</span>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', async function () {
            const Chart = await window.lazyChart();
            const trendData = @json($trendData ?? []);
            const classWiseData = @json($classWiseData ?? []);
            const todaySummary = @json($todaySummary ?? []);

            const chartColors = {
                success: '#16a34a',
                danger: '#dc2626',
                warning: '#d97706',
                info: '#0ea5e9',
                primary: '#2563eb',
                successBg: 'rgba(22,163,74,.12)',
                dangerBg: 'rgba(220,38,38,.1)',
                warningBg: 'rgba(217,119,6,.1)',
                infoBg: 'rgba(14,165,233,.1)',
                primaryBg: 'rgba(37,99,235,.1)',
            };

            function formatDate(dateStr) {
                const d = new Date(dateStr + 'T00:00:00');
                return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            }

            const trendCtx = document.getElementById('trendChart');
            if (trendData.length > 0 && trendCtx) {
                new Chart(trendCtx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: trendData.map(d => formatDate(d.date)),
                        datasets: [
                            {
                                label: 'Present %',
                                data: trendData.map(d => d.present_percent),
                                borderColor: chartColors.success,
                                backgroundColor: chartColors.successBg,
                                fill: true,
                                tension: 0.3,
                                pointRadius: 2,
                                pointHoverRadius: 5,
                            },
                            {
                                label: 'Absent %',
                                data: trendData.map(d => d.absent_percent),
                                borderColor: chartColors.danger,
                                backgroundColor: chartColors.dangerBg,
                                fill: true,
                                tension: 0.3,
                                pointRadius: 2,
                                pointHoverRadius: 5,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { intersect: false, mode: 'index' },
                        plugins: {
                            legend: { position: 'top', labels: { usePointStyle: true, padding: 16, boxWidth: 8, font: { size: 11 } } },
                        },
                        scales: {
                            y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%', font: { size: 10 } } },
                            x: { grid: { display: false }, ticks: { maxTicksLimit: 10, font: { size: 10 } } }
                        }
                    }
                });
            } else if (trendCtx) {
                trendCtx.parentNode.innerHTML =
                    '<div class="text-center text-muted py-4"><i class="ti ti-cloud-off fs-4 d-block mb-1"></i><small>No trend data available</small></div>';
            }

            const distCtx = document.getElementById('distributionChart');
            if (distCtx) {
                new Chart(distCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Present', 'Absent', 'Late', 'Leave'],
                        datasets: [{
                            data: [
                                todaySummary.present || 0,
                                todaySummary.absent || 0,
                                todaySummary.late || 0,
                                todaySummary.leave || 0,
                            ],
                            backgroundColor: [
                                chartColors.success,
                                chartColors.danger,
                                chartColors.warning,
                                chartColors.info,
                            ],
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '75%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { usePointStyle: true, padding: 12, boxWidth: 8, font: { size: 11 } }
                            }
                        },
                    }
                });
            }

            const cwCtx = document.getElementById('classWiseChart');
            if (classWiseData.length > 0 && cwCtx) {
                new Chart(cwCtx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: classWiseData.map(d => d.class_section),
                        datasets: [
                            {
                                label: 'Present',
                                data: classWiseData.map(d => d.present || 0),
                                backgroundColor: chartColors.success,
                                borderRadius: 3,
                                borderSkipped: false,
                                barPercentage: 0.7,
                            },
                            {
                                label: 'Absent',
                                data: classWiseData.map(d => d.absent || 0),
                                backgroundColor: chartColors.danger,
                                borderRadius: 3,
                                borderSkipped: false,
                                barPercentage: 0.7,
                            },
                            {
                                label: 'Late',
                                data: classWiseData.map(d => d.late || 0),
                                backgroundColor: chartColors.warning,
                                borderRadius: 3,
                                borderSkipped: false,
                                barPercentage: 0.7,
                            },
                            {
                                label: 'Leave',
                                data: classWiseData.map(d => d.leave || 0),
                                backgroundColor: chartColors.info,
                                borderRadius: 3,
                                borderSkipped: false,
                                barPercentage: 0.7,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { intersect: false, mode: 'index' },
                        plugins: {
                            legend: { position: 'top', labels: { usePointStyle: true, padding: 12, boxWidth: 8, font: { size: 11 } } },
                        },
                        scales: {
                            y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } } },
                            x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                        }
                    }
                });
            } else if (cwCtx) {
                cwCtx.parentNode.innerHTML =
                    '<div class="text-center text-muted py-4"><i class="ti ti-cloud-off fs-4 d-block mb-1"></i><small>No class data available for today</small></div>';
            }
        });
    </script>
@endpush
