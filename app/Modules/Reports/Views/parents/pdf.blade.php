@extends("Reports::reports_layout")

@section("title", $title)
@section("report_title", $title)

@section("content")
    <table class="table table-bordered">
        <thead>
            <tr>
                @if($type === 'list')
                    <th>Parent Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Occupation</th>
                    <th>Status</th>
                    <th>Linked Students</th>
                    <th>Classes</th>
                @elseif($type === 'mapping')
                    <th>Parent Name</th>
                    <th>Parent Email</th>
                    <th>Student Name</th>
                    <th>Admission No</th>
                    <th>Class/Section</th>
                    <th>Relationship</th>
                    <th>Primary</th>
                @elseif($type === 'activity_summary')
                    <th>Parent Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Linked Students</th>
                    <th>Notifications</th>
                    <th>Attendance Access</th>
                    <th>Fees Access</th>
                    <th>Exam Access</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    @if($type === 'list')
                        <td>{{ $row['parent_name'] ?? '' }}</td>
                        <td>{{ $row['email'] ?? '' }}</td>
                        <td>{{ $row['phone'] ?? '-' }}</td>
                        <td>{{ $row['occupation'] ?? '-' }}</td>
                        <td>{{ ucfirst($row['status'] ?? '') }}</td>
                        <td>{{ $row['linked_students'] ?? 0 }}</td>
                        <td>{{ $row['classes'] ?? '-' }}</td>
                    @elseif($type === 'mapping')
                        <td>{{ $row['parent_name'] ?? '' }}</td>
                        <td>{{ $row['parent_email'] ?? '' }}</td>
                        <td>{{ $row['student_name'] ?? '' }}</td>
                        <td>{{ $row['admission_no'] ?? '' }}</td>
                        <td>{{ $row['class_section'] ?? '-' }}</td>
                        <td>{{ ucfirst($row['relationship'] ?? '') }}</td>
                        <td>{{ ! empty($row['is_primary']) ? 'Yes' : 'No' }}</td>
                    @elseif($type === 'activity_summary')
                        <td>{{ $row['parent_name'] ?? '' }}</td>
                        <td>{{ $row['email'] ?? '' }}</td>
                        <td>{{ $row['phone'] ?? '-' }}</td>
                        <td>{{ $row['linked_students'] ?? 0 }}</td>
                        <td>{{ $row['notifications_count'] ?? 0 }}</td>
                        <td>{{ $row['attendance_access'] ?? 0 }}</td>
                        <td>{{ $row['fees_access'] ?? 0 }}</td>
                        <td>{{ $row['exam_access'] ?? 0 }}</td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
