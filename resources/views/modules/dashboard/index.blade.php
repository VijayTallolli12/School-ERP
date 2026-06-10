@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumbs')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
    <!-- Welcome Greeting -->
    <div class="erp-welcome">
        <div class="d-flex align-items-center gap-3">
            <div class="welcome-avatar">
                <i class="ti ti-sparkles"></i>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <div class="welcome-title">Welcome back, {{ auth()->user()->name ?? 'Admin' }}</div>
                        <div class="welcome-subtitle">Manage your school operations efficiently.</div>
                    </div>
                    <div class="welcome-meta text-end">
                        <div>{{ \Carbon\Carbon::now()->format('l, F j, Y') }}</div>
                        <div><i class="ti ti-clock me-1"></i>{{ date('h:i A') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3">
        @foreach ([
            ['Users', $stats['users'], 'users', 'primary'],
            ['Students', $stats['students'], 'school', 'secondary'],
            ['Teachers', $stats['teachers'], 'presentation', 'dark'],
            ['Active Teachers', $stats['active_teachers'], 'user-check', 'success'],
            ['Today Schedules', $stats['today_schedules'], 'calendar-event', 'info'],
            ['Active Classes', $stats['active_classes'], 'building', 'secondary'],
            ['Exams', $stats['exams'], 'books', 'info'],
            ['Published Exams', $stats['published_exams'], 'file-check', 'success'],
            ['Logins Today', $stats['login_today'], 'login', 'warning'],
            ['Audit Events', $stats['activities'], 'clipboard-list', 'info'],
        ] as [$label, $value, $icon, $color])
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="erp-stat-card">
                    <div class="stat-info">
                        <h4 @class(['text-zero' => $value == 0])>{{ number_format($value) }}</h4>
                        <p>{{ $label }}</p>
                    </div>
                    <div class="stat-icon {{ $color }}">
                        <i class="ti ti-{{ $icon }}"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($absentToday)
        <div class="row g-3 mt-2">
            <div class="col-12">
                <h5 class="fw-semibold text-secondary mb-0">Today's Attendance</h5>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="erp-stat-card">
                    <div class="stat-info">
                        <h4 @class(['text-zero' => $absentToday['absent'] == 0])>{{ $absentToday['absent'] }}</h4>
                        <p>Absent Today</p>
                    </div>
                    <div class="stat-icon danger">
                        <i class="ti ti-user-x"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="erp-stat-card">
                    <div class="stat-info">
                        <h4>{{ $absentToday['percentage'] }}%</h4>
                        <p>Attendance Rate</p>
                    </div>
                    <div class="stat-icon primary">
                        <i class="ti ti-percentage"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="erp-stat-card">
                    <div class="stat-info">
                        <h4>{{ $absentToday['present'] }}</h4>
                        <p>Present Today</p>
                    </div>
                    <div class="stat-icon success">
                        <i class="ti ti-user-check"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3 d-flex align-items-center">
                <a href="{{ route('reports.attendance.absent_students') }}" class="btn btn-outline-danger w-100">
                    <i class="ti ti-eye me-1"></i> View Details
                </a>
            </div>
        </div>
    @endif

    @if($feeStats)
        <div class="row g-3 mt-2">
            <div class="col-12">
                <h5 class="fw-semibold text-secondary mb-0">Fees Overview</h5>
            </div>
            @foreach ([
                ['Total Collected', $feeStats['total_collected'], 'wallet', 'success'],
                ['Pending Fees', $feeStats['pending_fees'], 'hourglass', 'warning'],
                ['This Month', $feeStats['monthly_collection'], 'calendar-event', 'primary'],
            ] as [$label, $value, $icon, $color])
                <div class="col-12 col-sm-6 col-xl-4">
                    <div class="erp-stat-card">
                        <div class="stat-info">
                            <h4>{{ number_format($value, 2) }}</h4>
                            <p>{{ $label }}</p>
                        </div>
                        <div class="stat-icon {{ $color }}">
                            <i class="ti ti-{{ $icon }}"></i>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="row g-3 mt-2">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title mb-0">Activity Overview</h3>
                </div>
                <div class="card-body">
                    <canvas id="activityChart" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4 d-flex flex-column gap-3">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title mb-0">Recent Logins</h3>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse ($recentLogins as $login)
                            <div class="list-group-item px-3 py-2">
                                <div class="fw-semibold small">{{ $login->user?->name ?? $login->email }}</div>
                                <div class="small text-secondary">{{ $login->ip_address }} · {{ $login->created_at->diffForHumans() }}</div>
                            </div>
                        @empty
                            <div class="list-group-item px-3 py-4 text-center text-secondary">
                                <i class="ti ti-inbox d-block fs-3 mb-2 opacity-25"></i>
                                No login activity yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            @if ($upcomingEvents)
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h3 class="card-title mb-0">Upcoming Events</h3>
                        @can('academic_calendar.view')
                            <a href="{{ route('admin.calendar.index') }}" class="btn btn-sm btn-outline-primary ms-auto">
                                <i class="ti ti-calendar me-1"></i> View All
                            </a>
                        @endcan
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse ($upcomingEvents as $event)
                                <div class="list-group-item px-3 py-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge {{ $event->event_type_badge }}" style="width:8px;height:8px;padding:0;border-radius:50%"></span>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="fw-semibold small text-truncate">{{ $event->title }}</div>
                                            <div class="small text-secondary">
                                                {{ $event->start_date->format('d M') }}
                                                @if ($event->end_date && $event->end_date->format('Y-m-d') !== $event->start_date->format('Y-m-d'))
                                                    - {{ $event->end_date->format('d M') }}
                                                @endif
                                                @if ($event->location)
                                                    · {{ $event->location }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="list-group-item px-3 py-4 text-center text-secondary">
                                    <i class="ti ti-calendar-off d-block fs-3 mb-2 opacity-25"></i>
                                    No upcoming events.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif

            @if ($documentStats)
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h3 class="card-title mb-0">Documents</h3>
                        @can('student_documents.view')
                            <a href="{{ route('admin.documents.index') }}" class="btn btn-sm btn-outline-primary ms-auto">
                                <i class="ti ti-file-text me-1"></i> View All
                            </a>
                        @endcan
                    </div>
                    <div class="card-body p-0">
                        @if ($documentStats['pending_count'] > 0 || $documentStats['expiring_count'] > 0)
                            <div class="px-3 py-2 border-bottom">
                                <div class="d-flex gap-3">
                                    @if ($documentStats['pending_count'] > 0)
                                        <div class="small">
                                            <span class="badge bg-warning text-dark">{{ $documentStats['pending_count'] }}</span> pending verification
                                        </div>
                                    @endif
                                    @if ($documentStats['expiring_count'] > 0)
                                        <div class="small">
                                            <span class="badge bg-danger">{{ $documentStats['expiring_count'] }}</span> expiring soon
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                        <div class="list-group list-group-flush">
                            @forelse ($documentStats['recent'] as $doc)
                                <div class="list-group-item px-3 py-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="ti ti-file text-secondary fs-5"></i>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="fw-semibold small text-truncate">{{ $doc['title'] ?? 'Untitled' }}</div>
                                            <div class="small text-secondary">
                                                {{ \Illuminate\Support\Str::limit($doc['student']['full_name'] ?? 'Unknown', 30) }}
                                                @if (isset($doc['expiry_date']))
                                                    · Expires {{ \Carbon\Carbon::parse($doc['expiry_date'])->format('d M') }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="list-group-item px-3 py-4 text-center text-secondary">
                                    <i class="ti ti-inbox d-block fs-3 mb-2 opacity-25"></i>
                                    No documents uploaded yet.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const canvas = document.getElementById('activityChart');
            if (!canvas) return;
            new Chart(canvas, {
                type: 'line',
                data: {
                    labels: ['Users', 'Students', 'Teachers', 'Active Teachers', 'Exams', 'Published Exams', 'Roles', 'Logins', 'Audit'],
                    datasets: [{
                        label: 'Current counts',
                        data: @json(array_values($stats)),
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37,99,235,.1)',
                        fill: true,
                        tension: .35,
                        pointRadius: 3,
                        pointBackgroundColor: '#2563eb',
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        });
    </script>
@endpush
