@extends('layouts.admin')

@section("title", "Absent Students Report")
@section("page-title", "Absent Students Report")

@push('styles')
<style>
    .stat-card-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; }
</style>
@endpush

@section("content")
    <div class="mb-3">
        <a href="{{ route('reports.attendance.index') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i> Back to Attendance Dashboard</a>
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
                    <label class="form-label">Student</label>
                    <select name="student_id" id="student_id" class="form-select">
                        <option value="">All Students</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" id="from_date" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" id="to_date" class="form-control">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="d-flex gap-2 w-100">
                        <button type="button" id="filterBtn" class="btn btn-primary py-2 flex-fill"><i class="ti ti-filter me-1"></i> Filter</button>
                        <button type="button" id="resetBtn" class="btn btn-outline-secondary py-2"><i class="ti ti-refresh"></i></button>
                    </div>
                </div>
            </form>
            <div class="row mt-3">
                <div class="col-12">
                    <button type="button" id="exportExcel" class="btn btn-success me-2"><i class="ti ti-file-type-xls me-1"></i> Export Excel</button>
                    <button type="button" id="exportPdf" class="btn btn-danger me-2"><i class="ti ti-file-type-pdf me-1"></i> Export PDF</button>
                    <button type="button" id="exportPrint" class="btn btn-warning"><i class="ti ti-printer me-1"></i> Print</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4" id="summaryCards">
        <div class="col-md-3">
            <div class="card border-start border-primary border-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon bg-primary bg-opacity-10"><i class="ti ti-users text-primary fs-24"></i></div>
                    <div>
                        <p class="text-muted fs-13 mb-0">Total Records</p>
                        <h4 class="fw-bold mb-0" id="totalRecords">--</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-success border-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon bg-success bg-opacity-10"><i class="ti ti-user-check text-success fs-24"></i></div>
                    <div>
                        <p class="text-muted fs-13 mb-0">Present</p>
                        <h4 class="fw-bold text-success mb-0" id="totalPresent">--</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-danger border-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon bg-danger bg-opacity-10"><i class="ti ti-user-x text-danger fs-24"></i></div>
                    <div>
                        <p class="text-muted fs-13 mb-0">Absent</p>
                        <h4 class="fw-bold text-danger mb-0" id="totalAbsent">--</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-info border-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon bg-info bg-opacity-10"><i class="ti ti-percentage text-info fs-24"></i></div>
                    <div>
                        <p class="text-muted fs-13 mb-0">Attendance %</p>
                        <h4 class="fw-bold text-info mb-0" id="attendancePct">--</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header"><h5 class="card-title mb-0"><i class="ti ti-chart-bar text-primary me-2"></i>Class-wise Absences</h5></div>
                <div class="card-body" style="min-height: 280px;">
                    <canvas id="classWiseChart" height="250"></canvas>
                    <div id="noClassChart" class="text-center text-muted py-5 d-none"><i class="ti ti-cloud-off fs-32 d-block mb-2"></i> No data</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header"><h5 class="card-title mb-0"><i class="ti ti-chart-line text-primary me-2"></i>Absence Trend</h5></div>
                <div class="card-body" style="min-height: 280px;">
                    <canvas id="trendChart" height="250"></canvas>
                    <div id="noTrendChart" class="text-center text-muted py-5 d-none"><i class="ti ti-cloud-off fs-32 d-block mb-2"></i> No data</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card">
        <div class="card-header"><h5 class="card-title mb-0"><i class="ti ti-list text-primary me-2"></i>Absent Student Records</h5></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="absentTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Admission No</th>
                            <th>Class & Section</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Consecutive Days</th>
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
    $(async function() {
        const Chart = await window.lazyChart();
        const DataTable = await window.lazyDT();
        let table = null;
        let classChart = null, trendChart = null;

        function getFilterParams() {
            return {
                academic_year_id: $('#academic_year_id').val(),
                class_section_id: $('#class_section_id').val(),
                student_id: $('#student_id').val(),
                from_date: $('#from_date').val(),
                to_date: $('#to_date').val()
            };
        }

        function getExportUrl(base) {
            var qs = $.param(getFilterParams());
            return base + (qs ? '?' + qs : '');
        }

        function updateExportLinks() {
            $('#exportExcel').off('click').on('click', function() { window.location.href = getExportUrl('{{ route('reports.attendance.absent_students.export.excel') }}'); });
            $('#exportPdf').off('click').on('click', function() { window.open(getExportUrl('{{ route('reports.attendance.absent_students.export.pdf') }}'), '_blank'); });
            $('#exportPrint').off('click').on('click', function() { window.open(getExportUrl('{{ route('reports.attendance.absent_students.print') }}'), '_blank'); });
        }

        function renderSummary(summary) {
            $('#totalRecords').text(summary.total || 0);
            $('#totalPresent').text(summary.present || 0);
            $('#totalAbsent').text(summary.absent || 0);
            $('#attendancePct').text((summary.percentage || 0) + '%');
        }

        function renderCharts(chartData) {
            var classCtx = document.getElementById('classWiseChart');
            if (classChart) classChart.destroy();
            if (classCtx && chartData.classWise && chartData.classWise.length > 0) {
                var labels = chartData.classWise.map(function(c) { return c.label; });
                var values = chartData.classWise.map(function(c) { return c.count; });
                var colors = ['#2563eb','#16a34a','#0ea5e9','#d97706','#dc2626','#8b5cf6','#ec4899','#14b8a6'];
                classChart = new Chart(classCtx, {
                    type: 'bar',
                    data: { labels: labels, datasets: [{ label: 'Absences', data: values, backgroundColor: colors, borderRadius: 4 }] },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.06)' } }, x: { grid: { display: false } } }
                    }
                });
                classCtx.classList.remove('d-none');
                document.getElementById('noClassChart').classList.add('d-none');
            } else if (classCtx) {
                classCtx.classList.add('d-none');
                document.getElementById('noClassChart').classList.remove('d-none');
            }

            var trendCtx = document.getElementById('trendChart');
            if (trendChart) trendChart.destroy();
            if (trendCtx && chartData.trend && chartData.trend.length > 0) {
                var tLabels = chartData.trend.map(function(t) { return t.date; });
                var tValues = chartData.trend.map(function(t) { return t.count; });
                trendChart = new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: tLabels,
                        datasets: [{ label: 'Absences', data: tValues, borderColor: '#dc2626', backgroundColor: 'rgba(220,38,38,0.1)', fill: true, tension: 0.4, pointRadius: 3 }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.06)' } } }
                    }
                });
                trendCtx.classList.remove('d-none');
                document.getElementById('noTrendChart').classList.add('d-none');
            } else if (trendCtx) {
                trendCtx.classList.add('d-none');
                document.getElementById('noTrendChart').classList.remove('d-none');
            }
        }

        function loadData() {
            var params = getFilterParams();
            $.ajax({
                url: "{{ route('reports.attendance.absent_students.data') }}",
                type: 'GET',
                data: params,
                dataType: 'json',
                success: function(response) {
                    renderSummary(response.summary);
                    renderCharts(response.charts);

                    if (table) {
                        table.clear();
                        table.rows.add(response.records);
                        table.draw();
                    } else {
                        table = $('#absentTable').DataTable({
                            data: response.records,
                            columns: [
                                { data: null, orderable: false, searchable: false, render: function(data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; } },
                                { data: 'student_name', name: 'student_name' },
                                { data: 'admission_no', name: 'admission_no' },
                                { data: 'class_section', name: 'class_section' },
                                { data: 'attendance_date', name: 'attendance_date' },
                                {
                                    data: 'status', name: 'status',
                                    render: function(d) {
                                        var map = { 'absent': '<span class="badge bg-danger">Absent</span>', 'late': '<span class="badge bg-warning text-dark">Late</span>', 'leave': '<span class="badge bg-info">Leave</span>' };
                                        return map[d] || d;
                                    }
                                },
                                { data: 'consecutive_days', name: 'consecutive_days', className: 'text-center' }
                            ],
                            pageLength: 25
                        });
                    }

                    updateExportLinks();
                },
                error: function(xhr) {
                    Toastr.error('Failed to load data: ' + (xhr.responseJSON?.message || 'Unknown error'));
                }
            });
        }

        $('#class_section_id').on('change', function() {
            var csId = $(this).val();
            var $studentSelect = $('#student_id');
            $studentSelect.html('<option value="">Loading...</option>');
            if (csId) {
                $.get('{{ route('reports.attendance.absent_students.students.by_class') }}', { class_section_id: csId }, function(students) {
                    $studentSelect.html('<option value="">All Students</option>');
                    $.each(students, function(i, s) { $studentSelect.append('<option value="' + s.id + '">' + s.name + '</option>'); });
                });
            } else {
                $studentSelect.html('<option value="">All Students</option>');
            }
        });

        $('#filterBtn').on('click', loadData);
        $('#resetBtn').on('click', function() {
            $('#filterForm')[0].reset();
            $('#student_id').html('<option value="">All Students</option>');
            loadData();
        });

        loadData();
    });
</script>
@endpush
