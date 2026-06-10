@extends("modules.reports.reports_layout")

@section("title", "Admission Report")
@section("report_title", "Admission Report")

@section("content")
    <div class="row mb-3">
        <div class="col-md-12">
            <form id="filterForm" class="row g-3" method="GET">
                <div class="col-auto">
                    <label for="start_date" class="form-label">From Date:</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-auto">
                    <label for="end_date" class="form-label">To Date:</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col-auto d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary py-2"><i class="ti ti-filter me-1"></i> Filter</button>
                    <a href="{{ route('reports.students.admission') }}" class="btn btn-outline-secondary py-2"><i class="ti ti-refresh me-1"></i> Reset</a>
                </div>
            </form>
            <div class="row mt-3">
                <div class="col-12">
                    <a href="{{ route('reports.students.admission.export', ['type' => 'excel']) }}?{{ http_build_query(request()->all()) }}" class="btn btn-success py-2"><i class="ti ti-file-type-xls me-1"></i> Export Excel</a>
                    <a href="{{ route('reports.students.admission.export', ['type' => 'pdf']) }}?{{ http_build_query(request()->all()) }}" class="btn btn-danger py-2"><i class="ti ti-file-type-pdf me-1"></i> Export PDF</a>
                    <a href="{{ route('reports.students.admission.export', ['type' => 'print']) }}?{{ http_build_query(request()->all()) }}" class="btn btn-warning py-2" target="_blank"><i class="ti ti-printer me-1"></i> Print</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="fw-semibold mb-0">Total Admissions: <span class="text-primary">{{ $totalAdmissions }}</span></h5>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover" id="admissionTable">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Class Name</th>
                    <th class="text-center">Total Admissions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $row->class_name }}</td>
                        <td class="text-center fw-bold">{{ $row->total_admissions }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">No admission data found</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td></td>
                    <td>Total</td>
                    <td class="text-center">{{ $totalAdmissions }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
@endsection
