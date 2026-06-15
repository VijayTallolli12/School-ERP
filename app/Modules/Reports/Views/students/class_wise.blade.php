@extends("Reports::reports_layout")

@section("title", "Class-wise Student Report")
@section("report_title", "Class-wise Student Report")

@section("content")
    <div class="row mb-3">
        <div class="col-md-12">
            <form method="GET" class="row g-3 align-items-end">
                <div class="me-3">
                    <label for="academic_year_id" class="form-label me-2">Academic Year:</label>
                    <select name="academic_year_id" id="academic_year_id" class="form-select">
                        <option value="">All</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class="ti ti-report me-1"></i>Generate Report</button>
                <a href="{{ route('reports.students.class_wise.export', ['type' => 'excel']) . '?' . http_build_query(request()->all()) }}" class="btn btn-success ms-2"><i class="ti ti-file-spreadsheet me-1"></i>Export Excel</a>
                <a href="{{ route('reports.students.class_wise.export', ['type' => 'pdf']) . '?' . http_build_query(request()->all()) }}" class="btn btn-danger ms-2"><i class="ti ti-file-type-pdf me-1"></i>Export PDF</a>
                <a href="{{ route('reports.students.class_wise.export', ['type' => 'print']) . '?' . http_build_query(request()->all()) }}" class="btn btn-warning ms-2" target="_blank"><i class="ti ti-printer me-1"></i>Print</a>
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
