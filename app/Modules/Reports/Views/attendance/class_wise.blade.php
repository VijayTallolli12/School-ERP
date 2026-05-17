@extends("Reports::reports_layout")

@section("title", "Class-wise Attendance Report")
@section("report_title", "Class-wise Attendance Report")

@section("content")
    <form action="{{ route("reports.attendance.class_wise") }}" method="GET">
        <div class="form-group">
            <label for="class_id">Select Class:</label>
            <select name="class_id" id="class_id" class="form-control">
                @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Generate Report</button>
    </form>

    {{-- Report results will be displayed here --}}
@endsection
