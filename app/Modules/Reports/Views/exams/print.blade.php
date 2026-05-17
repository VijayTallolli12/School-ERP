@extends('layouts.admin')

@section("title", $title)
@section("page-title", $title)

@push('styles')
<style>
    @media print {
        body { font-size: 12pt; }
        .no-print { display: none !important; }
        .table-bordered th, .table-bordered td { border: 1px solid #000 !important; }
    }
</style>
@endpush

@section("content")
    <div class="text-right mb-3 no-print">
        <button onclick="window.print()" class="btn btn-primary">Print Document</button>
        <button onclick="window.close()" class="btn btn-secondary">Close</button>
    </div>

    @if(empty($data))
        <p class="text-center">No data available for the selected criteria.</p>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        @if($type === 'results')
                            <th>Student</th>
                            <th>Admission No</th>
                            <th>Exam Name</th>
                            <th>Class & Section</th>
                            <th>Subject</th>
                            <th>Marks Obtained</th>
                            <th>Max Marks</th>
                            <th>Grade</th>
                            <th>Status</th>
                        @elseif($type === 'class_performance')
                            <th>Class & Section</th>
                            <th>Exam Name</th>
                            <th>Total Students</th>
                            <th>Passed</th>
                            <th>Failed</th>
                            <th>Average Marks</th>
                            <th>Pass %</th>
                            <th>Average %</th>
                        @elseif($type === 'subject_performance')
                            <th>Subject</th>
                            <th>Class & Section</th>
                            <th>Exam Name</th>
                            <th>Total Students</th>
                            <th>Highest Marks</th>
                            <th>Lowest Marks</th>
                            <th>Average Marks</th>
                            <th>Pass %</th>
                        @elseif($type === 'student_summary')
                            <th>Exam Name</th>
                            <th>Academic Year</th>
                            <th>Total Obtained</th>
                            <th>Total Maximum</th>
                            <th>Percentage</th>
                            <th>Overall Grade</th>
                            <th>Status</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            @if($type === 'results')
                                <td>{{ $row['student'] }}</td>
                                <td>{{ $row['admission_no'] }}</td>
                                <td>{{ $row['exam_name'] }}</td>
                                <td>{{ $row['class_section'] }}</td>
                                <td>{{ $row['subject'] }}</td>
                                <td>{{ $row['marks_obtained'] }}</td>
                                <td>{{ $row['maximum_marks'] }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ $row['grade'] }}</span>
                                </td>
                                <td>
                                    @if($row['status'] === 'Pass')
                                        <span class="badge bg-success">Pass</span>
                                    @else
                                        <span class="badge bg-danger">Fail</span>
                                    @endif
                                </td>
                            @elseif($type === 'class_performance')
                                <td>{{ $row['class_section'] }}</td>
                                <td>{{ $row['exam_name'] }}</td>
                                <td>{{ $row['total_students'] }}</td>
                                <td class="text-success font-weight-bold">{{ $row['passed'] }}</td>
                                <td class="text-danger font-weight-bold">{{ $row['failed'] }}</td>
                                <td>{{ $row['average_marks'] }}</td>
                                <td>{{ $row['pass_percentage'] }}</td>
                                <td>{{ $row['average_percentage'] }}</td>
                            @elseif($type === 'subject_performance')
                                <td>{{ $row['subject'] }}</td>
                                <td>{{ $row['class_section'] }}</td>
                                <td>{{ $row['exam_name'] }}</td>
                                <td>{{ $row['total_students'] }}</td>
                                <td class="text-success font-weight-bold">{{ $row['highest_marks'] }}</td>
                                <td class="text-danger font-weight-bold">{{ $row['lowest_marks'] }}</td>
                                <td>{{ $row['average_marks'] }}</td>
                                <td>{{ $row['pass_percentage'] }}</td>
                            @elseif($type === 'student_summary')
                                <td>{{ $row['exam_name'] }}</td>
                                <td>{{ $row['academic_year'] }}</td>
                                <td>{{ $row['total_obtained'] }}</td>
                                <td>{{ $row['total_maximum'] }}</td>
                                <td>{{ $row['percentage'] }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ $row['overall_grade'] }}</span>
                                </td>
                                <td>
                                    @if($row['status'] === 'Pass')
                                        <span class="badge bg-success">Pass</span>
                                    @else
                                        <span class="badge bg-danger">Fail</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        window.print();
    });
</script>
@endpush