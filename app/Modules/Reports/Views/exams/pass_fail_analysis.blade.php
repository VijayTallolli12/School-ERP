@extends('layouts.admin')

@section("title", "Pass/Fail Analysis Report")
@section("page-title", "Pass/Fail Analysis Report")

@push('styles')
<style>
    .stat-card-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; }
    .subject-high { border-left: 4px solid #198754; }
    .subject-low { border-left: 4px solid #dc3545; }
</style>
@endpush

@section("content")
    <div class="mb-3">
        <a href="{{ route('reports.exams.index') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> Back to Exam Reports
        </a>
    </div>

    {{-- Filter Card --}}
    <div class="card mb-4">
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Academic Year</label>
                    <select name="academic_year_id" id="academic_year_id" class="form-select">
                        <option value="">All</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ ($filters['academic_year_id'] ?? '') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Exam</label>
                    <select name="exam_id" id="exam_id" class="form-select">
                        <option value="">All Exams</option>
                        @foreach($examsWithLabel as $exam)
                            <option value="{{ $exam['id'] }}" {{ ($filters['exam_id'] ?? '') == $exam['id'] ? 'selected' : '' }}>{{ $exam['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Class & Section</label>
                    <select name="class_section_id" id="class_section_id" class="form-select">
                        <option value="">All</option>
                        @foreach($classSections as $cs)
                            <option value="{{ $cs->id }}" {{ ($filters['class_section_id'] ?? '') == $cs->id ? 'selected' : '' }}>{{ $cs->schoolClass->name }} - {{ $cs->section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Subject</label>
                    <select name="subject_id" id="subject_id" class="form-select">
                        <option value="">All</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ ($filters['subject_id'] ?? '') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">From</label>
                    <input type="date" name="from_date" id="from_date" class="form-control" value="{{ $filters['from_date'] ?? '' }}">
                </div>
                <div class="col-md-1">
                    <label class="form-label">To</label>
                    <input type="date" name="to_date" id="to_date" class="form-control" value="{{ $filters['to_date'] ?? '' }}">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <div class="d-flex gap-2 w-100">
                        <button type="button" id="filterBtn" class="btn btn-primary py-2 flex-fill">
                            <i class="ti ti-filter me-1"></i>
                        </button>
                        <button type="button" id="resetBtn" class="btn btn-outline-secondary py-2">
                            <i class="ti ti-refresh"></i>
                        </button>
                    </div>
                </div>
            </form>
            <div class="row mt-3">
                <div class="col-12">
                    <a id="exportExcel" href="#" class="btn btn-success me-2">
                        <i class="ti ti-file-type-xls me-1"></i> Export Excel
                    </a>
                    <a id="exportPdf" href="#" class="btn btn-danger me-2">
                        <i class="ti ti-file-type-pdf me-1"></i> Export PDF
                    </a>
                    <a id="exportPrint" href="#" class="btn btn-warning" target="_blank">
                        <i class="ti ti-printer me-1"></i> Print
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-start border-primary border-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon bg-primary bg-opacity-10">
                        <i class="ti ti-users text-primary fs-24"></i>
                    </div>
                    <div>
                        <p class="text-muted fs-13 mb-0">Students Appeared</p>
                        <h3 class="fw-bold mb-0" id="totalAppeared">{{ $analysis['overall']['total_appeared'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-success border-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon bg-success bg-opacity-10">
                        <i class="ti ti-circle-check text-success fs-24"></i>
                    </div>
                    <div>
                        <p class="text-muted fs-13 mb-0">Passed</p>
                        <h3 class="fw-bold text-success mb-0" id="totalPassed">{{ $analysis['overall']['total_passed'] }} <small class="fs-13 fw-normal text-muted">({{ $analysis['overall']['pass_percentage'] }}%)</small></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-danger border-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon bg-danger bg-opacity-10">
                        <i class="ti ti-circle-x text-danger fs-24"></i>
                    </div>
                    <div>
                        <p class="text-muted fs-13 mb-0">Failed</p>
                        <h3 class="fw-bold text-danger mb-0" id="totalFailed">{{ $analysis['overall']['total_failed'] }} <small class="fs-13 fw-normal text-muted">({{ $analysis['overall']['fail_percentage'] }}%)</small></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-info border-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon bg-info bg-opacity-10">
                        <i class="ti ti-chart-bar text-info fs-24"></i>
                    </div>
                    <div>
                        <p class="text-muted fs-13 mb-0">Avg Class Pass %</p>
                        <h3 class="fw-bold text-info mb-0" id="avgClassPct">{{ $analysis['avgPctOverall'] }}%</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Best/Lowest Class + Subject Highlights --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-success bg-opacity-10 h-100">
                <div class="card-body text-center">
                    <i class="ti ti-trophy text-success fs-28 d-block mb-2"></i>
                    <p class="fs-13 text-muted mb-1">Best Performing Class</p>
                    <h5 class="fw-bold text-success mb-0" id="bestClass">{{ $analysis['bestClass'] ?: '--' }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger bg-opacity-10 h-100">
                <div class="card-body text-center">
                    <i class="ti ti-arrow-down text-danger fs-28 d-block mb-2"></i>
                    <p class="fs-13 text-muted mb-1">Lowest Performing Class</p>
                    <h5 class="fw-bold text-danger mb-0" id="lowestClass">{{ $analysis['lowestClass'] ?: '--' }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary bg-opacity-10 h-100">
                <div class="card-body text-center">
                    <i class="ti ti-trending-up text-primary fs-28 d-block mb-2"></i>
                    <p class="fs-13 text-muted mb-1">Highest Pass % Subject</p>
                    <h5 class="fw-bold text-primary mb-0" id="highestSubject">{{ $analysis['highestSubject'] ?: '--' }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning bg-opacity-10 h-100">
                <div class="card-body text-center">
                    <i class="ti ti-trending-down text-warning fs-28 d-block mb-2"></i>
                    <p class="fs-13 text-muted mb-1">Lowest Pass % Subject</p>
                    <h5 class="fw-bold text-warning mb-0" id="lowestSubject">{{ $analysis['lowestSubject'] ?: '--' }}</h5>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ti ti-chart-bar text-primary me-2"></i>Pass vs Fail</h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 300px;">
                    <canvas id="passFailDoughnut" height="260"></canvas>
                    <div id="noDoughnutData" class="text-center text-muted d-none">
                        <i class="ti ti-cloud-off fs-32 d-block mb-2"></i> No data
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ti ti-school text-primary me-2"></i>Class-wise Pass %</h5>
                </div>
                <div class="card-body" style="min-height: 300px;">
                    <canvas id="classPassChart" height="260"></canvas>
                    <div id="noClassChartData" class="text-center text-muted py-5 d-none">
                        <i class="ti ti-cloud-off fs-32 d-block mb-2"></i> No data
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ti ti-book text-primary me-2"></i>Subject-wise Pass %</h5>
                </div>
                <div class="card-body" style="min-height: 300px;">
                    <canvas id="subjectPassChart" height="260"></canvas>
                    <div id="noSubjectChartData" class="text-center text-muted py-5 d-none">
                        <i class="ti ti-cloud-off fs-32 d-block mb-2"></i> No data
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Class Performance Table --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="ti ti-table text-primary me-2"></i>Class-wise Performance</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="classPerformanceTable">
                    <thead class="table-light">
                        <tr>
                            <th>Class & Section</th>
                            <th class="text-center">Appeared</th>
                            <th class="text-center">Passed</th>
                            <th class="text-center">Failed</th>
                            <th class="text-center">Pass %</th>
                            <th class="text-center">Avg Marks</th>
                            <th class="text-center">Avg %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($analysis['classPerformance'] as $cp)
                            <tr>
                                <td>{{ $cp['class_section'] }}</td>
                                <td class="text-center">{{ $cp['appeared'] }}</td>
                                <td class="text-center text-success fw-medium">{{ $cp['passed'] }}</td>
                                <td class="text-center text-danger fw-medium">{{ $cp['failed'] }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $cp['pass_pct'] >= 80 ? 'success' : ($cp['pass_pct'] >= 50 ? 'warning' : 'danger') }}">
                                        {{ $cp['pass_pct'] }}%
                                    </span>
                                </td>
                                <td class="text-center">{{ $cp['avg_marks'] }}</td>
                                <td class="text-center">{{ $cp['avg_percentage'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Subject-wise Analysis --}}
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><i class="ti ti-chart-line text-primary me-2"></i>Subject-wise Analysis</h5>
            <div class="fs-13 text-muted">
                <span class="badge bg-success me-2">Highest: {{ $analysis['highestSubject'] ?: '--' }}</span>
                <span class="badge bg-danger">Lowest: {{ $analysis['lowestSubject'] ?: '--' }}</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="subjectAnalysisTable">
                    <thead class="table-light">
                        <tr>
                            <th>Subject</th>
                            <th class="text-center">Appeared</th>
                            <th class="text-center">Passed</th>
                            <th class="text-center">Failed</th>
                            <th class="text-center">Pass %</th>
                            <th class="text-center">Fail %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($analysis['subjectAnalysis'] as $sa)
                            <tr>
                                <td>{{ $sa['subject'] }}</td>
                                <td class="text-center">{{ $sa['appeared'] }}</td>
                                <td class="text-center text-success fw-medium">{{ $sa['passed'] }}</td>
                                <td class="text-center text-danger fw-medium">{{ $sa['failed'] }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $sa['pass_pct'] >= 80 ? 'success' : ($sa['pass_pct'] >= 50 ? 'warning' : 'danger') }}">
                                        {{ $sa['pass_pct'] }}%
                                    </span>
                                </td>
                                <td class="text-center">{{ $sa['fail_pct'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Student-level DataTable --}}
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="ti ti-users text-primary me-2"></i>Student-wise Breakdown</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="studentAnalysisTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Admission No</th>
                            <th>Class & Section</th>
                            <th>Exam</th>
                            <th class="text-center">Percentage</th>
                            <th class="text-center">Result</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(async function() {
        const Chart = await window.lazyChart();
        const DataTable = await window.lazyDT();
        let chartData = @json($analysis['chartData']);

        // --- Pass vs Fail Doughnut ---
        let doughnutCtx = document.getElementById('passFailDoughnut');
        let doughnutChart = null;
        if (doughnutCtx && chartData.pass_vs_fail && (chartData.pass_vs_fail.passed > 0 || chartData.pass_vs_fail.failed > 0)) {
            doughnutChart = new Chart(doughnutCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Passed', 'Failed'],
                    datasets: [{
                        data: [chartData.pass_vs_fail.passed, chartData.pass_vs_fail.failed],
                        backgroundColor: ['#198754', '#dc3545'],
                        borderWidth: 2,
                        borderColor: '#fff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16 } }
                    },
                    cutout: '65%',
                }
            });
        } else if (doughnutCtx) {
            doughnutCtx.classList.add('d-none');
            document.getElementById('noDoughnutData').classList.remove('d-none');
        }

        // --- Class-wise Bar ---
        let classCtx = document.getElementById('classPassChart');
        let classChart = null;
        if (classCtx && chartData.class_pass_pct && chartData.class_pass_pct.length > 0) {
            let labels = chartData.class_pass_pct.map(function(c) { return c.label; });
            let values = chartData.class_pass_pct.map(function(c) { return c.value; });
            let colors = values.map(function(v) { return v >= 80 ? '#198754' : (v >= 50 ? '#ffc107' : '#dc3545'); });

            classChart = new Chart(classCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Pass %',
                        data: values,
                        backgroundColor: colors,
                        borderRadius: 4,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(c) { return c.parsed.x + '%'; }
                            }
                        }
                    },
                    scales: {
                        x: { beginAtZero: true, max: 100, ticks: { callback: function(v) { return v + '%'; } } },
                        y: { grid: { display: false } }
                    }
                }
            });
        } else if (classCtx) {
            classCtx.classList.add('d-none');
            document.getElementById('noClassChartData').classList.remove('d-none');
        }

        // --- Subject-wise Horizontal Bar ---
        let subCtx = document.getElementById('subjectPassChart');
        let subChart = null;
        if (subCtx && chartData.subject_pass_pct && chartData.subject_pass_pct.length > 0) {
            let labels = chartData.subject_pass_pct.map(function(s) { return s.label; });
            let values = chartData.subject_pass_pct.map(function(s) { return s.value; });
            let colors = values.map(function(v) { return v >= 80 ? '#0d6efd' : (v >= 50 ? '#6f42c1' : '#dc3545'); });

            subChart = new Chart(subCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Pass %',
                        data: values,
                        backgroundColor: colors,
                        borderRadius: 4,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(c) { return c.parsed.x + '%'; }
                            }
                        }
                    },
                    scales: {
                        x: { beginAtZero: true, max: 100, ticks: { callback: function(v) { return v + '%'; } } },
                        y: { grid: { display: false } }
                    }
                }
            });
        } else if (subCtx) {
            subCtx.classList.add('d-none');
            document.getElementById('noSubjectChartData').classList.remove('d-none');
        }

        // --- Student DataTable ---
        let studentData = @json($analysis['studentAnalysis']);

        let table = $('#studentAnalysisTable').DataTable({
            data: studentData,
            columns: [
                { data: null, orderable: false, searchable: false, render: function(data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; } },
                { data: 'student_name', name: 'student_name' },
                { data: 'admission_no', name: 'admission_no' },
                { data: 'class_section', name: 'class_section' },
                { data: 'exam_name', name: 'exam_name' },
                {
                    data: 'percentage', name: 'percentage', className: 'text-center',
                    render: function(data) {
                        let pct = parseFloat(data);
                        let color = pct >= 80 ? 'success' : (pct >= 60 ? 'warning' : (pct >= 40 ? 'info' : 'danger'));
                        return '<span class="badge bg-' + color + ' fs-13">' + pct + '%</span>';
                    }
                },
                {
                    data: 'result', name: 'result', className: 'text-center',
                    render: function(data) {
                        let cls = data === 'Pass' ? 'success' : 'danger';
                        let icon = data === 'Pass' ? 'ti ti-circle-check' : 'ti ti-circle-x';
                        return '<span class="badge bg-' + cls + ' fs-13"><i class="' + icon + ' me-1"></i> ' + data + '</span>';
                    }
                },
            ],
            order: [[1, 'asc']],
            pageLength: 25
        });

        // --- Filter ---
        function getFilterParams() {
            return {
                academic_year_id: $('#academic_year_id').val(),
                exam_id: $('#exam_id').val(),
                class_section_id: $('#class_section_id').val(),
                subject_id: $('#subject_id').val(),
                from_date: $('#from_date').val(),
                to_date: $('#to_date').val(),
            };
        }

        function updateExportLinks() {
            var qs = $.param(getFilterParams());
            $('#exportExcel').attr('href', "{{ route('reports.exams.pass_fail_analysis.export.excel') }}" + (qs ? '?' + qs : ''));
            $('#exportPdf').attr('href', "{{ route('reports.exams.pass_fail_analysis.export.pdf') }}" + (qs ? '?' + qs : ''));
            $('#exportPrint').attr('href', "{{ route('reports.exams.pass_fail_analysis.print') }}" + (qs ? '?' + qs : ''));
        }

        $('#filterBtn').on('click', function() {
            var qs = $.param(getFilterParams());
            window.location.href = "{{ route('reports.exams.pass_fail_analysis') }}" + (qs ? '?' + qs : '');
        });

        $('#resetBtn').on('click', function() {
            window.location.href = "{{ route('reports.exams.pass_fail_analysis') }}";
        });

        updateExportLinks();
    });
</script>
@endpush
