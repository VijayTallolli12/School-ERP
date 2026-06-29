@extends('layouts.admin')

@section("title", "Teacher Reports Dashboard")
@section("page-title", "Teacher Reports Dashboard")

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
                        <p class="text-muted fs-6 fw-medium mb-0">Total Teachers</p>
                        <h3 class="fw-bold mb-0">{{ $stats['total_teachers'] ?? 0 }}</h3>
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
                        <p class="text-muted fs-6 fw-medium mb-0">Active Teachers</p>
                        <h3 class="fw-bold mb-0">{{ $stats['active_teachers'] ?? 0 }}</h3>
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
                        <i class="ti ti-chalkboard fs-4"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <p class="text-muted fs-6 fw-medium mb-0">Class Teachers</p>
                        <h3 class="fw-bold mb-0">{{ $stats['class_teachers'] ?? 0 }}</h3>
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
                        <i class="ti ti-book fs-4"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <p class="text-muted fs-6 fw-medium mb-0">Subject Allocations</p>
                        <h3 class="fw-bold mb-0">{{ $stats['subject_allocations'] ?? 0 }}</h3>
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
                <h6 class="fw-semibold mb-0"><i class="ti ti-book me-1 text-primary"></i> Teachers by Subject</h6>
            </div>
            <div class="card-body">
                <div style="position:relative;height:220px">
                    <canvas id="teacherSubjectChart"></canvas>
                </div>
                <p class="text-muted small text-center mt-2 mb-0">Distribution of teachers across subjects</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-transparent border-bottom-0 pb-0">
                <h6 class="fw-semibold mb-0"><i class="ti ti-trending-up me-1 text-primary"></i> Attendance Trend (6 Months)</h6>
            </div>
            <div class="card-body">
                <div style="position:relative;height:220px">
                    <canvas id="teacherAttendanceChart"></canvas>
                </div>
                <p class="text-muted small text-center mt-2 mb-0">Monthly present vs absent counts</p>
            </div>
        </div>
    </div>
</div>

<div class="mb-0">
    <h5 class="fw-semibold mb-3" style="font-size:0.95rem;"><i class="ti ti-list me-1 text-primary"></i> Available Reports</h5>
    <div class="report-grid">
        <div class="report-card-saas" onclick="location.href='{{ route('reports.teachers.list') }}'">
            <div class="d-flex align-items-start gap-2 mb-2">
                <div class="rcs-icon" style="background:rgba(37,99,235,.08);color:#2563eb;"><i class="ti ti-list-details"></i></div>
                <div class="flex-grow-1 min-w-0">
                    <div class="rcs-title">Teacher List</div>
                    <p class="rcs-desc">View comprehensive list of teachers with filtering options.</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="rcs-stat"><i class="ti ti-database"></i>{{ number_format($reportStats['teacher_list'] ?? 0) }} teachers</span>
            </div>
            <div class="rcs-footer">
                <span>View Report</span>
                <span class="rcs-arrow">→</span>
            </div>
        </div>
        <div class="report-card-saas" onclick="location.href='{{ route('reports.teachers.attendance') }}'">
            <div class="d-flex align-items-start gap-2 mb-2">
                <div class="rcs-icon" style="background:rgba(22,163,74,.08);color:#16a34a;"><i class="ti ti-calendar-stats"></i></div>
                <div class="flex-grow-1 min-w-0">
                    <div class="rcs-title">Attendance</div>
                    <p class="rcs-desc">Analyze present/absent counts and monthly attendance summary.</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="rcs-stat"><i class="ti ti-database"></i>{{ number_format($reportStats['teacher_attendance'] ?? 0) }} records</span>
            </div>
            <div class="rcs-footer">
                <span>View Report</span>
                <span class="rcs-arrow">→</span>
            </div>
        </div>
        <div class="report-card-saas" onclick="location.href='{{ route('reports.teachers.subject_allocation') }}'">
            <div class="d-flex align-items-start gap-2 mb-2">
                <div class="rcs-icon" style="background:rgba(14,165,233,.08);color:#0ea5e9;"><i class="ti ti-book-2"></i></div>
                <div class="flex-grow-1 min-w-0">
                    <div class="rcs-title">Subject Allocation</div>
                    <p class="rcs-desc">Analyze teacher-wise subjects and class assignments.</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="rcs-stat"><i class="ti ti-database"></i>{{ number_format($reportStats['subject_allocation'] ?? 0) }} allocations</span>
            </div>
            <div class="rcs-footer">
                <span>View Report</span>
                <span class="rcs-arrow">→</span>
            </div>
        </div>
        <div class="report-card-saas" onclick="location.href='{{ route('reports.teachers.class_teacher_mapping') }}'">
            <div class="d-flex align-items-start gap-2 mb-2">
                <div class="rcs-icon" style="background:rgba(217,119,6,.08);color:#d97706;"><i class="ti ti-building-community"></i></div>
                <div class="flex-grow-1 min-w-0">
                    <div class="rcs-title">Class Teacher Map</div>
                    <p class="rcs-desc">View class, section, and assigned teacher details.</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="rcs-stat"><i class="ti ti-database"></i>{{ number_format($reportStats['class_teacher'] ?? 0) }} mappings</span>
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

    const primary = '#2563eb';
    const success = '#16a34a';
    const danger = '#dc2626';
    const info = '#0ea5e9';
    const warning = '#d97706';

    const chartData = @json($chartData ?? []);

    const subjectCtx = document.getElementById('teacherSubjectChart');
    if (subjectCtx && chartData.subjectLabels && chartData.subjectLabels.length > 0) {
        new Chart(subjectCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: chartData.subjectLabels,
                datasets: [{
                    data: chartData.subjectCounts,
                    backgroundColor: ['#2563eb','#16a34a','#0ea5e9','#d97706','#dc2626','#8b5cf6','#ec4899','#14b8a6'],
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

    const trendCtx = document.getElementById('teacherAttendanceChart');
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
});
</script>
@endpush
