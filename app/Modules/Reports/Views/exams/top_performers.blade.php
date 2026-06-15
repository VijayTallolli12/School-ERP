@extends('layouts.admin')

@section("title", "Top Performers Report")
@section("page-title", "Top Performers Report")

@push('styles')
<style>
    .rank-badge {
        width: 32px; height: 32px; display: inline-flex; align-items: center;
        justify-content: center; border-radius: 50%; font-weight: 700; font-size: 13px;
    }
    .rank-1 { background: #ffd700; color: #000; }
    .rank-2 { background: #c0c0c0; color: #000; }
    .rank-3 { background: #cd7f32; color: #fff; }
    .rank-default { background: #e9ecef; color: #495057; }
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
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Exam</label>
                    <select name="exam_id" id="exam_id" class="form-select">
                        <option value="">All Exams</option>
                        @foreach($examsWithLabel as $exam)
                            <option value="{{ $exam['id'] }}">{{ $exam['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Class & Section</label>
                    <select name="class_section_id" id="class_section_id" class="form-select">
                        <option value="">All</option>
                        @foreach($classSections as $cs)
                            <option value="{{ $cs->id }}">{{ $cs->schoolClass->name }} - {{ $cs->section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Subject</label>
                    <select name="subject_id" id="subject_id" class="form-select">
                        <option value="">All</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Top N</label>
                    <select name="top_n" id="top_n" class="form-select">
                        @foreach([5, 10, 20, 30, 50] as $n)
                            <option value="{{ $n }}" {{ $n == 10 ? 'selected' : '' }}>{{ $n }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="d-flex gap-2 w-100">
                        <button type="button" id="filterBtn" class="btn btn-primary py-2 flex-fill">
                            <i class="ti ti-filter me-1"></i> Filter
                        </button>
                        <button type="button" id="resetBtn" class="btn btn-outline-secondary py-2">
                            <i class="ti ti-refresh"></i>
                        </button>
                    </div>
                </div>
            </form>
            <div class="row mt-3">
                <div class="col-12">
                    <a id="exportExcel" href="{{ route('reports.exams.top_performers.export.excel') }}" class="btn btn-success me-2">
                        <i class="ti ti-file-type-xls me-1"></i> Export Excel
                    </a>
                    <a id="exportPdf" href="{{ route('reports.exams.top_performers.export.pdf') }}" class="btn btn-danger me-2">
                        <i class="ti ti-file-type-pdf me-1"></i> Export PDF
                    </a>
                    <a id="exportPrint" href="{{ route('reports.exams.top_performers.print') }}" class="btn btn-warning" target="_blank">
                        <i class="ti ti-printer me-1"></i> Print
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4" id="summaryCards">
        <div class="col-md-3">
            <div class="card border-start border-warning border-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted fs-13 mb-1 text-uppercase fw-medium">Highest %</p>
                            <h3 class="fw-bold mb-0 text-warning" id="highestPercentage">--</h3>
                        </div>
                        <div class="fs-32 text-warning opacity-50">
                            <i class="ti ti-trophy"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-success border-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted fs-13 mb-1 text-uppercase fw-medium">Top Student</p>
                            <h5 class="fw-bold mb-0 text-success" id="topStudent">--</h5>
                        </div>
                        <div class="fs-32 text-success opacity-50">
                            <i class="ti ti-crown"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-info border-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted fs-13 mb-1 text-uppercase fw-medium">Class Average</p>
                            <h3 class="fw-bold mb-0 text-info" id="classAverage">--</h3>
                        </div>
                        <div class="fs-32 text-info opacity-50">
                            <i class="ti ti-chart-histogram"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-primary border-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted fs-13 mb-1 text-uppercase fw-medium">Students Evaluated</p>
                            <h3 class="fw-bold mb-0 text-primary" id="studentsEvaluated">--</h3>
                        </div>
                        <div class="fs-32 text-primary opacity-50">
                            <i class="ti ti-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ti ti-chart-bar text-primary me-2"></i>Top Performers <span class="text-muted fs-13 fw-normal">(Percentage)</span></h5>
                </div>
                <div class="card-body">
                    <canvas id="topPerformersChart" height="280"></canvas>
                    <div id="noChartData" class="text-center text-muted py-5 d-none">
                        <i class="ti ti-cloud-off fs-32 d-block mb-2"></i> No data available
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ti ti-chart-donut text-primary me-2"></i>Grade Distribution</h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 330px;">
                    <canvas id="gradeDistributionChart" height="280"></canvas>
                    <div id="noGradeData" class="text-center text-muted d-none">
                        <i class="ti ti-cloud-off fs-32 d-block mb-2"></i> No data
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- DataTable --}}
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="ti ti-list text-primary me-2"></i>Ranked Students</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="topPerformersTable">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Student Name</th>
                            <th>Admission No</th>
                            <th>Class & Section</th>
                            <th>Exam Name</th>
                            <th class="text-center">Total Marks</th>
                            <th class="text-center">Obtained</th>
                            <th class="text-center">Percentage</th>
                            <th class="text-center">Grade</th>
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
        const gradeColors = {
            'A+': '#198754', 'A': '#0d6efd', 'B+': '#0dcaf0',
            'B': '#6f42c1', 'C': '#ffc107', 'D': '#fd7e14', 'F': '#dc3545'
        };
        const gradeOrder = ['A+', 'A', 'B+', 'B', 'C', 'D', 'F'];

        let table = $('#topPerformersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('reports.exams.top_performers') }}",
                data: function(d) {
                    d.academic_year_id = $('#academic_year_id').val();
                    d.exam_id = $('#exam_id').val();
                    d.class_section_id = $('#class_section_id').val();
                    d.subject_id = $('#subject_id').val();
                    d.top_n = $('#top_n').val();
                }
            },
            columns: [
                {
                    data: 'rank', name: 'rank',
                    render: function(data) {
                        let cls = 'rank-default';
                        if (data === 1) cls = 'rank-1';
                        else if (data === 2) cls = 'rank-2';
                        else if (data === 3) cls = 'rank-3';
                        return '<span class="rank-badge ' + cls + '">' + data + '</span>';
                    }
                },
                {data: 'student_name', name: 'student_name'},
                {data: 'admission_no', name: 'admission_no'},
                {data: 'class_section', name: 'class_section'},
                {data: 'exam_name', name: 'exam_name'},
                {data: 'total_marks', name: 'total_marks', className: 'text-center'},
                {data: 'obtained_marks', name: 'obtained_marks', className: 'text-center'},
                {
                    data: 'percentage', name: 'percentage', className: 'text-center',
                    render: function(data) {
                        let pct = parseFloat(data);
                        let color = pct >= 80 ? 'success' : (pct >= 60 ? 'warning' : (pct >= 40 ? 'info' : 'danger'));
                        return '<span class="badge bg-' + color + ' fs-13">' + pct + '%</span>';
                    }
                },
                {
                    data: 'grade', name: 'grade', className: 'text-center',
                    render: function(data) {
                        let bg = gradeColors[data] || '#6c757d';
                        return '<span class="badge fw-semibold" style="background:' + bg + ';color:#fff">' + data + '</span>';
                    }
                },
            ],
            order: [[0, 'asc']],
            columnDefs: [
                {targets: 0, orderable: false, searchable: false}
            ],
            pageLength: 25,
            drawCallback: function() {
                updateSummaryAndCharts();
            }
        });

        function updateExportLinks() {
            var params = {
                academic_year_id: $('#academic_year_id').val(),
                exam_id: $('#exam_id').val(),
                class_section_id: $('#class_section_id').val(),
                subject_id: $('#subject_id').val(),
                top_n: $('#top_n').val()
            };
            var qs = $.param(params);
            $('#exportExcel').attr('href', "{{ route('reports.exams.top_performers.export.excel') }}" + (qs ? '?' + qs : ''));
            $('#exportPdf').attr('href', "{{ route('reports.exams.top_performers.export.pdf') }}" + (qs ? '?' + qs : ''));
            $('#exportPrint').attr('href', "{{ route('reports.exams.top_performers.print') }}" + (qs ? '?' + qs : ''));
        }

        function updateSummaryAndCharts() {
            var data = table.rows({filter: 'applied'}).data();
            if (!data || data.length === 0) {
                $('#summaryCards .fw-bold').text('--');
                $('#topStudent').text('--');
                showEmptyCharts();
                return;
            }

            var pcts = data.map(function(r) { return parseFloat(r.percentage); });
            var maxPct = Math.max.apply(null, pcts);
            var avgPct = pcts.reduce(function(a, b) { return a + b; }, 0) / pcts.length;

            $('#highestPercentage').text(maxPct.toFixed(1) + '%');
            $('#topStudent').text(data[0].student_name);
            $('#classAverage').text(avgPct.toFixed(1) + '%');
            $('#studentsEvaluated').text(data.length);

            // Grade distribution
            var grades = {};
            data.each(function(r) {
                grades[r.grade] = (grades[r.grade] || 0) + 1;
            });

            // Charts
            renderBarChart($('#top_n').val(), data);
            renderGradeChart(grades);
        }

        var topChart = null;
        var gradeChart = null;

        function renderBarChart(topN, data) {
            var ctx = document.getElementById('topPerformersChart');
            if (!ctx) return;
            if (topChart) topChart.destroy();

            var labels = [], values = [];
            data.each(function(r) {
                labels.push(r.student_name);
                values.push(parseFloat(r.percentage));
            });

            if (labels.length === 0) {
                document.getElementById('topPerformersChart').classList.add('d-none');
                document.getElementById('noChartData').classList.remove('d-none');
                return;
            }
            document.getElementById('topPerformersChart').classList.remove('d-none');
            document.getElementById('noChartData').classList.add('d-none');

            var bgColors = values.map(function(v) {
                if (v >= 80) return '#198754';
                if (v >= 60) return '#ffc107';
                if (v >= 40) return '#0dcaf0';
                return '#dc3545';
            });

            topChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Percentage',
                        data: values,
                        backgroundColor: bgColors,
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
        }

        function renderGradeChart(grades) {
            var ctx = document.getElementById('gradeDistributionChart');
            if (!ctx) return;
            if (gradeChart) gradeChart.destroy();

            var labels = [], values = [], colors = [];
            gradeOrder.forEach(function(g) {
                if (grades[g]) {
                    labels.push(g);
                    values.push(grades[g]);
                    colors.push(gradeColors[g]);
                }
            });

            if (labels.length === 0) {
                document.getElementById('gradeDistributionChart').classList.add('d-none');
                document.getElementById('noGradeData').classList.remove('d-none');
                return;
            }
            document.getElementById('gradeDistributionChart').classList.remove('d-none');
            document.getElementById('noGradeData').classList.add('d-none');

            gradeChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: colors,
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
        }

        function showEmptyCharts() {
            if (topChart) topChart.destroy();
            if (gradeChart) gradeChart.destroy();
            document.getElementById('topPerformersChart').classList.add('d-none');
            document.getElementById('noChartData').classList.remove('d-none');
            document.getElementById('gradeDistributionChart').classList.add('d-none');
            document.getElementById('noGradeData').classList.remove('d-none');
        }

        $('#filterBtn').on('click', function() {
            table.ajax.reload();
            updateExportLinks();
        });

        $('#resetBtn').on('click', function() {
            $('#filterForm')[0].reset();
            $('#top_n').val(10);
            table.ajax.reload();
            updateExportLinks();
        });

        $('select[name="top_n"]').on('change', function() {
            table.ajax.reload();
            updateExportLinks();
        });

        updateExportLinks();
    });
</script>
@endpush
