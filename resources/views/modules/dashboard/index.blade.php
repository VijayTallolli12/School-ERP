@extends('layouts.' . $dashboard->layout)

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumbs')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
    <div class="hero-welcome">
        <div class="hw-left">
            <div class="hw-avatar">
                <i class="ti ti-sparkles"></i>
            </div>
            <div>
                <div class="hw-greeting">{{ $dashboard->greeting }}</div>
                <div class="hw-sub">{{ \Carbon\Carbon::now()->format('l, F j, Y') }} &middot; {{ date('h:i A') }}</div>
            </div>
        </div>
    </div>

    @if(count($dashboard->statCards) > 0)
        <div class="row g-3 mb-3">
            @foreach($dashboard->statCards as $card)
                <div class="col-xl-3 col-md-6">
                    @if($card->route)
                        <a href="{{ $card->route }}" class="text-decoration-none">
                    @endif
                    <div class="erp-hero-card">
                        <div>
                            <div class="hero-value">{{ $card->formattedValue }}</div>
                            <div class="hero-label">{{ $card->label }}</div>
                            @if($card->trend)
                                <div class="hero-trend trend-{{ $card->trend === 'up' ? 'up' : 'down' }}">
                                    <i class="ti ti-arrow-{{ $card->trend === 'up' ? 'up' : 'down' }}"></i>
                                    @if($card->trendValue)
                                        {{ $card->trendValue }}
                                    @endif
                                </div>
                            @endif
                        </div>
                        @if($card->icon)
                            <div class="hero-icon" style="background:rgba(37,99,235,.1);color:#2563eb;">
                                <i class="ti ti-{{ $card->icon }}"></i>
                            </div>
                        @endif
                    </div>
                    @if($card->route)
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if(count($dashboard->widgets) > 0)
        <div class="row g-3 mb-3">
            @foreach($dashboard->widgets as $widget)
                <div class="col-xl-{{ $widget->cols ?? 4 }}">
                    <div class="card h-100">
                        <div class="card-header d-flex align-items-center">
                            @if($widget->icon)
                                <i class="ti ti-{{ $widget->icon }} text-primary me-2"></i>
                            @endif
                            <h3 class="card-title mb-0">{{ $widget->title }}</h3>
                            @if($widget->route)
                                <a href="{{ $widget->route }}" class="btn btn-sm btn-outline-primary ms-auto">
                                    <i class="ti ti-eye me-1"></i>Details
                                </a>
                            @endif
                        </div>
                        <div class="card-body py-3">
                            @if($widget->type === 'donut')
                                @php $rate = $widget->data['rate'] ?? 0; @endphp
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="position-relative" style="width:90px;height:90px;">
                                        <canvas id="donut-{{ $widget->key }}" width="90" height="90"></canvas>
                                        <div class="donut-center">
                                            <div class="donut-value">{{ $rate }}%</div>
                                            <div class="donut-label">Rate</div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ps-3">
                                        <div class="stat-inline-row flex-wrap">
                                            <div class="stat-inline-item">
                                                <span class="stat-inline-dot" style="background:#16a34a;"></span>
                                                <div>
                                                    <div class="stat-inline-value">{{ $widget->data['present'] ?? 0 }}</div>
                                                    <div class="stat-inline-label">Present</div>
                                                </div>
                                            </div>
                                            <div class="stat-inline-item">
                                                <span class="stat-inline-dot" style="background:#dc2626;"></span>
                                                <div>
                                                    <div class="stat-inline-value">{{ $widget->data['absent'] ?? 0 }}</div>
                                                    <div class="stat-inline-label">Absent</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @elseif($widget->type === 'list')
                                <div class="compact-widget px-3 py-2">
                                    @forelse($widget->data as $event)
                                        <div class="compact-widget-item">
                                            @if(is_object($event))
                                                <span class="badge {{ $event->event_type_badge ?? 'bg-primary' }}" style="width:10px;height:10px;padding:0;border-radius:50%;flex-shrink:0;"></span>
                                                <div class="cw-info">
                                                    <div class="cw-name">{{ $event->title }}</div>
                                                    <div class="cw-meta">
                                                        {{ $event->start_date?->format('d M') }}
                                                        @if($event->end_date && $event->end_date?->format('Y-m-d') !== $event->start_date?->format('Y-m-d'))
                                                            - {{ $event->end_date->format('d M') }}
                                                        @endif
                                                        @if($event->location)
                                                            &middot; {{ $event->location }}
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <i class="ti ti-file text-secondary fs-5"></i>
                                                <div class="cw-info">
                                                        <div class="cw-name">{{ $event['student']['full_name'] ?? $event['label'] ?? 'Unknown' }}</div>
                                                    <div class="cw-meta">{{ $event['title'] ?? $event['value'] ?? '' }}</div>
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="text-center text-secondary py-4">
                                            <i class="ti ti-calendar-off d-block fs-3 mb-1 opacity-25"></i>
                                            <small>{{ $widget->emptyMessage ?? 'No data available.' }}</small>
                                        </div>
                                    @endforelse
                                </div>
                            @elseif($widget->type === 'summary')
                                @php
                                    $collected = $widget->data['collected'] ?? $widget->data['paid'] ?? 0;
                                    $pending = $widget->data['pending'] ?? 0;
                                    $total = $collected + $pending;
                                    $pct = $total > 0 ? round(($collected / $total) * 100) : 0;
                                @endphp
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="position-relative" style="width:80px;height:80px;">
                                        <canvas id="summary-{{ $widget->key }}" width="80" height="80"></canvas>
                                        <div class="donut-center">
                                            <div class="donut-value" style="font-size:1.2rem;">{{ $pct }}%</div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="small text-secondary">@lang('Collected')</span>
                                            <span class="small fw-semibold">{{ number_format($collected, 0) }}</span>
                                        </div>
                                        <div class="mini-progress-bar mb-2">
                                            <div class="mp-fill" style="width:{{ $pct }}%;background:#16a34a;"></div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="small text-secondary">@lang('Pending')</span>
                                            <span class="small fw-semibold">{{ number_format($pending, 0) }}</span>
                                        </div>
                                    </div>
                                </div>
                            @elseif($widget->type === 'alerts')
                                <div class="d-flex gap-3">
                                    <div class="text-center flex-fill">
                                        <div class="fs-2 fw-bold text-danger">{{ $widget->data['expiring_count'] ?? 0 }}</div>
                                        <div class="small text-secondary">@lang('Expiring')</div>
                                    </div>
                                    <div class="text-center flex-fill">
                                        <div class="fs-2 fw-bold text-warning">{{ $widget->data['pending_count'] ?? 0 }}</div>
                                        <div class="small text-secondary">@lang('Pending')</div>
                                    </div>
                                </div>
                            @elseif($widget->type === 'stats_grid')
                                <div class="row g-2">
                                    @foreach($widget->data as $label => $value)
                                        <div class="col-6">
                                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                                <span class="small text-secondary">{{ ucwords(str_replace('_', ' ', $label)) }}</span>
                                                <span class="fw-semibold">{{ is_numeric($value) ? number_format($value) : $value }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if(count($dashboard->charts) > 0)
        <div class="row g-3 mb-3">
            @foreach($dashboard->charts as $chart)
                <div class="col-xl-7">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h3 class="card-title mb-0"><i class="ti ti-chart-bar text-primary me-2"></i>{{ $chart->title }}</h3>
                        </div>
                        <div class="card-body" style="height:{{ $chart->height }}px;">
                            <canvas id="chart-{{ $chart->key }}"></canvas>
                        </div>
                    </div>
                </div>
            @endforeach
            @if(count($dashboard->recentActivity) > 0)
                <div class="col-xl-5">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h3 class="card-title mb-0"><i class="ti ti-login text-primary me-2"></i>Recent Activity</h3>
                            <span class="badge bg-secondary-subtle text-secondary fs-13">{{ count($dashboard->recentActivity) }} entries</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="compact-widget px-3 py-2">
                                @forelse($dashboard->recentActivity as $activity)
                                    <div class="compact-widget-item">
                                        <div class="cw-avatar">
                                            {{ strtoupper(substr($activity['user']['name'] ?? $activity['email'] ?? '?', 0, 2)) }}
                                        </div>
                                        <div class="cw-info">
                                            <div class="cw-name">{{ $activity['user']['name'] ?? $activity['email'] ?? 'Unknown' }}</div>
                                            <div class="cw-meta">{{ $activity['ip_address'] ?? '' }} &middot; {{ isset($activity['created_at']) ? \Carbon\Carbon::parse($activity['created_at'])->diffForHumans() : '' }}</div>
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
            @endif
        </div>
    @endif

    @if(count($dashboard->quickActions) > 0)
        <div class="row g-3 mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title mb-0"><i class="ti ti-bolt text-primary me-2"></i>Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($dashboard->quickActions as $action)
                                @if(!$action->permission || auth()->user()->can($action->permission))
                                    <a href="{{ $action->route }}" class="btn btn-{{ $action->color ?? 'primary' }}">
                                        <i class="ti ti-{{ $action->icon }} me-1"></i>{{ $action->label }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(count($dashboard->insights) > 0)
        <div class="row g-3 mb-3">
            @foreach($dashboard->insights as $insight)
                <div class="col-md-6">
                    <div class="alert alert-{{ $insight['type'] ?? 'info' }} d-flex align-items-center mb-0">
                        <i class="ti ti-{{ $insight['type'] === 'tip' ? 'bulb' : 'info-circle' }} fs-4 me-2"></i>
                        <div class="flex-grow-1">
                            <strong>{{ $insight['title'] ?? '' }}</strong><br>
                            <small>{{ $insight['message'] ?? '' }}</small>
                        </div>
                        @if(isset($insight['action']))
                            <a href="{{ $insight['action']['route'] ?? '#' }}" class="btn btn-sm btn-outline-{{ $insight['type'] ?? 'info' }} ms-2">
                                {{ $insight['action']['label'] ?? 'View' }}
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const Chart = await window.lazyChart();

            document.querySelectorAll('[id^="donut-"]').forEach(canvas => {
                const id = canvas.id.replace('donut-', '');
                const widget = @json(collect($dashboard->widgets)->keyBy('key'));
                const data = widget[id]?.data ?? {};
                const present = data.present ?? 0;
                const absent = data.absent ?? 0;
                if (present === 0 && absent === 0) return;
                new Chart(canvas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Present', 'Absent'],
                        datasets: [{
                            data: [present, absent],
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
            });

            document.querySelectorAll('[id^="summary-"]').forEach(canvas => {
                const id = canvas.id.replace('summary-', '');
                const widget = @json(collect($dashboard->widgets)->keyBy('key'));
                const data = widget[id]?.data ?? {};
                const collected = (data.collected ?? data.paid ?? 0);
                const pending = data.pending ?? 0;
                if (collected === 0 && pending === 0) return;
                new Chart(canvas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Collected', 'Pending'],
                        datasets: [{
                            data: [collected, pending],
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
            });

            document.querySelectorAll('[id^="chart-"]').forEach(canvas => {
                const id = canvas.id.replace('chart-', '');
                const chart = @json(collect($dashboard->charts)->keyBy('key'));
                const config = chart[id];
                if (!config) return;
                new Chart(canvas, {
                    type: config.type ?? 'line',
                    data: {
                        labels: config.labels ?? [],
                        datasets: (config.datasets ?? []).map(ds => ({
                            ...ds,
                            borderWidth: ds.borderWidth ?? 2,
                            tension: ds.tension ?? 0.3,
                        })),
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: config.type !== 'bar' } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' } },
                            x: { grid: { display: false } }
                        },
                        ...(config.options ?? {}),
                    }
                });
            });
        });
    </script>
@endpush
