@extends('layouts.admin')

@section("title", "Exam Reports Dashboard")
@section("page-title", "Exam Reports Dashboard")

@section("content")
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-3" style="width:48px;height:48px;background:rgba(37,99,235,.1);color:#2563eb;">
                        <i class="ti ti-file-text fs-4"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <p class="text-muted fs-6 fw-medium mb-0">Total Exams</p>
                        <h3 class="fw-bold mb-0">{{ $stats['total_exams'] ?? 0 }}</h3>
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
                        <i class="ti ti-checkbox fs-4"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <p class="text-muted fs-6 fw-medium mb-0">Published Results</p>
                        <h3 class="fw-bold mb-0">{{ $stats['published_results'] ?? 0 }}</h3>
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
                        <p class="text-muted fs-6 fw-medium mb-0">Pass Percentage</p>
                        <h3 class="fw-bold mb-0">{{ $stats['pass_percentage'] ?? 0 }}%</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-3" style="width:48px;height:48px;background:rgba(139,92,246,.1);color:#8b5cf6;">
                        <i class="ti ti-crown fs-4"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <p class="text-muted fs-6 fw-medium mb-0">Toppers</p>
                        <h3 class="fw-bold mb-0">{{ $stats['toppers_count'] ?? 0 }}</h3>
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
                <h6 class="fw-semibold mb-0"><i class="ti ti-chart-bar me-1 text-primary"></i> Pass Percentage by Exam</h6>
            </div>
            <div class="card-body">
                <div style="position:relative;height:220px">
                    <canvas id="examPassChart"></canvas>
                </div>
                <p class="text-muted small text-center mt-2 mb-0">Latest exam pass rates</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-transparent border-bottom-0 pb-0">
                <h6 class="fw-semibold mb-0"><i class="ti ti-toggle-left me-1 text-primary"></i> Result Publication Status</h6>
            </div>
            <div class="card-body">
                <div style="position:relative;height:220px">
                    <canvas id="publicationChart"></canvas>
                </div>
                <p class="text-muted small text-center mt-2 mb-0">Published vs unpublished exam results</p>
            </div>
        </div>
    </div>
</div>

