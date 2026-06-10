<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; padding: 0; font-size: 18px; }
        .header p { margin: 5px 0; color: #666; }
        .summary-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .summary-table td { padding: 6px 12px; text-align: center; border: 1px solid #ddd; }
        .summary-table th { background-color: #f5f5f5; padding: 6px 12px; border: 1px solid #ddd; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 5px 6px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .bg-success { background-color: #d1e7dd; color: #0f5132; padding: 1px 6px; border-radius: 3px; }
        .bg-warning { background-color: #fff3cd; color: #664d03; padding: 1px 6px; border-radius: 3px; }
        .bg-info { background-color: #cff4fc; color: #055160; padding: 1px 6px; border-radius: 3px; }
        .bg-danger { background-color: #f8d7da; color: #842029; padding: 1px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Top {{ $topN }} | Generated on {{ date('Y-m-d H:i:s') }}</p>
    </div>

    @php $summary = $data['summary'] ?? []; @endphp
    @if(!empty($summary) && ($summary['students_evaluated'] ?? 0) > 0)
        <table class="summary-table">
            <tr>
                <th>Highest %</th>
                <th>Top Student</th>
                <th>Class Average</th>
                <th>Students Evaluated</th>
            </tr>
            <tr>
                <td class="text-center fw-bold">{{ $summary['highest_percentage'] }}%</td>
                <td class="text-center">{{ $summary['top_student'] }}</td>
                <td class="text-center">{{ $summary['class_average'] }}%</td>
                <td class="text-center">{{ $summary['students_evaluated'] }}</td>
            </tr>
        </table>
    @endif

    @php $ranked = $data['ranked'] ?? []; @endphp
    @if(empty($ranked))
        <p class="text-center">No data available for the selected criteria.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th class="text-center">Rank</th>
                    <th>Student Name</th>
                    <th>Admission No</th>
                    <th>Class & Section</th>
                    <th>Exam Name</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">Obtained</th>
                    <th class="text-center">%</th>
                    <th class="text-center">Grade</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ranked as $row)
                    @php
                        $pct = $row['percentage'];
                        $pctClass = $pct >= 80 ? 'bg-success' : ($pct >= 60 ? 'bg-warning' : ($pct >= 40 ? 'bg-info' : 'bg-danger'));
                    @endphp
                    <tr>
                        <td class="text-center fw-bold">{{ $row['rank'] }}</td>
                        <td>{{ $row['student_name'] }}</td>
                        <td>{{ $row['admission_no'] }}</td>
                        <td>{{ $row['class_section'] }}</td>
                        <td>{{ $row['exam_name'] }}</td>
                        <td class="text-center">{{ $row['total_marks'] }}</td>
                        <td class="text-center">{{ $row['obtained_marks'] }}</td>
                        <td class="text-center"><span class="{{ $pctClass }}">{{ $pct }}%</span></td>
                        <td class="text-center">{{ $row['grade'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
