@extends('layouts.admin')

@section("title", "Parent Reports Dashboard")
@section("page-title", "Parent Reports Dashboard")

@section("content")
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-3" style="width:48px;height:48px;background:rgba(37,99,235,.1);color:#2563eb;">
                        <i class="ti ti-users fs-4"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <p class="text-muted fs-6 fw-medium mb-0">Total Parents</p>
                        <h3 class="fw-bold mb-0">{{ $stats['total_parents'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-3" style="width:48px;height:48px;background:rgba(22,163,74,.1);color:#16a34a;">
                        <i class="ti ti-user-check fs-4"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <p class="text-muted fs-6 fw-medium mb-0">Active Parents</p>
                        <h3 class="fw-bold mb-0">{{ $stats['active_parents'] ?? 0 }}</h3>
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
                        <i class="ti ti-link fs-4"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <p class="text-muted fs-6 fw-medium mb-0">Mapped Parents</p>
                        <h3 class="fw-bold mb-0">{{ $stats['mapped_parents'] ?? 0 }}</h3>
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
                        <i class="ti ti-school fs-4"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <p class="text-muted fs-6 fw-medium mb-0">Linked Students</p>
                        <h3 class="fw-bold mb-0">{{ $stats['linked_students'] ?? 0 }}</h3>
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
                <h6 class="fw-semibold mb-0"><i class="ti ti-users-group me-1 text-primary"></i> Parent Status</h6>
            </div>
            <div class="card-body">
                <div style="position:relative;height:220px">
                    <canvas id="parentStatusChart"></canvas>
                </div>
                <p class="text-muted small text-center mt-2 mb-0">Active vs inactive parent distribution</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-transparent border-bottom-0 pb-0">
                <h6 class="fw-semibold mb-0"><i class="ti ti-chart-pie me-1 text-primary"></i> Linked Students per Parent</h6>
            </div>
            <div class="card-body">
                <div style="position:relative;height:220px">
                    <canvas id="linkedStudentsChart"></canvas>
                </div>
                <p class="text-muted small text-center mt-2 mb-0">How many parents have 1, 2, 3, or 4+ linked students</p>
            </div>
        </div>
    </div>
</div>

<div class="mb-0">
    <h5 class="fw-semibold mb-3" style="font-size:0.95rem;"><i class="ti ti-list me-1 text-primary"></i> Available Reports</h5>
    <div class="report-grid">
        <div class="report-card-saas" onclick="location.href='{{ route('reports.parents.list') }}'">
            <div class="d-flex align-items-start gap-2 mb-2">
                <div class="rcs-icon" style="background:rgba(37,99,235,.08);color:#2563eb;"><i class="ti ti-address-book"></i></div>
                <div class="flex-grow-1 min-w-0">
                    <div class="rcs-title">Parent List</div>
                    <p class="rcs-desc">View parent contact, occupation, status, linked student, and class details.</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="rcs-stat"><i class="ti ti-database"></i>{{ number_format($reportStats['parent_list'] ?? 0) }} parents</span>
            </div>
            <div class="rcs-footer">
                <span>View Report</span>
                <span class="rcs-arrow">→</span>
            </div>
        </div>
        <div class="report-card-saas" onclick="location.href='{{ route('reports.parents.mapping') }}'">
            <div class="d-flex align-items-start gap-2 mb-2">
                <div class="rcs-icon" style="background:rgba(22,163,74,.08);color:#16a34a;"><i class="ti ti-relation-many-to-many"></i></div>
                <div class="flex-grow-1 min-w-0">
                    <div class="rcs-title">Parent Student Mapping</div>
                    <p class="rcs-desc">Review parent-student relationships and primary contact assignments.</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="rcs-stat"><i class="ti ti-database"></i>{{ number_format($reportStats['parent_mapping'] ?? 0) }} mapped</span>
            </div>
            <div class="rcs-footer">
                <span>View Report</span>
                <span class="rcs-arrow">→</span>
            </div>
        </div>
        <div class="report-card-saas" onclick="location.href='{{ route('reports.parents.activity_summary') }}'">
            <div class="d-flex align-items-start gap-2 mb-2">
                <div class="rcs-icon" style="background:rgba(14,165,233,.08);color:#0ea5e9;"><i class="ti ti-activity-heartbeat"></i></div>
                <div class="flex-grow-1 min-w-0">
                    <div class="rcs-title">Activity Summary</div>
                    <p class="rcs-desc">Summarize notifications and portal activity signals by parent.</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="rcs-stat"><i class="ti ti-clock"></i> Real-time</span>
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

    const statusCtx = document.getElementById('parentStatusChart');
    if (statusCtx && chartData.statusLabels && chartData.statusLabels.length > 0) {
        new Chart(statusCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: chartData.statusLabels,
                datasets: [{
                    data: chartData.statusCounts,
                    backgroundColor: ['rgba(22,163,74,.7)', 'rgba(220,38,38,.6)'],
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

    const linkedCtx = document.getElementById('linkedStudentsChart');
    if (linkedCtx && chartData.engagementLabels && chartData.engagementLabels.length > 0) {
        new Chart(linkedCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: chartData.engagementLabels,
                datasets: [{
                    label: 'Parents',
                    data: chartData.engagementCounts,
                    backgroundColor: ['rgba(37,99,235,.7)', 'rgba(22,163,74,.7)', 'rgba(14,165,233,.7)', 'rgba(217,119,6,.7)'],
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.06)' }, ticks: { stepSize: 1 } }
                }
            }
        });
    }
});
</script>
@endpush