<div class="mb-0">
    <h5 class="fw-semibold mb-3" style="font-size:0.95rem;"><i class="ti ti-list me-1 text-primary"></i> Available Reports</h5>
    <div class="report-grid">
        <div class="report-card-saas" onclick="location.href='{{ route('reports.exams.results') }}'">
            <div class="d-flex align-items-start gap-2 mb-2">
                <div class="rcs-icon" style="background:rgba(37,99,235,.08);color:#2563eb;"><i class="ti ti-list-details"></i></div>
                <div class="flex-grow-1 min-w-0">
                    <div class="rcs-title">Exam Results</div>
                    <p class="rcs-desc">Comprehensive exam results with filtering.</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="rcs-stat"><i class="ti ti-database"></i>{{ number_format($reportStats['exam_results'] ?? 0) }} results</span>
            </div>
            <div class="rcs-footer">
                <span>View Report</span>
                <span class="rcs-arrow">→</span>
            </div>
        </div>
        <div class="report-card-saas" onclick="location.href='{{ route('reports.exams.class_performance') }}'">
            <div class="d-flex align-items-start gap-2 mb-2">
                <div class="rcs-icon" style="background:rgba(22,163,74,.08);color:#16a34a;"><i class="ti ti-chart-bar"></i></div>
                <div class="flex-grow-1 min-w-0">
                    <div class="rcs-title">Class Performance</div>
                    <p class="rcs-desc">Performance averages and pass/fail ratios.</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="rcs-stat"><i class="ti ti-database"></i>{{ number_format($reportStats['class_performance'] ?? 0) }} classes</span>
            </div>
            <div class="rcs-footer">
                <span>View Report</span>
                <span class="rcs-arrow">→</span>
            </div>
        </div>
        <div class="report-card-saas" onclick="location.href='{{ route('reports.exams.subject_performance') }}'">
            <div class="d-flex align-items-start gap-2 mb-2">
                <div class="rcs-icon" style="background:rgba(14,165,233,.08);color:#0ea5e9;"><i class="ti ti-book-2"></i></div>
                <div class="flex-grow-1 min-w-0">
                    <div class="rcs-title">Subject Performance</div>
                    <p class="rcs-desc">Highest, lowest and average marks per subject.</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="rcs-stat"><i class="ti ti-database"></i>{{ number_format($reportStats['subject_performance'] ?? 0) }} subjects</span>
            </div>
            <div class="rcs-footer">
                <span>View Report</span>
                <span class="rcs-arrow">→</span>
            </div>
        </div>
        <div class="report-card-saas" onclick="location.href='{{ route('reports.exams.student_summary') }}'">
            <div class="d-flex align-items-start gap-2 mb-2">
                <div class="rcs-icon" style="background:rgba(217,119,6,.08);color:#d97706;"><i class="ti ti-user-star"></i></div>
                <div class="flex-grow-1 min-w-0">
                    <div class="rcs-title">Student Summary</div>
                    <p class="rcs-desc">Overall report card summary per student.</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="rcs-stat"><i class="ti ti-database"></i>{{ number_format($reportStats['student_summary'] ?? 0) }} summaries</span>
            </div>
            <div class="rcs-footer">
                <span>View Report</span>
                <span class="rcs-arrow">→</span>
            </div>
        </div>
        <div class="report-card-saas" onclick="location.href='{{ route('reports.exams.top_performers') }}'">
            <div class="d-flex align-items-start gap-2 mb-2">
                <div class="rcs-icon" style="background:rgba(139,92,246,.08);color:#8b5cf6;"><i class="ti ti-crown"></i></div>
                <div class="flex-grow-1 min-w-0">
                    <div class="rcs-title">Top Performers</div>
                    <p class="rcs-desc">Ranked list of top performing students.</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="rcs-stat"><i class="ti ti-database"></i>{{ number_format($reportStats['top_performers'] ?? 0) }} toppers</span>
            </div>
            <div class="rcs-footer">
                <span>View Report</span>
                <span class="rcs-arrow">→</span>
            </div>
        </div>
        <div class="report-card-saas" onclick="location.href='{{ route('reports.exams.pass_fail_analysis') }}'">
            <div class="d-flex align-items-start gap-2 mb-2">
                <div class="rcs-icon" style="background:rgba(220,38,38,.08);color:#dc2626;"><i class="ti ti-alert-triangle"></i></div>
                <div class="flex-grow-1 min-w-0">
                    <div class="rcs-title">Pass/Fail Analysis</div>
                    <p class="rcs-desc">Pass/fail rates across classes and subjects.</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="rcs-stat"><i class="ti ti-database"></i>{{ number_format($reportStats['pass_fail'] ?? 0) }} records</span>
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

    const passCtx = document.getElementById('examPassChart');
    if (passCtx && chartData.passLabels && chartData.passLabels.length > 0) {
        new Chart(passCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: chartData.passLabels,
                datasets: [{
                    label: 'Pass %',
                    data: chartData.passValues,
                    backgroundColor: chartData.passValues.map(function (v) {
                        return v >= 70 ? 'rgba(22,163,74,.7)' : v >= 40 ? 'rgba(217,119,6,.7)' : 'rgba(220,38,38,.7)';
                    }),
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    x: { beginAtZero: true, max: 100, grid: { color: 'rgba(0,0,0,.06)' } },
                    y: { grid: { display: false } }
                }
            }
        });
    }

    const pubCtx = document.getElementById('publicationChart');
    if (pubCtx && chartData.publishedCount !== undefined && chartData.unpublishedCount !== undefined) {
        const total = chartData.publishedCount + chartData.unpublishedCount;
        if (total > 0) {
            new Chart(pubCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Published', 'Unpublished'],
                    datasets: [{
                        data: [chartData.publishedCount, chartData.unpublishedCount],
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
    }
});
</script>
@endpush
