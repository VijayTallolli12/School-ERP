@extends('layouts.admin')

@section("title", "Attendance Reports Dashboard")
@section("page-title", "Attendance Reports Dashboard")

@section("content")
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-3" style="width:48px;height:48px;background:rgba(22,163,74,.1);color:#16a34a;">
                        <i class="ti ti-user-check fs-4"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <p class="text-muted fs-6 fw-medium mb-0">Present Today</p>
                        <h3 class="fw-bold mb-0">{{ $stats['present_today'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-3" style="width:48px;height:48px;background:rgba(220,38,38,.1);color:#dc2626;">
                        <i class="ti ti-user-x fs-4"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <p class="text-muted fs-6 fw-medium mb-0">Absent Today</p>
                        <h3 class="fw-bold mb-0">{{ $stats['absent_today'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-3" style="width:48px;height:48px;background:rgba(14,165,233,.1);color:#0ea5e9;">
                        <i class="ti ti-clock fs-4"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <p class="text-muted fs-6 fw-medium mb-0">Late Today</p>
                        <h3 class="fw-bold mb-0">{{ $stats['late_today'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-3" style="width:48px;height:48px;background:rgba(217,119,6,.1);color:#d97706;">
                        <i class="ti ti-percentage fs-4"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <p class="text-muted fs-6 fw-medium mb-0">Attendance %</p>
                        <h3 class="fw-bold mb-0">{{ $stats['attendance_percentage'] ?? 0 }}%</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-transparent border-bottom-0 pb-0">
                <h6 class="fw-semibold mb-0"><i class="ti ti-chart-bar me-1 text-primary"></i> Attendance Trend (6 Months)</h6>
            </div>
            <div class="card-body">
                <div style="position:relative;height:220px">
                    <canvas id="attendanceTrendChart"></canvas>
                </div>
                <p class="text-muted small text-center mt-2 mb-0">Monthly present vs absent counts</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-transparent border-bottom-0 pb-0">
                <h6 class="fw-semibold mb-0"><i class="ti ti-chart-donut me-1 text-primary"></i> Today's Breakdown</h6>
            </div>
            <div class="card-body">
                <div style="position:relative;height:220px">
                    <canvas id="todayPieChart"></canvas>
                </div>
                <p class="text-muted small text-center mt-2 mb-0">Present, Absent, Late & Leave distribution</p>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-transparent">
        <h6 class="fw-semibold mb-0"><i class="ti ti-building-community me-1 text-primary"></i> Class-wise Attendance Today</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped mb-0">
                <thead>
                    <tr>
                        <th>Class</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Late</th>
                        <th>Leave</th>
                        <th>Total</th>
                        <th>%</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classWiseData ?? [] as $item)
                        <tr>
                            <td>{{ $item['class'] }}</td>
                            <td>{{ $item['present'] }}</td>
                            <td>{{ $item['absent'] }}</td>
                            <td>{{ $item['late'] }}</td>
                            <td>{{ $item['leave'] }}</td>
                            <td>{{ $item['total'] }}</td>
                            <td>{{ $item['percentage'] }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">No attendance data for today.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mb-0">
    <h5 class="fw-semibold mb-3" style="font-size:0.95rem;"><i class="ti ti-list me-1 text-primary"></i> Available Reports</h5>
    <div class="report-grid">
        <div class="report-card-saas" onclick="location.href='{{ route('reports.attendance.daily') }}'">
            <div class="d-flex align-items-start gap-2 mb-2">
                <div class="rcs-icon" style="background:rgba(37,99,235,.08);color:#2563eb;"><i class="ti ti-calendar-day"></i></div>
                <div class="flex-grow-1 min-w-0">
                    <div class="rcs-title">Daily Attendance</div>
                    <p class="rcs-desc">View daily attendance records with filters.</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="rcs-stat"><i class="ti ti-database"></i>{{ number_format($reportStats['daily'] ?? 0) }} records</span>
            </div>
            <div class="rcs-footer">
                <span>View Report</span>
                <span class="rcs-arrow">→</span>
            </div>
        </div>
        <div class="report-card-saas" onclick="location.href='{{ route('reports.attendance.monthly') }}'">
            <div class="d-flex align-items-start gap-2 mb-2">
                <div class="rcs-icon" style="background:rgba(22,163,74,.08);color:#16a34a;"><i class="ti ti-calendar-stats"></i></div>
                <div class="flex-grow-1 min-w-0">
                    <div class="rcs-title">Monthly Attendance</div>
                    <p class="rcs-desc">Monthly breakdown by class and section.</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="rcs-stat"><i class="ti ti-database"></i>{{ number_format($reportStats['monthly'] ?? 0) }} summaries</span>
            </div>
            <div class="rcs-footer">
                <span>View Report</span>
                <span class="rcs-arrow">→</span>
            </div>
        </div>
        <div class="report-card-saas" onclick="location.href='{{ route('reports.attendance.class_wise') }}'">
            <div class="d-flex align-items-start gap-2 mb-2">
                <div class="rcs-icon" style="background:rgba(14,165,233,.08);color:#0ea5e9;"><i class="ti ti-building-community"></i></div>
                <div class="flex-grow-1 min-w-0">
                    <div class="rcs-title">Class-wise Attendance</div>
                    <p class="rcs-desc">Summary of attendance across classes.</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="rcs-stat"><i class="ti ti-database"></i>{{ number_format($reportStats['class_wise'] ?? 0) }} classes</span>
            </div>
            <div class="rcs-footer">
                <span>View Report</span>
                <span class="rcs-arrow">→</span>
            </div>
        </div>
        <div class="report-card-saas" onclick="location.href='{{ route('reports.attendance.absent_students') }}'">
            <div class="d-flex align-items-start gap-2 mb-2">
                <div class="rcs-icon" style="background:rgba(220,38,38,.08);color:#dc2626;"><i class="ti ti-users-x"></i></div>
                <div class="flex-grow-1 min-w-0">
                    <div class="rcs-title">Absent Students</div>
                    <p class="rcs-desc">Track absent students with consecutive days.</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="rcs-stat"><i class="ti ti-database"></i>{{ number_format($reportStats['absent_students'] ?? 0) }} records</span>
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

    const chartData = @json($chartData ?? []);

    const trendCtx = document.getElementById('attendanceTrendChart');
    if (trendCtx && chartData.trendLabels && chartData.trendLabels.length > 0) {
        new Chart(trendCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: chartData.trendLabels,
                datasets: [
                    { label: 'Present', data: chartData.trendPresent, backgroundColor: 'rgba(22,163,74,.7)', borderRadius: 4 },
                    { label: 'Absent', data: chartData.trendAbsent, backgroundColor: 'rgba(220,38,38,.7)', borderRadius: 4 },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { boxWidth: 12, padding: 12, font: { size: 11 } } } },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.06)' } }
                }
            }
        });
    }

    const pieCtx = document.getElementById('todayPieChart');
    if (pieCtx && chartData.todayBreakdown) {
        var td = chartData.todayBreakdown;
        if (td.present > 0 || td.absent > 0 || td.late > 0 || td.leave > 0) {
            new Chart(pieCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Present', 'Absent', 'Late', 'Leave'],
                    datasets: [{
                        data: [td.present, td.absent, td.late, td.leave],
                        backgroundColor: ['rgba(22,163,74,.7)', 'rgba(220,38,38,.7)', 'rgba(14,165,233,.7)', 'rgba(217,119,6,.7)'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: { position: 'right', labels: { boxWidth: 12, padding: 12, font: { size: 11 } } }
                    }
                }
            });
        }
    }
});
</script>
@endpush
