@extends('layouts.admin')

@section('page-title', 'Attendance Reports')
@section('title', 'Attendance Reports')

@section('breadcrumbs')
    <li class="breadcrumb-item active">Attendance Reports</li>
@endsection

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted fs-13 mb-1 text-uppercase fw-medium">Present</p>
                            <h3 class="fw-bold mb-1 text-success">{{ $todaySummary['present'] ?? 0 }}</h3>
                            <span class="badge bg-success-subtle text-success">
                                {{ $todaySummary['present_percent'] ?? 0 }}%
                            </span>
                            <span class="text-muted ms-1 small">of total</span>
                        </div>
                        <div class="fs-32 text-success opacity-50">
                            <i class="ti ti-circle-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-start border-danger border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted fs-13 mb-1 text-uppercase fw-medium">Absent</p>
                            <h3 class="fw-bold mb-1 text-danger">{{ $todaySummary['absent'] ?? 0 }}</h3>
                            <span class="badge bg-danger-subtle text-danger">
                                {{ $todaySummary['absent_percent'] ?? 0 }}%
                            </span>
                            <span class="text-muted ms-1 small">of total</span>
                        </div>
                        <div class="fs-32 text-danger opacity-50">
                            <i class="ti ti-circle-x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted fs-13 mb-1 text-uppercase fw-medium">Late</p>
                            <h3 class="fw-bold mb-1 text-warning">{{ $todaySummary['late'] ?? 0 }}</h3>
                            <span class="badge bg-warning-subtle text-warning">
                                {{ $todaySummary['late_percent'] ?? 0 }}%
                            </span>
                            <span class="text-muted ms-1 small">of total</span>
                        </div>
                        <div class="fs-32 text-warning opacity-50">
                            <i class="ti ti-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-start border-info border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted fs-13 mb-1 text-uppercase fw-medium">Leave</p>
                            <h3 class="fw-bold mb-1 text-info">{{ $todaySummary['leave'] ?? 0 }}</h3>
                            <span class="badge bg-info-subtle text-info">
                                {{ $todaySummary['leave_percent'] ?? 0 }}%
                            </span>
                            <span class="text-muted ms-1 small">of total</span>
                        </div>
                        <div class="fs-32 text-info opacity-50">
                            <i class="ti ti-clipboard-list"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Attendance Trend <span class="text-muted fs-13 fw-normal">(Last 30 Days)</span></h5>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" height="280"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Today's Distribution</h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 330px;">
                    <canvas id="distributionChart" height="280"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Class-wise Attendance <span class="text-muted fs-13 fw-normal">(Today)</span></h5>
                </div>
                <div class="card-body">
                    <canvas id="classWiseChart" height="220"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('reports.attendance.daily') }}" class="text-decoration-none">
                <div class="card h-100 hover-shadow transition-shadow">
                    <div class="card-body text-center py-4">
                        <div class="fs-36 text-primary mb-3">
                            <i class="ti ti-calendar-check"></i>
                        </div>
                        <h5 class="fw-semibold">Daily Report</h5>
                        <p class="text-muted small mb-0">View attendance for a specific date</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('reports.attendance.monthly') }}" class="text-decoration-none">
                <div class="card h-100 hover-shadow transition-shadow">
                    <div class="card-body text-center py-4">
                        <div class="fs-36 text-info mb-3">
                            <i class="ti ti-calendar-month"></i>
                        </div>
                        <h5 class="fw-semibold">Monthly Report</h5>
                        <p class="text-muted small mb-0">View attendance trends for a month</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('reports.attendance.class_wise') }}" class="text-decoration-none">
                <div class="card h-100 hover-shadow transition-shadow">
                    <div class="card-body text-center py-4">
                        <div class="fs-36 text-warning mb-3">
                            <i class="ti ti-chart-bar"></i>
                        </div>
                        <h5 class="fw-semibold">Class-wise Report</h5>
                        <p class="text-muted small mb-0">Compare attendance across classes</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('reports.attendance.absent_students') }}" class="text-decoration-none">
                <div class="card h-100 hover-shadow transition-shadow">
                    <div class="card-body text-center py-4">
                        <div class="fs-36 text-danger mb-3">
                            <i class="ti ti-user-cancel"></i>
                        </div>
                        <h5 class="fw-semibold">Absent Students</h5>
                        <p class="text-muted small mb-0">Track absent students and patterns</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const trendData = @json($trendData ?? []);
            const classWiseData = @json($classWiseData ?? []);
            const todaySummary = @json($todaySummary ?? []);

            const chartColors = {
                success: '#198754',
                danger: '#dc3545',
                warning: '#ffc107',
                info: '#0dcaf0',
                primary: '#0d6efd',
                successBg: 'rgba(25, 135, 84, 0.15)',
                dangerBg: 'rgba(220, 53, 69, 0.15)',
                warningBg: 'rgba(255, 193, 7, 0.15)',
                infoBg: 'rgba(13, 202, 240, 0.15)',
                primaryBg: 'rgba(13, 110, 253, 0.15)',
            };

            function formatDate(dateStr) {
                const d = new Date(dateStr + 'T00:00:00');
                return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            }

            if (trendData.length > 0) {
                const ctx = document.getElementById('trendChart').getContext('2d');
                new Chart(ctx, {
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
                                pointRadius: 3,
                                pointHoverRadius: 6,
                            },
                            {
                                label: 'Absent %',
                                data: trendData.map(d => d.absent_percent),
                                borderColor: chartColors.danger,
                                backgroundColor: chartColors.dangerBg,
                                fill: true,
                                tension: 0.3,
                                pointRadius: 3,
                                pointHoverRadius: 6,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { intersect: false, mode: 'index' },
                        plugins: {
                            legend: { position: 'top', labels: { usePointStyle: true, padding: 20 } },
                        },
                        scales: {
                            y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } },
                            x: { grid: { display: false }, ticks: { maxTicksLimit: 15 } }
                        }
                    }
                });
            } else {
                document.getElementById('trendChart').parentNode.innerHTML =
                    '<div class="text-center text-muted py-5"><i class="ti ti-cloud-off fs-32 d-block mb-2"></i>No trend data available</div>';
            }

            const distCtx = document.getElementById('distributionChart').getContext('2d');
            new Chart(distCtx, {
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
                        borderWidth: 2,
                        borderColor: '#fff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { usePointStyle: true, padding: 16, boxWidth: 10 }
                        }
                    },
                    cutout: '65%',
                }
            });

            if (classWiseData.length > 0) {
                const cwCtx = document.getElementById('classWiseChart').getContext('2d');
                new Chart(cwCtx, {
                    type: 'bar',
                    data: {
                        labels: classWiseData.map(d => d.class_section),
                        datasets: [
                            {
                                label: 'Present',
                                data: classWiseData.map(d => d.present || 0),
                                backgroundColor: chartColors.success,
                                borderRadius: 4,
                            },
                            {
                                label: 'Absent',
                                data: classWiseData.map(d => d.absent || 0),
                                backgroundColor: chartColors.danger,
                                borderRadius: 4,
                            },
                            {
                                label: 'Late',
                                data: classWiseData.map(d => d.late || 0),
                                backgroundColor: chartColors.warning,
                                borderRadius: 4,
                            },
                            {
                                label: 'Leave',
                                data: classWiseData.map(d => d.leave || 0),
                                backgroundColor: chartColors.info,
                                borderRadius: 4,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { intersect: false, mode: 'index' },
                        plugins: {
                            legend: { position: 'top', labels: { usePointStyle: true, padding: 16 } },
                        },
                        scales: {
                            y: { beginAtZero: true, ticks: { stepSize: 1 } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            } else {
                document.getElementById('classWiseChart').parentNode.innerHTML =
                    '<div class="text-center text-muted py-5"><i class="ti ti-cloud-off fs-32 d-block mb-2"></i>No class data available for today</div>';
            }
        });
    </script>
@endpush
