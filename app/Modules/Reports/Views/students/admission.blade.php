@extends("Reports::reports_layout")

@section("title", "Admission Report")
@section("report_title", "Admission Report")

@section("content")
    <div class="row mb-3">
        <div class="col-md-12">
            <form method="GET" class="row g-3 align-items-end">
                <div class="me-3">
                    <label for="start_date" class="form-label me-2">Start Date:</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="me-3">
                    <label for="end_date" class="form-label me-2">End Date:</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <button type="submit" class="btn btn-primary"><i class="ti ti-report me-1"></i>Generate Report</button>
                <a href="{{ route('reports.students.admission.export', ['type' => 'excel']) . '?' . http_build_query(request()->all()) }}" class="btn btn-success ms-2"><i class="ti ti-file-spreadsheet me-1"></i>Export Excel</a>
                <a href="{{ route('reports.students.admission.export', ['type' => 'pdf']) . '?' . http_build_query(request()->all()) }}" class="btn btn-danger ms-2"><i class="ti ti-file-type-pdf me-1"></i>Export PDF</a>
                <a href="{{ route('reports.students.admission.export', ['type' => 'print']) . '?' . http_build_query(request()->all()) }}" class="btn btn-warning ms-2" target="_blank"><i class="ti ti-printer me-1"></i>Print</a>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <h4>Total Admissions: {{ $totalAdmissions }}</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Class</th>
                        <th>Total Admissions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $item)
                        <tr>
                            <td>{{ $item->class_name }}</td>
                            <td>{{ $item->total_admissions }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
