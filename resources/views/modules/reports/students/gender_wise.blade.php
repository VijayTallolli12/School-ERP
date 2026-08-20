@extends('modules.reports.reports_layout')

@section("title", "Gender-wise Student Report")
@section("page-title", "Gender-wise Student Report")

@push('styles')
<style>
</style>
@endpush

@section("content")
    

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
                    <label class="form-label">Class & Section</label>
                    <select name="class_section_id" id="class_section_id" class="form-select">
                        <option value="">All</option>
                        @foreach($classSections as $cs)
                            <option value="{{ $cs->id }}" {{ request('class_section_id') == $cs->id ? 'selected' : '' }}>{{ $cs->schoolClass->name }} - {{ $cs->section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary py-2 flex-fill"><i class="ti ti-filter me-1"></i> Filter</button>
                    <a href="{{ route('reports.students.gender_wise') }}" class="btn btn-outline-secondary py-2"><i class="ti ti-refresh"></i></a>
                </div>
            </form>
            <div class="row mt-3">
                <div class="col-12">
                    <a id="exportExcel" href="{{ route('reports.students.gender_wise.export', ['type' => 'excel']) }}?{{ http_build_query(request()->all()) }}" class="btn btn-sm btn-outline-success me-2"><i class="ti ti-file-type-xls me-1"></i> Export Excel</a>
                    <a id="exportPdf" href="{{ route('reports.students.gender_wise.export', ['type' => 'pdf']) }}?{{ http_build_query(request()->all()) }}" class="btn btn-sm btn-outline-danger me-2"><i class="ti ti-file-type-pdf me-1"></i> Export PDF</a>
                    <a id="exportPrint" href="{{ route('reports.students.gender_wise.export', ['type' => 'print']) }}?{{ http_build_query(request()->all()) }}" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="ti ti-printer me-1"></i> Print</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="erp-hero-card">
                <div>
                    <div class="hero-value" id="totalStudents">{{ $totals['total'] }}</div>
                    <div class="hero-label">Total Students</div>
                </div>
                <div class="hero-icon primary"><i class="ti ti-users"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="erp-hero-card">
                <div>
                    <div class="hero-value" id="maleStudents">{{ $totals['male'] }}</div>
                    <div class="hero-label">Male</div>
                </div>
                <div class="hero-icon info"><i class="ti ti-man"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="erp-hero-card">
                <div>
                    <div class="hero-value" id="femaleStudents">{{ $totals['female'] }}</div>
                    <div class="hero-label">Female</div>
                </div>
                <div class="hero-icon warning"><i class="ti ti-woman"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="erp-hero-card">
                <div>
                    <div class="hero-value" id="otherStudents">{{ $totals['other'] }}</div>
                    <div class="hero-label">Other</div>
                </div>
                <div class="hero-icon secondary"><i class="ti ti-gender-third"></i></div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header"><h5 class="card-title mb-0"><i class="ti ti-chart-donut text-primary me-2"></i>Gender Distribution</h5></div>
                <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 320px;">
                    <canvas id="genderDoughnut" height="280"></canvas>
                    <div id="noDoughnut" class="text-center text-muted d-none"><i class="ti ti-cloud-off fs-32 d-block mb-2"></i> No data</div>
                </div>
            </div>
        </div>
        <div class="col-xl-7">
            <div class="card h-100">
                <div class="card-header"><h5 class="card-title mb-0"><i class="ti ti-chart-bar text-primary me-2"></i>Class-wise Gender Distribution</h5></div>
                <div class="card-body" style="min-height: 320px;">
                    <canvas id="classGenderChart" height="280"></canvas>
                    <div id="noBar" class="text-center text-muted py-5 d-none"><i class="ti ti-cloud-off fs-32 d-block mb-2"></i> No data</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-header"><h5 class="card-title mb-0"><i class="ti ti-table text-primary me-2"></i>Class-wise Breakdown</h5></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Class</th>
                            <th class="text-center">Total Students</th>
                            <th class="text-center">Male</th>
                            <th class="text-center">Female</th>
                            <th class="text-center">Other</th>
                            <th class="text-center">Male %</th>
                            <th class="text-center">Female %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td>{{ $row['class_name'] }}</td>
                                <td class="text-center fw-bold">{{ $row['total'] }}</td>
                                <td class="text-center text-info fw-medium">{{ $row['male'] }}</td>
                                <td class="text-center text-warning fw-medium">{{ $row['female'] }}</td>
                                <td class="text-center text-secondary">{{ $row['other'] }}</td>
                                <td class="text-center">{{ $row['male_pct'] }}%</td>
                                <td class="text-center">{{ $row['female_pct'] }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">No data available</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td>Total</td>
                            <td class="text-center">{{ $totals['total'] }}</td>
                            <td class="text-center">{{ $totals['male'] }}</td>
                            <td class="text-center">{{ $totals['female'] }}</td>
                            <td class="text-center">{{ $totals['other'] }}</td>
                            <td class="text-center">{{ $totals['total'] > 0 ? round(($totals['male'] / $totals['total']) * 100, 1) : 0 }}%</td>
                            <td class="text-center">{{ $totals['total'] > 0 ? round(($totals['female'] / $totals['total']) * 100, 1) : 0 }}%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', async function() {
        const Chart = await window.lazyChart();
        var rows = @json($rows);
        var totals = @json($totals);

        if (totals.total > 0) {
            // Doughnut
            var doughnutCtx = document.getElementById('genderDoughnut');
            if (doughnutCtx) {
                new Chart(doughnutCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Male', 'Female', 'Other'],
                        datasets: [{
                            data: [totals.male, totals.female, totals.other],
                            backgroundColor: ['#0dcaf0', '#ffc107', '#6c757d'],
                            borderWidth: 2,
                            borderColor: '#fff',
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16 } },
                            tooltip: {
                                callbacks: {
                                    label: function(c) {
                                        var pct = totals.total > 0 ? ((c.parsed / totals.total) * 100).toFixed(1) : 0;
                                        return c.label + ': ' + c.parsed + ' (' + pct + '%)';
                                    }
                                }
                            }
                        },
                        cutout: '65%',
                    }
                });
            }

            // Stacked bar
            var barCtx = document.getElementById('classGenderChart');
            if (barCtx) {
                new Chart(barCtx, {
                    type: 'bar',
                    data: {
                        labels: rows.map(function(r) { return r.class_name; }),
                        datasets: [
                            { label: 'Male', data: rows.map(function(r) { return r.male; }), backgroundColor: '#0dcaf0', borderRadius: 2 },
                            { label: 'Female', data: rows.map(function(r) { return r.female; }), backgroundColor: '#ffc107', borderRadius: 2 },
                            { label: 'Other', data: rows.map(function(r) { return r.other; }), backgroundColor: '#6c757d', borderRadius: 2 },
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        scales: {
                            x: { stacked: true, grid: { display: false } },
                            y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 } }
                        },
                        plugins: {
                            legend: { position: 'bottom', labels: { usePointStyle: true, padding: 12 } }
                        }
                    }
                });
            }
        } else {
            document.getElementById('genderDoughnut').classList.add('d-none');
            document.getElementById('noDoughnut').classList.remove('d-none');
            document.getElementById('classGenderChart').classList.add('d-none');
            document.getElementById('noBar').classList.remove('d-none');
        }
    });
</script>
@endpush
