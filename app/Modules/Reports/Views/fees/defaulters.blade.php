@extends('layouts.admin')

@section("title", "Fee Defaulters Report")
@section("page-title", "Fee Defaulters Report")

@push('styles')
<style>
    .stat-card-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; }
    .overdue-30 { background-color: #fff3cd; color: #664d03; }
    .overdue-60 { background-color: #f8d7da; color: #842029; }
    .overdue-90 { background-color: #dc3545; color: #fff; }
</style>
@endpush

@section("content")
    <div class="mb-3">
        <a href="{{ route('reports.fees.index') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> Back to Fee Reports
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
                    <label class="form-label">Fee Structure</label>
                    <select name="fee_structure_id" id="fee_structure_id" class="form-select">
                        <option value="">All</option>
                        @foreach($feeStructures as $fs)
                            <option value="{{ $fs['id'] }}">{{ $fs['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Due Date</label>
                    <input type="date" name="from_due_date" id="from_due_date" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Due Date</label>
                    <input type="date" name="to_due_date" id="to_due_date" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Min Outstanding</label>
                    <input type="number" name="min_outstanding" id="min_outstanding" class="form-control" step="0.01" min="0" placeholder="0.00">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Max Outstanding</label>
                    <input type="number" name="max_outstanding" id="max_outstanding" class="form-control" step="0.01" min="0" placeholder="999999">
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

    {{-- Collection Summary Cards --}}
    <h5 class="mb-3">Collection Summary</h5>
    <div class="row g-3 mb-4" id="collectionSummaryCards">
        <div class="col-md-3">
            <div class="card border-start border-primary border-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon bg-primary bg-opacity-10">
                        <i class="ti ti-currency-rupee text-primary fs-24"></i>
                    </div>
                    <div>
                        <p class="text-muted fs-13 mb-0">Total Assigned</p>
                        <h4 class="fw-bold mb-0" id="totalAssigned">--</h4>
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
                        <p class="text-muted fs-13 mb-0">Total Collected</p>
                        <h4 class="fw-bold text-success mb-0" id="totalCollected">--</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-danger border-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon bg-danger bg-opacity-10">
                        <i class="ti ti-alert-triangle text-danger fs-24"></i>
                    </div>
                    <div>
                        <p class="text-muted fs-13 mb-0">Total Outstanding</p>
                        <h4 class="fw-bold text-danger mb-0" id="totalOutstanding">--</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-info border-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon bg-info bg-opacity-10">
                        <i class="ti ti-percentage text-info fs-24"></i>
                    </div>
                    <div>
                        <p class="text-muted fs-13 mb-0">Collection %</p>
                        <h4 class="fw-bold text-info mb-0" id="collectionPct">--</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Defaulter Summary Cards --}}
    <h5 class="mb-3">Defaulter Summary</h5>
    <div class="row g-3 mb-4" id="defaulterSummaryCards">
        <div class="col-md-3">
            <div class="card border-start border-warning border-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon bg-warning bg-opacity-10">
                        <i class="ti ti-users text-warning fs-24"></i>
                    </div>
                    <div>
                        <p class="text-muted fs-13 mb-0">Students with Dues</p>
                        <h4 class="fw-bold mb-0" id="studentsWithDues">--</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-danger border-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon bg-danger bg-opacity-10">
                        <i class="ti ti-clock text-danger fs-24"></i>
                    </div>
                    <div>
                        <p class="text-muted fs-13 mb-0">Overdue Students</p>
                        <h4 class="fw-bold text-danger mb-0" id="overdueStudents">--</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-primary border-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon bg-primary bg-opacity-10">
                        <i class="ti ti-arrow-up text-primary fs-24"></i>
                    </div>
                    <div>
                        <p class="text-muted fs-13 mb-0">Highest Outstanding</p>
                        <h4 class="fw-bold text-primary mb-0" id="highestOutstanding">--</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-secondary border-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon bg-secondary bg-opacity-10">
                        <i class="ti ti-chart-bar text-secondary fs-24"></i>
                    </div>
                    <div>
                        <p class="text-muted fs-13 mb-0">Avg Outstanding</p>
                        <h4 class="fw-bold text-secondary mb-0" id="avgOutstanding">--</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Outstanding Fees by Class</h5>
                </div>
                <div class="card-body" style="min-height: 300px;">
                    <canvas id="classOutstandingChart" height="260"></canvas>
                    <div id="noClassChart" class="text-center text-muted py-5 d-none">
                        <i class="ti ti-cloud-off fs-32 d-block mb-2"></i> No data
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Collection vs Outstanding</h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 300px;">
                    <canvas id="collectionDoughnut" height="260"></canvas>
                    <div id="noDoughnutChart" class="text-center text-muted d-none">
                        <i class="ti ti-cloud-off fs-32 d-block mb-2"></i> No data
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Monthly Collection Trend</h5>
                </div>
                <div class="card-body" style="min-height: 300px;">
                    <canvas id="monthlyTrendChart" height="260"></canvas>
                    <div id="noTrendChart" class="text-center text-muted py-5 d-none">
                        <i class="ti ti-cloud-off fs-32 d-block mb-2"></i> No data
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Defaulters DataTable --}}
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Defaulter List</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="defaultersTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Admission No</th>
                            <th>Class & Section</th>
                            <th>Parent Name</th>
                            <th>Parent Mobile</th>
                            <th>Fee Structure</th>
                            <th class="text-end">Total Fee</th>
                            <th class="text-end">Amount Paid</th>
                            <th class="text-end">Outstanding</th>
                            <th>Due Date</th>
                            <th class="text-center">Days Overdue</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
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
        let table = null;
        let classChart = null, doughnutChart = null, trendChart = null;

        function getFilterParams() {
            return {
                academic_year_id: $('#academic_year_id').val(),
                class_section_id: $('#class_section_id').val(),
                student_id: $('#student_id').val(),
                fee_structure_id: $('#fee_structure_id').val(),
                from_due_date: $('#from_due_date').val(),
                to_due_date: $('#to_due_date').val(),
                min_outstanding: $('#min_outstanding').val(),
                max_outstanding: $('#max_outstanding').val(),
            };
        }

        function updateExportLinks() {
            var qs = $.param(getFilterParams());
            $('#exportExcel').attr('href', "{{ route('reports.fees.defaulters.export.excel') }}" + (qs ? '?' + qs : ''));
            $('#exportPdf').attr('href', "{{ route('reports.fees.defaulters.export.pdf') }}" + (qs ? '?' + qs : ''));
            $('#exportPrint').attr('href', "{{ route('reports.fees.defaulters.print') }}" + (qs ? '?' + qs : ''));
        }

        function renderSummary(summary) {
            $('#totalAssigned').text('₹ ' + Number(summary.total_assigned).toLocaleString('en-IN', {minimumFractionDigits: 2}));
            $('#totalCollected').text('₹ ' + Number(summary.total_collected).toLocaleString('en-IN', {minimumFractionDigits: 2}));
            $('#totalOutstanding').text('₹ ' + Number(summary.total_outstanding).toLocaleString('en-IN', {minimumFractionDigits: 2}));
            $('#collectionPct').text(summary.collection_percentage + '%');
            $('#studentsWithDues').text(summary.students_with_dues);
            $('#overdueStudents').text(summary.overdue_students);
            $('#highestOutstanding').text('₹ ' + Number(summary.highest_outstanding).toLocaleString('en-IN', {minimumFractionDigits: 2}));
            $('#avgOutstanding').text('₹ ' + Number(summary.average_outstanding).toLocaleString('en-IN', {minimumFractionDigits: 2}));
        }

        function renderCharts(chartData) {
            // Outstanding by Class (horizontal bar)
            var classCtx = document.getElementById('classOutstandingChart');
            if (classChart) classChart.destroy();
            if (classCtx && chartData.outstanding_by_class && chartData.outstanding_by_class.length > 0) {
                var labels = chartData.outstanding_by_class.map(function(c) { return c.label; });
                var values = chartData.outstanding_by_class.map(function(c) { return c.value; });
                var colors = values.map(function(v) {
                    if (v > 100000) return '#dc3545';
                    if (v > 50000) return '#ffc107';
                    return '#0d6efd';
                });
                classChart = new Chart(classCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Outstanding (₹)',
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
                                    label: function(c) { return '₹ ' + Number(c.parsed.x).toLocaleString('en-IN'); }
                                }
                            }
                        },
                        scales: {
                            x: { beginAtZero: true, ticks: { callback: function(v) { return '₹ ' + Number(v).toLocaleString('en-IN'); } } },
                            y: { grid: { display: false } }
                        }
                    }
                });
                classCtx.classList.remove('d-none');
                document.getElementById('noClassChart').classList.add('d-none');
            } else if (classCtx) {
                classCtx.classList.add('d-none');
                document.getElementById('noClassChart').classList.remove('d-none');
            }

            // Collection vs Outstanding (doughnut)
            var doughnutCtx = document.getElementById('collectionDoughnut');
            if (doughnutChart) doughnutChart.destroy();
            if (doughnutCtx && chartData.collection_vs_outstanding) {
                var cv = chartData.collection_vs_outstanding;
                if (cv.collected > 0 || cv.outstanding > 0) {
                    doughnutChart = new Chart(doughnutCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Collected', 'Outstanding'],
                            datasets: [{
                                data: [cv.collected, cv.outstanding],
                                backgroundColor: ['#198754', '#dc3545'],
                                borderWidth: 2,
                                borderColor: '#fff',
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16 } },
                                tooltip: {
                                    callbacks: {
                                        label: function(c) {
                                            var val = Number(c.parsed);
                                            var total = c.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                                            var pct = total > 0 ? (val / total * 100).toFixed(1) : 0;
                                            return c.label + ': ₹ ' + val.toLocaleString('en-IN') + ' (' + pct + '%)';
                                        }
                                    }
                                }
                            },
                            cutout: '65%',
                        }
                    });
                    doughnutCtx.classList.remove('d-none');
                    document.getElementById('noDoughnutChart').classList.add('d-none');
                } else {
                    doughnutCtx.classList.add('d-none');
                    document.getElementById('noDoughnutChart').classList.remove('d-none');
                }
            } else if (doughnutCtx) {
                doughnutCtx.classList.add('d-none');
                document.getElementById('noDoughnutChart').classList.remove('d-none');
            }

            // Monthly Collection Trend (line)
            var trendCtx = document.getElementById('monthlyTrendChart');
            if (trendChart) trendChart.destroy();
            if (trendCtx && chartData.monthly_trend && chartData.monthly_trend.length > 0) {
                var tLabels = chartData.monthly_trend.map(function(t) { return t.label; });
                var tValues = chartData.monthly_trend.map(function(t) { return t.value; });
                trendChart = new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: tLabels,
                        datasets: [{
                            label: 'Collection (₹)',
                            data: tValues,
                            borderColor: '#0d6efd',
                            backgroundColor: 'rgba(13, 110, 253, 0.1)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointBackgroundColor: '#0d6efd',
                            borderWidth: 2,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(c) { return '₹ ' + Number(c.parsed.y).toLocaleString('en-IN'); }
                                }
                            }
                        },
                        scales: {
                            y: { beginAtZero: true, ticks: { callback: function(v) { return '₹ ' + Number(v).toLocaleString('en-IN'); } } }
                        }
                    }
                });
                trendCtx.classList.remove('d-none');
                document.getElementById('noTrendChart').classList.add('d-none');
            } else if (trendCtx) {
                trendCtx.classList.add('d-none');
                document.getElementById('noTrendChart').classList.remove('d-none');
            }
        }

        function getOverdueBadge(days) {
            if (days <= 0) return '<span class="badge bg-success">On Time</span>';
            if (days <= 30) return '<span class="badge bg-warning text-dark">' + days + ' days</span>';
            if (days <= 60) return '<span class="badge bg-danger">' + days + ' days</span>';
            return '<span class="badge bg-dark">' + days + ' days</span>';
        }

        function getStatusBadge(status) {
            if (status === 'Paid') return '<span class="badge bg-success"><i class="ti ti-circle-check me-1"></i>Paid</span>';
            if (status === 'Overdue') return '<span class="badge bg-danger"><i class="ti ti-alert-triangle me-1"></i>Overdue</span>';
            return '<span class="badge bg-warning text-dark"><i class="ti ti-clock me-1"></i>Pending</span>';
        }

        function loadData() {
            var params = getFilterParams();
            $.ajax({
                url: "{{ route('reports.fees.defaulters') }}",
                type: 'GET',
                data: params,
                dataType: 'json',
                beforeSend: function() {
                    // Could add loading indicator
                },
                success: function(response) {
                    renderSummary(response.summary);
                    renderCharts(response.chartData);

                    if (table) {
                        table.clear();
                        table.rows.add(response.defaulters);
                        table.draw();
                    } else {
                        table = $('#defaultersTable').DataTable({
                            data: response.defaulters,
                            columns: [
                                { data: 'DT_RowIndex', orderable: false, searchable: false },
                                { data: 'student_name', name: 'student_name' },
                                { data: 'admission_no', name: 'admission_no' },
                                { data: 'class_section', name: 'class_section' },
                                { data: 'parent_name', name: 'parent_name' },
                                { data: 'parent_mobile', name: 'parent_mobile' },
                                { data: 'fee_structure', name: 'fee_structure' },
                                {
                                    data: 'total_fee', name: 'total_fee', className: 'text-end',
                                    render: function(d) { return '₹ ' + Number(d).toLocaleString('en-IN', {minimumFractionDigits: 2}); }
                                },
                                {
                                    data: 'amount_paid', name: 'amount_paid', className: 'text-end',
                                    render: function(d) { return '₹ ' + Number(d).toLocaleString('en-IN', {minimumFractionDigits: 2}); }
                                },
                                {
                                    data: 'outstanding', name: 'outstanding', className: 'text-end',
                                    render: function(d) {
                                        var cls = d > 0 ? 'text-danger fw-bold' : 'text-success';
                                        return '<span class="' + cls + '">₹ ' + Number(d).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</span>';
                                    }
                                },
                                { data: 'due_date', name: 'due_date' },
                                {
                                    data: null, name: 'days_overdue', className: 'text-center',
                                    render: function(row) { return getOverdueBadge(row.days_overdue); }
                                },
                                {
                                    data: 'status', name: 'status', className: 'text-center',
                                    render: function(d) { return getStatusBadge(d); }
                                },
                                {
                                    data: null, className: 'text-center', orderable: false, searchable: false,
                                    render: function(row) {
                                        return '<div class="dropdown">' +
                                            '<button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>' +
                                            '<ul class="dropdown-menu dropdown-menu-end">' +
                                            '<li><a class="dropdown-item" href="#" onclick="alert(\'View Parent: ' + row.parent_name.replace(/'/g, "\\'") + '\')"><i class="ti ti-user me-2"></i>View Parent</a></li>' +
                                            '<li><a class="dropdown-item" href="#" onclick="alert(\'Student Fee History: ' + row.student_name.replace(/'/g, "\\'") + '\')"><i class="ti ti-receipt me-2"></i>Fee History</a></li>' +
                                            '</ul></div>';
                                    }
                                }
                            ],
                            order: [[10, 'asc']],
                            pageLength: 25,
                            drawCallback: function() {
                                var api = this.api();
                                api.rows().every(function() {
                                    var d = this.data();
                                    d.DT_RowIndex = this.index() + 1;
                                });
                            }
                        });
                    }

                    updateExportLinks();
                },
                error: function(xhr) {
                    Toastr.error('Failed to load defaulter data: ' + (xhr.responseJSON?.message || 'Unknown error'));
                }
            });
        }

        // Load student dropdown on class change
        $('#class_section_id').on('change', function() {
            var csId = $(this).val();
            var $studentSelect = $('#student_id');
            $studentSelect.html('<option value="">Loading...</option>');
            if (csId) {
                $.get('{{ route('reports.fees.defaulters.students_by_class') }}', { class_section_id: csId }, function(students) {
                    $studentSelect.html('<option value="">All Students</option>');
                    $.each(students, function(i, s) {
                        $studentSelect.append('<option value="' + s.id + '">' + s.text + '</option>');
                    });
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

        // Initial load
        loadData();
    });
</script>
@endpush
