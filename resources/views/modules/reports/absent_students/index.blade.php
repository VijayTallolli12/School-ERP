@extends('modules.reports.reports_layout')

@section('title', 'Absent Students Report')
@section('report_title', 'Absent Students Report')

@section('content')
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="text-muted">Identify absent students quickly</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('reports.attendance.index') }}" class="btn btn-outline-secondary me-2">
                    <i class="ti ti-arrow-left me-1"></i> Back to Attendance Reports
                </a>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="card mb-4">
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $filters['from_date'] }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $filters['to_date'] }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Academic Year</label>
                    <select name="academic_year_id" class="form-select">
                        <option value="">All</option>
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->id }}" {{ ($filters['academic_year_id'] ?? '') == $year->id ? 'selected' : '' }}>
                                {{ $year->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Class & Section</label>
                    <select name="class_section_id" id="class_section_id" class="form-select">
                        <option value="">All</option>
                        @foreach ($classSections as $cs)
                            <option value="{{ $cs->id }}" {{ ($filters['class_section_id'] ?? '') == $cs->id ? 'selected' : '' }}>
                                {{ $cs->schoolClass->name }} - {{ $cs->section->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Student</label>
                    <select name="student_id" id="student_id" class="form-select">
                        <option value="">All</option>
                        @foreach ($students as $s)
                            <option value="{{ $s['id'] }}" {{ ($filters['student_id'] ?? '') == $s['id'] ? 'selected' : '' }}>
                                {{ $s['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="d-flex gap-2 w-100">
                        <button type="submit" class="btn btn-primary py-2 flex-fill">
                            <i class="ti ti-filter me-1"></i> Filter
                        </button>
                        <button type="button" id="resetBtn" class="btn btn-outline-secondary py-2">
                            <i class="ti ti-refresh me-1"></i>
                        </button>
                    </div>
                </div>
            </form>
            <div class="row mt-3">
                <div class="col-12">
                    <a id="exportExcel" href="{{ route('reports.attendance.absent_students.export.excel', request()->query()) }}" class="btn btn-success me-2">
                        <i class="ti ti-file-type-xls me-1"></i> Export Excel
                    </a>
                    <a id="exportPdf" href="{{ route('reports.attendance.absent_students.export.pdf', request()->query()) }}" class="btn btn-danger me-2">
                        <i class="ti ti-file-type-pdf me-1"></i> Export PDF
                    </a>
                    <a id="exportPrint" href="{{ route('reports.attendance.absent_students.print', request()->query()) }}" class="btn btn-warning" target="_blank">
                        <i class="ti ti-printer me-1"></i> Print
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1">Total Records</p>
                            <h4 class="fw-semibold mb-0">{{ $summary['total'] }}</h4>
                        </div>
                        <div class="fs-32 text-info">
                            <i class="ti ti-clipboard"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1">Present</p>
                            <h4 class="fw-semibold mb-0">{{ $summary['present'] }}</h4>
                        </div>
                        <div class="fs-32 text-success">
                            <i class="ti ti-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1">Absent</p>
                            <h4 class="fw-semibold mb-0">{{ $summary['absent'] }}</h4>
                        </div>
                        <div class="fs-32 text-danger">
                            <i class="ti ti-close"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1">Attendance %</p>
                            <h4 class="fw-semibold mb-0">{{ $summary['percentage'] }}%</h4>
                        </div>
                        <div class="fs-32 text-primary">
                            <i class="ti ti-percentage"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="fw-semibold card-title mb-0"><i class="ti ti-chart-bar text-primary me-2"></i>Class-wise Absence Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="classWiseChart" height="180"></canvas>
                    <div id="noClassWiseChart" class="text-center text-muted py-5 d-none">
                        <i class="ti ti-cloud-off fs-32 d-block mb-2"></i> No data available
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="fw-semibold card-title mb-0"><i class="ti ti-chart-line text-primary me-2"></i>Attendance Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" height="180"></canvas>
                    <div id="noTrendChart" class="text-center text-muted py-5 d-none">
                        <i class="ti ti-cloud-off fs-32 d-block mb-2"></i> No data available
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- DataTable --}}
    <div class="card">
        <div class="card-header">
            <h5 class="fw-semibold card-title mb-0"><i class="ti ti-users text-primary me-2"></i>Absent Students List</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="absentStudentsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Admission No</th>
                            <th>Class & Section</th>
                            <th>Parent Name</th>
                            <th>Parent Mobile</th>
                            <th>Attendance Date</th>
                            <th>Status</th>
                            <th>Consecutive Absent Days</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .chart-container { position: relative; min-height: 200px; }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', async function() {
        const Chart = await window.lazyChart();
        const DataTable = await window.lazyDT();
        let table = $('#absentStudentsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('reports.attendance.absent_students.data') }}",
                data: function(d) {
                    d.from_date = $('input[name="from_date"]').val();
                    d.to_date = $('input[name="to_date"]').val();
                    d.academic_year_id = $('select[name="academic_year_id"]').val();
                    d.class_section_id = $('select[name="class_section_id"]').val();
                    d.student_id = $('select[name="student_id"]').val();
                }
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', searchable: false, orderable: false},
                {data: 'student_name', name: 'student_name'},
                {data: 'admission_no', name: 'admission_no'},
                {data: 'class_section', name: 'class_section'},
                {data: 'parent_name', name: 'parent_name'},
                {data: 'parent_mobile', name: 'parent_mobile'},
                {data: 'attendance_date', name: 'attendance_date'},
                {data: 'status', name: 'status'},
                {data: 'consecutive_badge', name: 'consecutive_days', orderable: false, searchable: false},
            ],
            order: [[6, 'desc']],
            columnDefs: [
                {targets: 0, orderable: false, searchable: false},
                {targets: 7, orderable: false},
            ],
            pageLength: 25
        });

        function updateExportLinks() {
            var params = {
                from_date: $('input[name="from_date"]').val(),
                to_date: $('input[name="to_date"]').val(),
                academic_year_id: $('select[name="academic_year_id"]').val(),
                class_section_id: $('select[name="class_section_id"]').val(),
                student_id: $('select[name="student_id"]').val()
            };
            var queryString = $.param(params);

            var baseExcel = "{{ route('reports.attendance.absent_students.export.excel') }}";
            var basePdf = "{{ route('reports.attendance.absent_students.export.pdf') }}";
            var basePrint = "{{ route('reports.attendance.absent_students.print') }}";

            $('#exportExcel').attr('href', baseExcel + (queryString ? '?' + queryString : ''));
            $('#exportPdf').attr('href', basePdf + (queryString ? '?' + queryString : ''));
            $('#exportPrint').attr('href', basePrint + (queryString ? '?' + queryString : ''));
        }

        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            table.draw();
            updateExportLinks();
            updateCharts();
        });

        $('#resetBtn').on('click', function() {
            $('input[name="from_date"]').val('{{ \Carbon\Carbon::today()->toDateString() }}');
            $('input[name="to_date"]').val('{{ \Carbon\Carbon::today()->toDateString() }}');
            $('select[name="academic_year_id"]').val('');
            $('select[name="class_section_id"]').val('');
            $('select[name="student_id"]').val('');
            table.draw();
            updateExportLinks();
            updateCharts();
        });

        // Class section change -> reload students
        $('#class_section_id').on('change', function() {
            var classSectionId = $(this).val();
            $.get("{{ route('reports.attendance.absent_students.students.by_class') }}", { class_section_id: classSectionId }, function(data) {
                var $select = $('select[name="student_id"]');
                $select.find('option:not(:first)').remove();
                $.each(data, function(i, s) {
                    $select.append($('<option>', { value: s.id, text: s.name }));
                });
            });
        });

        updateExportLinks();

        // ─── Charts ────────────────────────────────────────────────

        var classWiseCtx = document.getElementById('classWiseChart');
        var trendCtx = document.getElementById('trendChart');
        var classWiseChart = null;
        var trendChart = null;

        function renderCharts(classData, trendData) {
            if (classWiseChart) classWiseChart.destroy();
            if (trendChart) trendChart.destroy();

            if (classWiseCtx && classData && classData.length) {
                classWiseCtx.classList.remove('d-none');
                document.getElementById('noClassWiseChart').classList.add('d-none');
                classWiseChart = new Chart(classWiseCtx, {
                    type: 'bar',
                    data: {
                        labels: classData.map(function(d) { return d.label; }),
                        datasets: [{
                            label: 'Absent Students',
                            data: classData.map(function(d) { return d.count; }),
                            backgroundColor: [
                                'rgba(220,53,69,0.7)',
                                'rgba(255,193,7,0.7)',
                                'rgba(13,110,253,0.7)',
                                'rgba(25,135,84,0.7)',
                                'rgba(111,66,193,0.7)',
                                'rgba(13,202,240,0.7)',
                                'rgba(253,126,20,0.7)',
                                'rgba(108,117,125,0.7)',
                            ],
                            borderColor: [
                                '#dc3545', '#ffc107', '#0d6efd', '#198754',
                                '#6f42c1', '#0dcaf0', '#fd7e14', '#6c757d'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { stepSize: 1 } }
                        }
                    }
                });
            } else if (classWiseCtx) {
                classWiseCtx.classList.add('d-none');
                document.getElementById('noClassWiseChart').classList.remove('d-none');
            }

            if (trendCtx && trendData && trendData.length) {
                trendCtx.classList.remove('d-none');
                document.getElementById('noTrendChart').classList.add('d-none');
                trendChart = new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: trendData.map(function(d) { return d.date; }),
                        datasets: [{
                            label: 'Absences',
                            data: trendData.map(function(d) { return d.count; }),
                            borderColor: '#dc3545',
                            backgroundColor: 'rgba(220,53,69,0.1)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 3,
                            pointBackgroundColor: '#dc3545',
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { stepSize: 1 } }
                        }
                    }
                });
            } else if (trendCtx) {
                trendCtx.classList.add('d-none');
                document.getElementById('noTrendChart').classList.remove('d-none');
            }
        }

        function updateCharts() {
            var params = {
                from_date: $('input[name="from_date"]').val(),
                to_date: $('input[name="to_date"]').val(),
                academic_year_id: $('select[name="academic_year_id"]').val(),
                class_section_id: $('select[name="class_section_id"]').val(),
                student_id: $('select[name="student_id"]').val()
            };

            $.when(
                $.get("{{ route('reports.attendance.absent_students.chart.class_wise') }}", params),
                $.get("{{ route('reports.attendance.absent_students.chart.trend') }}", params)
            ).done(function(classResp, trendResp) {
                renderCharts(classResp[0], trendResp[0]);
            });
        }

        // Initial chart render
        var initialClassData = @json($classWiseChart);
        var initialTrendData = @json($trendChart);
        renderCharts(initialClassData, initialTrendData);
    });
    </script>
@endpush
