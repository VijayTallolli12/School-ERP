<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            padding: 0;
            font-size: 18px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Generated on {{ date('Y-m-d H:i:s') }}</p>
    </div>

    @if(empty($data))
        <p class="text-center">No data available for the selected criteria.</p>
    @else
        <table>
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
                            <td>{{ $row['grade'] }}</td>
                            <td>{{ $row['status'] }}</td>
                        @elseif($type === 'class_performance')
                            <td>{{ $row['class_section'] }}</td>
                            <td>{{ $row['exam_name'] }}</td>
                            <td>{{ $row['total_students'] }}</td>
                            <td>{{ $row['passed'] }}</td>
                            <td>{{ $row['failed'] }}</td>
                            <td>{{ $row['average_marks'] }}</td>
                            <td>{{ $row['pass_percentage'] }}</td>
                            <td>{{ $row['average_percentage'] }}</td>
                        @elseif($type === 'subject_performance')
                            <td>{{ $row['subject'] }}</td>
                            <td>{{ $row['class_section'] }}</td>
                            <td>{{ $row['exam_name'] }}</td>
                            <td>{{ $row['total_students'] }}</td>
                            <td>{{ $row['highest_marks'] }}</td>
                            <td>{{ $row['lowest_marks'] }}</td>
                            <td>{{ $row['average_marks'] }}</td>
                            <td>{{ $row['pass_percentage'] }}</td>
                        @elseif($type === 'student_summary')
                            <td>{{ $row['exam_name'] }}</td>
                            <td>{{ $row['academic_year'] }}</td>
                            <td>{{ $row['total_obtained'] }}</td>
                            <td>{{ $row['total_maximum'] }}</td>
                            <td>{{ $row['percentage'] }}</td>
                            <td>{{ $row['overall_grade'] }}</td>
                            <td>{{ $row['status'] }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>