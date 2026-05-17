@extends("Reports::reports_layout")

@section("title", "Admission Report")
@section("report_title", "Admission Report")

@section("content")
    <div class="row mb-3">
        <div class="col-md-12">
            <form method="GET" class="form-inline">
                <div class="form-group mr-3">
                    <label for="start_date" class="mr-2">Start Date:</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="form-group mr-3">
                    <label for="end_date" class="mr-2">End Date:</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <button type="submit" class="btn btn-primary">Generate Report</button>
                <a href="{{ route('reports.students.admission.export', ['type' => 'excel']) . '?' . http_build_query(request()->all()) }}" class="btn btn-success ml-2">Export Excel</a>
                <a href="{{ route('reports.students.admission.export', ['type' => 'pdf']) . '?' . http_build_query(request()->all()) }}" class="btn btn-danger ml-2">Export PDF</a>
                <a href="{{ route('reports.students.admission.export', ['type' => 'print']) . '?' . http_build_query(request()->all()) }}" class="btn btn-warning ml-2" target="_blank">Print</a>
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
