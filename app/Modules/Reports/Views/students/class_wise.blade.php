@extends("Reports::reports_layout")

@section("title", "Class-wise Student Report")
@section("report_title", "Class-wise Student Report")

@section("content")
    <div class="row mb-3">
        <div class="col-md-12">
            <form method="GET" class="form-inline">
                <div class="form-group mr-3">
                    <label for="academic_year_id" class="mr-2">Academic Year:</label>
                    <select name="academic_year_id" id="academic_year_id" class="form-control">
                        <option value="">All</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Generate Report</button>
                <a href="{{ route('reports.students.class_wise.export', ['type' => 'excel']) . '?' . http_build_query(request()->all()) }}" class="btn btn-success ml-2">Export Excel</a>
                <a href="{{ route('reports.students.class_wise.export', ['type' => 'pdf']) . '?' . http_build_query(request()->all()) }}" class="btn btn-danger ml-2">Export PDF</a>
                <a href="{{ route('reports.students.class_wise.export', ['type' => 'print']) . '?' . http_build_query(request()->all()) }}" class="btn btn-warning ml-2" target="_blank">Print</a>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Class</th>
                        <th>Total Students</th>
                        <th>Male</th>
                        <th>Female</th>
                        <th>Active</th>
                        <th>Inactive</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $item)
                        <tr>
                            <td>{{ $item->class_name }}</td>
                            <td>{{ $item->total_students }}</td>
                            <td>{{ $item->male_count }}</td>
                            <td>{{ $item->female_count }}</td>
                            <td>{{ $item->active_count }}</td>
                            <td>{{ $item->inactive_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
