@extends("modules.reports.reports_layout")

@section("title", "Class-Wise Student Report")
@section("report_title", "Class-Wise Student Report")

@section("content")
    <div class="row mb-3">
        <div class="col-md-12">
            <form id="filterForm" class="row g-3" method="GET">
                <div class="col-auto">
                    <label for="academic_year_id" class="form-label">Academic Year:</label>
                    <select name="academic_year_id" id="academic_year_id" class="form-select">
                        <option value="">All</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary py-2"><i class="ti ti-filter me-1"></i> Filter</button>
                    <a href="{{ route('reports.students.class_wise') }}" class="btn btn-outline-secondary py-2"><i class="ti ti-refresh me-1"></i> Reset</a>
                </div>
            </form>
            <div class="row mt-3">
                <div class="col-12">
                    <a href="{{ route('reports.students.class_wise.export', ['type' => 'excel']) }}?{{ http_build_query(request()->all()) }}" class="btn btn-success py-2"><i class="ti ti-file-type-xls me-1"></i> Export Excel</a>
                    <a href="{{ route('reports.students.class_wise.export', ['type' => 'pdf']) }}?{{ http_build_query(request()->all()) }}" class="btn btn-danger py-2"><i class="ti ti-file-type-pdf me-1"></i> Export PDF</a>
                    <a href="{{ route('reports.students.class_wise.export', ['type' => 'print']) }}?{{ http_build_query(request()->all()) }}" class="btn btn-warning py-2" target="_blank"><i class="ti ti-printer me-1"></i> Print</a>
                </div>
            </div>
        </div>
    </div>

    @php
        $totalStudents = $data->sum('total_students');
        $totalMale = $data->sum('male_count');
        $totalFemale = $data->sum('female_count');
        $totalActive = $data->sum('active_count');
        $totalInactive = $data->sum('inactive_count');
    @endphp

    <div class="table-responsive">
        <table class="table table-bordered table-hover" id="classWiseTable">
            <thead class="table-light">
                <tr>
                    <th>Class</th>
                    <th class="text-center">Total Students</th>
                    <th class="text-center">Male</th>
                    <th class="text-center">Female</th>
                    <th class="text-center">Active</th>
                    <th class="text-center">Inactive</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                    <tr>
                        <td>{{ $row->class_name }}</td>
                        <td class="text-center fw-bold">{{ $row->total_students }}</td>
                        <td class="text-center">{{ $row->male_count }}</td>
                        <td class="text-center">{{ $row->female_count }}</td>
                        <td class="text-center"><span class="badge bg-success">{{ $row->active_count }}</span></td>
                        <td class="text-center"><span class="badge bg-danger">{{ $row->inactive_count }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No data available</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td>Total</td>
                    <td class="text-center">{{ $totalStudents }}</td>
                    <td class="text-center">{{ $totalMale }}</td>
                    <td class="text-center">{{ $totalFemale }}</td>
                    <td class="text-center">{{ $totalActive }}</td>
                    <td class="text-center">{{ $totalInactive }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
@endsection
