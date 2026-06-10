@extends('layouts.admin')

@section("title", "Teacher Workload Report")
@section("page-title", "Teacher Workload Report")

@push('styles')
<style>
    .stat-card-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; }
    .chart-container { position: relative; min-height: 320px; }
</style>
@endpush

@section("content")
    <div class="mb-3">
        <a href="{{ route('reports.teachers.index') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> Back to Teacher Reports
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form id="filterForm" class="row g-3" method="GET">
                <div class="col-md-3">
                    <label class="form-label">Academic Year</label>
                    <select name="academic_year_id" id="academic_year_id" class="form-select">
                        <option value="">All</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Teacher</label>
                    <select name="teacher_id" class="form-select">
                        <option value="">All</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}" {{ request('teacher_id') == $t->id ? 'selected' : '' }}>{{ $t->full_name }} ({{ $t->employee_id }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Subject</label>
                    <select name="subject_id" class="form-select">
                        <option value="">All</option>
                        @foreach($subjects as $subj)
                            <option value="{{ $subj->id }}" {{ request('subject_id') == $subj->id ? 'selected' : '' }}>{{ $subj->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Class & Section</label>
                    <select name="class_section_id" class="form-select">
                        <option value="">All</option>
                        @foreach($classSections as $cs)
                            <option value="{{ $cs->id }}" {{ request('class_section_id') == $cs->id ? 'selected' : '' }}>{{ $cs->schoolClass->name }} - {{ $cs->section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary py-2"><i class="ti ti-filter me-1"></i> Filter</button>
                    <a href="{{ route('reports.teachers.workload') }}" class="btn btn-outline-secondary py-2"><i class="ti ti-refresh me-1"></i> Reset</a>
                </div>
            </form>
            <div class="row mt-3">
                <div class="col-12">
                    <a id="exportExcel" href="{{ route('reports.teachers.workload.export', ['type' => 'excel']) }}?{{ http_build_query(request()->all()) }}" class="btn btn-success me-2"><i class="ti ti-file-type-xls me-1"></i> Export Excel</a>
                    <a id="exportPdf" href="{{ route('reports.teachers.workload.export', ['type' => 'pdf']) }}?{{ http_build_query(request()->all()) }}" class="btn btn-danger me-2"><i class="ti ti-file-type-pdf me-1"></i> Export PDF</a>
                    <a id="exportPrint" href="{{ route('reports.teachers.workload.export', ['type' => 'print']) }}?{{ http_build_query(request()->all()) }}" class="btn btn-warning" target="_blank"><i class="ti ti-printer me-1"></i> Print</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-start border-primary border-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon bg-primary bg-opacity-10"><i class="ti ti-users text-primary fs-24"></i></div>
                    <div>
                        <p class="text-muted fs-13 mb-0">Total Teachers</p>
                        <h3 class="fw-bold mb-0" id="totalTeachers">{{ $summary['total_teachers'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-info border-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon bg-info bg-opacity-10"><i class="ti ti-chart-bar text-info fs-24"></i></div>
                    <div>
                        <p class="text-muted fs-13 mb-0">Avg Workload Score</p>
                        <h3 class="fw-bold text-info mb-0" id="avgWorkload">{{ $summary['avg_workload'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-warning border-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon bg-warning bg-opacity-10"><i class="ti ti-school text-warning fs-24"></i></div>
                    <div>
                        <p class="text-muted fs-13 mb-0">Avg Classes/Teacher</p>
                        <h3 class="fw-bold text-warning mb-0" id="avgClasses">{{ $summary['avg_classes'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-success border-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon bg-success bg-opacity-10"><i class="ti ti-book text-success fs-24"></i></div>
                    <div>
                        <p class="text-muted fs-13 mb-0">Avg Subjects/Teacher</p>
                        <h3 class="fw-bold text-success mb-0" id="avgSubjects">{{ $summary['avg_subjects'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-7">
            <div class="card h-100">
                <div class="card-header"><h5 class="card-title mb-0">Workload Distribution</h5></div>
                <div class="card-body chart-container">
                    <canvas id="workloadChart"></canvas>
                    <div id="noWorkloadChart" class="text-center text-muted py-5 d-none"><i class="ti ti-cloud-off fs-32 d-block mb-2"></i> No data</div>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header"><h5 class="card-title mb-0">Subject Allocation</h5></div>
                <div class="card-body chart-container d-flex align-items-center justify-content-center">
                    <canvas id="subjectChart"></canvas>
                    <div id="noSubjectChart" class="text-center text-muted d-none"><i class="ti ti-cloud-off fs-32 d-block mb-2"></i> No data</div>
                </div>
            </div>
        </div>
    </div>

    {{-- DataTable --}}
    <div class="card">
        <div class="card-header"><h5 class="card-title mb-0">Teacher Workload Breakdown</h5></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="workloadTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Teacher Name</th>
                            <th>Employee ID</th>
                            <th>Subjects</th>
                            <th>Classes</th>
                            <th>Weekly Periods</th>
                            <th>Workload Score</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var summary = @json($summary ?? []);
        var chartData = @json($chartData ?? []);

        // Charts
        if (chartData.workload_distribution && chartData.workload_distribution.length > 0) {
            var barCtx = document.getElementById('workloadChart');
            if (barCtx) {
                new Chart(barCtx, {
                    type: 'bar',
                    data: {
                        labels: chartData.workload_distribution.map(function(r) { return r.label; }),
                        datasets: [{
                            label: 'Workload Score',
                            data: chartData.workload_distribution.map(function(r) { return r.value; }),
                            backgroundColor: '#0d6efd',
                            borderRadius: 4,
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        indexAxis: 'y',
                        scales: {
                            x: { beginAtZero: true, ticks: { stepSize: 1 } },
                            y: { grid: { display: false } }
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(c) { return 'Score: ' + c.parsed.x; }
                                }
                            }
                        }
                    }
                });
            }
        } else {
            document.getElementById('workloadChart').classList.add('d-none');
            document.getElementById('noWorkloadChart').classList.remove('d-none');
        }

        if (chartData.subject_allocation && chartData.subject_allocation.length > 0) {
            var doughnutCtx = document.getElementById('subjectChart');
            if (doughnutCtx) {
                new Chart(doughnutCtx, {
                    type: 'doughnut',
                    data: {
                        labels: chartData.subject_allocation.map(function(r) { return r.label; }),
                        datasets: [{
                            data: chartData.subject_allocation.map(function(r) { return r.value; }),
                            backgroundColor: ['#0d6efd', '#6f42c1', '#d63384', '#fd7e14', '#198754', '#0dcaf0', '#ffc107', '#dc3545'],
                            borderWidth: 2,
                            borderColor: '#fff',
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { usePointStyle: true, padding: 12 } },
                            tooltip: {
                                callbacks: {
                                    label: function(c) {
                                        var total = c.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                                        var pct = total > 0 ? ((c.parsed / total) * 100).toFixed(1) : 0;
                                        return c.label + ': ' + c.parsed + ' teacher(s) (' + pct + '%)';
                                    }
                                }
                            }
                        },
                        cutout: '60%',
                    }
                });
            }
        } else {
            document.getElementById('subjectChart').classList.add('d-none');
            document.getElementById('noSubjectChart').classList.remove('d-none');
        }

        // DataTable
        $('#workloadTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: window.location.href,
                data: function(d) {
                    d.academic_year_id = $('#academic_year_id').val();
                    d.teacher_id = $('select[name="teacher_id"]').val();
                    d.subject_id = $('select[name="subject_id"]').val();
                    d.class_section_id = $('select[name="class_section_id"]').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', searchable: false, orderable: false },
                { data: 'teacher_name', name: 'teacher_name' },
                { data: 'employee_id', name: 'employee_id' },
                { data: 'assigned_subjects', name: 'assigned_subjects', className: 'text-center' },
                { data: 'assigned_classes', name: 'assigned_classes', className: 'text-center' },
                { data: 'weekly_periods', name: 'weekly_periods', className: 'text-center' },
                { data: 'workload_score', name: 'workload_score', className: 'text-center fw-bold' },
            ],
            order: [[6, 'desc']],
            pageLength: 25,
            language: { search: "Search:", searchPlaceholder: "Type to search..." },
            drawCallback: function() {
                var api = this.api();
                var totalPages = api.page.info().pages;
                if (totalPages > 1 && $('.dataTables_scrollHead').length === 0) {
                    // DataTable responsive wrapper already handles this
                }
            }
        });
    });
</script>
@endpush