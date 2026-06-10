<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; padding: 0; font-size: 18px; }
        .header p { margin: 5px 0; color: #666; }
        .summary-table { width: 100%; margin-bottom: 20px; }
        .summary-table td { padding: 6px 12px; text-align: center; border: 1px solid #ddd; }
        .summary-table th { background-color: #f5f5f5; padding: 6px 12px; border: 1px solid #ddd; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .text-center { text-align: center; }
        .bg-warning { background-color: #ffc107; color: #000; padding: 2px 6px; border-radius: 3px; font-size: 10px; }
        .bg-danger { background-color: #dc3545; color: #fff; padding: 2px 6px; border-radius: 3px; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Period: {{ $fromDate }} to {{ $toDate }} | Generated on {{ date('Y-m-d H:i:s') }}</p>
    </div>

    @if(!empty($summary))
        <table class="summary-table">
            <tr>
                <th>Total Records</th>
                <th>Present</th>
                <th>Absent</th>
                <th>Late</th>
                <th>Leave</th>
                <th>Attendance %</th>
            </tr>
            <tr class="text-center">
                <td>{{ $summary['total'] }}</td>
                <td>{{ $summary['present'] }}</td>
                <td>{{ $summary['absent'] }}</td>
                <td>{{ $summary['late'] }}</td>
                <td>{{ $summary['leave'] }}</td>
                <td>{{ $summary['percentage'] }}%</td>
            </tr>
        </table>
    @endif

    @if(empty($data))
        <p class="text-center">No absent students found for the selected period.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student Name</th>
                    <th>Admission No</th>
                    <th>Class & Section</th>
                    <th>Parent Name</th>
                    <th>Parent Mobile</th>
                    <th>Attendance Date</th>
                    <th>Status</th>
                    <th>Consecutive Days</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $row['student_name'] }}</td>
                        <td>{{ $row['admission_no'] }}</td>
                        <td>{{ $row['class_section'] }}</td>
                        <td>{{ $row['parent_name'] }}</td>
                        <td>{{ $row['parent_mobile'] }}</td>
                        <td>{{ $row['attendance_date'] }}</td>
                        <td class="text-center">{{ $row['status'] }}</td>
                        <td class="text-center">
                            @if($row['consecutive_days'] >= 3)
                                <span class="bg-danger">{{ $row['consecutive_days'] }} days</span>
                            @else
                                <span class="bg-warning">{{ $row['consecutive_days'] }} day{{ $row['consecutive_days'] > 1 ? 's' : '' }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
