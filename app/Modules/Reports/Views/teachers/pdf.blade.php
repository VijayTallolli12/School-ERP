@extends("Reports::reports_layout")

@section("title", $title)
@section("report_title", $title)

@section("content")
    <table class="table table-bordered">
        <thead>
            @if(count($data) > 0)
                <tr>
                    @if($type === 'list')
                        <th>Name</th>
                        <th>Employee ID</th>
                        <th>Status</th>
                        <th>Joining Date</th>
                    @elseif($type === 'attendance')
                        <th>Teacher Name</th>
                        <th>Employee ID</th>
                        <th>Status</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Late</th>
                        <th>Half Day</th>
                        <th>Excused</th>
                        <th>Total</th>
                        <th>%</th>
                    @elseif($type === 'subject_allocation')
                        <th>Teacher Name</th>
                        <th>Employee ID</th>
                        <th>Subjects</th>
                    @elseif($type === 'class_teacher_mapping')
                        <th>Teacher Name</th>
                        <th>Employee ID</th>
                        <th>Class - Section</th>
                    @endif
                </tr>
            @endif
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    @if($type === 'list')
                        <td>{{ $row['first_name'] ?? '' }} {{ $row['last_name'] ?? '' }}</td>
                        <td>{{ $row['employee_id'] ?? '' }}</td>
                        <td>{{ $row['status'] ?? '' }}</td>
                        <td>{{ $row['joining_date'] ?? '' }}</td>
                    @elseif($type === 'attendance')
                        <td>{{ $row['teacher_name'] ?? trim(($row['teacher']['first_name'] ?? '') . ' ' . ($row['teacher']['last_name'] ?? '')) }}</td>
                        <td>{{ $row['employee_id'] ?? ($row['teacher']['employee_id'] ?? '') }}</td>
                        <td>{{ ucfirst($row['status'] ?? ($row['teacher']['status'] ?? '')) }}</td>
                        <td>{{ $row['present'] ?? 0 }}</td>
                        <td>{{ $row['absent'] ?? 0 }}</td>
                        <td>{{ $row['late'] ?? 0 }}</td>
                        <td>{{ $row['half_day'] ?? 0 }}</td>
                        <td>{{ $row['excused'] ?? 0 }}</td>
                        <td>{{ $row['total'] ?? 0 }}</td>
                        <td>{{ $row['percentage'] ?? 0 }}%</td>
                    @elseif($type === 'subject_allocation')
                        <td>{{ $row['first_name'] ?? '' }} {{ $row['last_name'] ?? '' }}</td>
                        <td>{{ $row['employee_id'] ?? '' }}</td>
                        <td>
                            @if(isset($row['subjects']) && count($row['subjects']) > 0)
                                {{ implode(', ', array_column($row['subjects'], 'name')) }}
                            @else
                                None
                            @endif
                        </td>
                    @elseif($type === 'class_teacher_mapping')
                        <td>{{ $row['first_name'] ?? '' }} {{ $row['last_name'] ?? '' }}</td>
                        <td>{{ $row['employee_id'] ?? '' }}</td>
                        <td>
                            @if(isset($row['class_teacher_sections']) && count($row['class_teacher_sections']) > 0)
                                {{ implode(', ', array_map(function($s) { return ($s['school_class']['name'] ?? '') . ' - ' . ($s['section']['name'] ?? ''); }, $row['class_teacher_sections'])) }}
                            @else
                                None
                            @endif
                        </td>
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
