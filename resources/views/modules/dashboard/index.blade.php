@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumbs')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
    <!-- Hero Welcome Bar -->
    <div class="hero-welcome">
        <div class="hw-left">
            <div class="hw-avatar">
                <i class="ti ti-sparkles"></i>
            </div>
            <div>
                <div class="hw-greeting">Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ auth()->user()->name ?? 'Admin' }}</div>
                <div class="hw-sub">{{ \Carbon\Carbon::now()->format('l, F j, Y') }} &middot; {{ date('h:i A') }}</div>
            </div>
        </div>
        <div class="hw-meta d-flex gap-3">
            @if ($documentStats && ($documentStats['pending_count'] > 0 || $documentStats['expiring_count'] > 0))
                <span class="badge bg-warning text-dark"><i class="ti ti-file-alert me-1"></i>{{ $documentStats['pending_count'] }} pending</span>
                @if ($documentStats['expiring_count'] > 0)
                    <span class="badge bg-danger"><i class="ti ti-clock-exclamation me-1"></i>{{ $documentStats['expiring_count'] }} expiring</span>
                @endif
            @endif
        </div>
    </div>

    <!-- Hero KPI Row -->
    @php
        $totalStudents = $stats['students'] ?? 0;
        $totalTeachers = $stats['teachers'] ?? 0;
        $attendanceRate = $absentToday['percentage'] ?? 0;
        $totalCollected = $feeStats['total_collected'] ?? 0;
    @endphp
    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-md-6">
            <div class="erp-hero-card">
                <div>
                    <div class="hero-value">{{ number_format($totalStudents) }}</div>
                    <div class="hero-label">Total Students</div>
                    <div class="hero-sub">{{ $stats['active_classes'] ?? 0 }} active classes</div>
                </div>
                <div class="hero-icon" style="background:rgba(37,99,235,.1);color:#2563eb;">
                    <i class="ti ti-school"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="erp-hero-card">
                <div>
                    <div class="hero-value">{{ number_format($totalTeachers) }}</div>
                    <div class="hero-label">Teachers</div>
                    <div class="hero-trend trend-up">
                        <i class="ti ti-arrow-up"></i> {{ $stats['active_teachers'] ?? 0 }} active
                    </div>
                </div>
                <div class="hero-icon" style="background:rgba(100,116,139,.1);color:#64748b;">
                    <i class="ti ti-presentation"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="erp-hero-card">
                <div>
                    <div class="hero-value">{{ $attendanceRate }}%</div>
                    <div class="hero-label">Attendance Rate</div>
                    <div class="hero-sub">{{ $absentToday['present'] ?? 0 }} present today</div>
                </div>
                <div class="hero-icon" style="background:rgba(22,163,74,.1);color:#16a34a;">
                    <i class="ti ti-user-check"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="erp-hero-card">
                <div>
                    <div class="hero-value">{{ number_format($totalCollected, 0) }}</div>
                    <div class="hero-label">Total Collected</div>
                    <div class="hero-trend trend-up">
                        <i class="ti ti-trending-up"></i> {{ number_format($feeStats['monthly_collection'] ?? 0, 0) }} this month
                    </div>
                </div>
                <div class="hero-icon" style="background:rgba(245,158,11,.12);color:#d97706;">
                    <i class="ti ti-wallet"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Stat Row (compact) -->
    <div class="row g-3 mb-3">
        @foreach ([
            ['Active Classes', $stats['active_classes'] ?? 0, 'building', 'rgb(37,99,235)'],
            ['Exams', $stats['exams'] ?? 0, 'books', 'rgb(14,165,233)'],
            ['Today Schedules', $stats['today_schedules'] ?? 0, 'calendar-event', 'rgb(100,116,139)'],
            ['Logins Today', $stats['login_today'] ?? 0, 'login', 'rgb(245,158,11)'],
        ] as [$label, $value, $icon, $color])
            <div class="col-6 col-xl-3">
                <div class="nav-card-compact">
                    <div class="nc-icon" style="background:{{ $color }}1a;color:{{ $color }};">
                        <i class="ti ti-{{ $icon }}"></i>
                    </div>
                    <div>
                        <div class="nc-title">{{ number_format($value) }}</div>
                        <div class="nc-sub">{{ $label }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Analytics Row -->
    <div class="row g-3 mb-3">
        <div class="col-xl-7">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0"><i class="ti ti-chart-bar text-primary me-2"></i>Platform Overview</h3>
                    <span class="badge bg-primary-subtle text-primary fs-13">{{ $stats['activities'] ?? 0 }} total activities</span>
                </div>
                <div class="card-body" style="height:260px;">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0"><i class="ti ti-login text-primary me-2"></i>Recent Logins</h3>
                    <span class="badge bg-secondary-subtle text-secondary fs-13">{{ $recentLogins->count() }} entries</span>
                </div>
                <div class="card-body p-0">
                    <div class="compact-widget px-3 py-2">
                        @forelse ($recentLogins as $login)
                            <div class="compact-widget-item">
                                <div class="cw-avatar">
                                    {{ strtoupper(substr($login->user?->name ?? $login->email, 0, 2)) }}
                                </div>
                                <div class="cw-info">
                                    <div class="cw-name">{{ $login->user?->name ?? $login->email }}</div>
                                    <div class="cw-meta">{{ $login->ip_address }} &middot; {{ $login->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-secondary py-4">
                                <i class="ti ti-inbox d-block fs-3 mb-1 opacity-25"></i>
                                <small>No login activity yet.</small>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Row: Attendance + Fees + Upcoming Events -->
    <div class="row g-3">
        @if ($absentToday)
            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center">
                        <h3 class="card-title mb-0"><i class="ti ti-user-check text-primary me-2"></i>Today's Attendance</h3>
                        <a href="{{ route('reports.attendance.absent_students') }}" class="btn btn-sm btn-outline-primary ms-auto">
                            <i class="ti ti-eye me-1"></i>Details
                        </a>
                    </div>
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="position-relative" style="width:90px;height:90px;">
                                <canvas id="attendanceDonut" width="90" height="90"></canvas>
                                <div class="donut-center">
                                    <div class="donut-value">{{ $absentToday['percentage'] }}%</div>
                                    <div class="donut-label">Rate</div>
                                </div>
                            </div>
                            <div class="flex-grow-1 ps-3">
                                <div class="stat-inline-row flex-wrap">
                                    <div class="stat-inline-item">
                                        <span class="stat-inline-dot" style="background:#16a34a;"></span>
                                        <div>
                                            <div class="stat-inline-value">{{ $absentToday['present'] }}</div>
                                            <div class="stat-inline-label">Present</div>
                                        </div>
                                    </div>
                                    <div class="stat-inline-item">
                                        <span class="stat-inline-dot" style="background:#dc2626;"></span>
                                        <div>
                                            <div class="stat-inline-value">{{ $absentToday['absent'] }}</div>
                                            <div class="stat-inline-label">Absent</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($feeStats)
            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center">
                        <h3 class="card-title mb-0"><i class="ti ti-wallet text-primary me-2"></i>Fee Collection</h3>
                        @can('fees.view')
                            <a href="{{ route('reports.fees.index') }}" class="btn btn-sm btn-outline-primary ms-auto">
                                <i class="ti ti-chart-bar me-1"></i>Overview
                            </a>
                        @endcan
                    </div>
                    <div class="card-body py-3">
                        @php
                            $feeTotal = ($feeStats['total_collected'] ?? 0) + ($feeStats['pending_fees'] ?? 0);
                            $feeCollectedPct = $feeTotal > 0 ? round(($feeStats['total_collected'] / $feeTotal) * 100) : 0;
                        @endphp
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="position-relative" style="width:80px;height:80px;">
                                <canvas id="feeDonut" width="80" height="80"></canvas>
                                <div class="donut-center">
                                    <div class="donut-value" style="font-size:1.2rem;">{{ $feeCollectedPct }}%</div>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small text-secondary">Collected</span>
                                    <span class="small fw-semibold">{{ number_format($feeStats['total_collected'] ?? 0, 0) }}</span>
                                </div>
                                <div class="mini-progress-bar mb-2">
                                    <div class="mp-fill" style="width:{{ $feeCollectedPct }}%;background:#16a34a;"></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-secondary">Pending</span>
                                    <span class="small fw-semibold">{{ number_format($feeStats['pending_fees'] ?? 0, 0) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="small text-secondary text-center mt-2">
                            <i class="ti ti-calendar me-1"></i>{{ number_format($feeStats['monthly_collection'] ?? 0, 0) }} collected this month
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($upcomingEvents)
            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center">
                        <h3 class="card-title mb-0"><i class="ti ti-calendar-event text-primary me-2"></i>Upcoming Events</h3>
                        @can('academic_calendar.view')
                            <a href="{{ route('admin.calendar.index') }}" class="btn btn-sm btn-outline-primary ms-auto">
                                <i class="ti ti-arrow-right me-1"></i>View All
                            </a>
                        @endcan
                    </div>
                    <div class="card-body p-0">
                        <div class="compact-widget px-3 py-2">
                            @forelse ($upcomingEvents as $event)
                                <div class="compact-widget-item">
                                    <span class="badge {{ $event->event_type_badge }}" style="width:10px;height:10px;padding:0;border-radius:50%;flex-shrink:0;"></span>
                                    <div class="cw-info">
                                        <div class="cw-name">{{ $event->title }}</div>
                                        <div class="cw-meta">
                                            {{ $event->start_date->format('d M') }}
                                            @if ($event->end_date && $event->end_date->format('Y-m-d') !== $event->start_date->format('Y-m-d'))
                                                - {{ $event->end_date->format('d M') }}
                                            @endif
                                            @if ($event->location)
                                                &middot; {{ $event->location }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-secondary py-4">
                                    <i class="ti ti-calendar-off d-block fs-3 mb-1 opacity-25"></i>
                                    <small>No upcoming events.</small>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($documentStats && !$upcomingEvents)
            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center">
                        <h3 class="card-title mb-0"><i class="ti ti-file text-primary me-2"></i>Documents</h3>
                        @can('student_documents.view')
                            <a href="{{ route('admin.documents.index') }}" class="btn btn-sm btn-outline-primary ms-auto">
                                <i class="ti ti-arrow-right me-1"></i>View All
                            </a>
                        @endcan
                    </div>
                    <div class="card-body p-0">
                        <div class="compact-widget px-3 py-2">
                            @forelse ($documentStats['recent'] as $doc)
                                <div class="compact-widget-item">
                                    <i class="ti ti-file text-secondary fs-5"></i>
                                    <div class="cw-info">
                                        <div class="cw-name">{{ \Illuminate\Support\Str::limit($doc['student']['full_name'] ?? 'Unknown', 25) }}</div>
                                        <div class="cw-meta">{{ $doc['title'] ?? 'Untitled' }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-secondary py-4">
                                    <i class="ti ti-inbox d-block fs-3 mb-1 opacity-25"></i>
                                    <small>No documents uploaded yet.</small>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const Chart = await window.lazyChart();
            // Activity Overview Chart
            const actCanvas = document.getElementById('activityChart');
            if (actCanvas) {
                new Chart(actCanvas, {
                    type: 'bar',
                    data: {
                        labels: ['Users', 'Students', 'Teachers', 'Active Teachers', 'Exams', 'Published', 'Logins Today'],
                        datasets: [{
                            label: 'Count',
                            data: [
                                {{ $stats['users'] ?? 0 }},
                                {{ $stats['students'] ?? 0 }},
                                {{ $stats['teachers'] ?? 0 }},
                                {{ $stats['active_teachers'] ?? 0 }},
                                {{ $stats['exams'] ?? 0 }},
                                {{ $stats['published_exams'] ?? 0 }},
                                {{ $stats['login_today'] ?? 0 }},
                            ],
                            backgroundColor: [
                                'rgba(37,99,235,.7)', 'rgba(100,116,139,.7)', 'rgba(30,41,59,.7)',
                                'rgba(22,163,74,.7)', 'rgba(14,165,233,.7)', 'rgba(16,185,129,.7)',
                                'rgba(245,158,11,.7)'
                            ],
                            borderRadius: 4,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            // Attendance Donut
            const attCanvas = document.getElementById('attendanceDonut');
            if (attCanvas) {
                new Chart(attCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Present', 'Absent'],
                        datasets: [{
                            data: [{{ $absentToday['present'] ?? 0 }}, {{ $absentToday['absent'] ?? 0 }}],
                            backgroundColor: ['#16a34a', '#dc2626'],
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        responsive: false,
                        cutout: '75%',
                        plugins: { legend: { display: false }, tooltip: { enabled: false } }
                    }
                });
            }

            // Fee Donut
            const feeCanvas = document.getElementById('feeDonut');
            if (feeCanvas) {
                @php
                    $feeTotal2 = ($feeStats['total_collected'] ?? 0) + ($feeStats['pending_fees'] ?? 0);
                    $feeCollected = $feeStats['total_collected'] ?? 0;
                    $feePending = $feeStats['pending_fees'] ?? 0;
                @endphp
                new Chart(feeCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Collected', 'Pending'],
                        datasets: [{
                            data: [{{ $feeCollected }}, {{ $feePending }}],
                            backgroundColor: ['#16a34a', '#f59e0b'],
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        responsive: false,
                        cutout: '75%',
                        plugins: { legend: { display: false }, tooltip: { enabled: false } }
                    }
                });
            }
        });
    </script>
@endpush
