@extends("Reports::reports_layout")

@section("title", $title)
@section("report_title", $title)

@section("content")
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Teacher Name</th>
                <th>Employee ID</th>
                <th>Status</th>
                <th>Subjects Taught</th>
                <th>Class Sections</th>
                <th>Total Periods/Week</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    <td>{{ $row['first_name'] ?? '' }} {{ $row['last_name'] ?? '' }}</td>
                    <td>{{ $row['employee_id'] ?? '' }}</td>
                    <td>{{ ucfirst($row['status'] ?? '') }}</td>
                    <td>
                        @if(isset($row['subjects']) && count($row['subjects']) > 0)
                            {{ implode(', ', array_column($row['subjects'], 'name')) }}
                        @else
                            None
                        @endif
                    </td>
                    <td>{{ isset($row['class_sections']) ? count($row['class_sections']) : 0 }} sections</td>
                    <td>{{ $row['total_periods'] ?? 0 }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
