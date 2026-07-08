<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Monthly Attendance Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 5px 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Monthly Attendance Report</h1>
        <p>
            Class: {{ $classSection ?? 'N/A' }} |
            Month: {{ $monthName ?? '' }} {{ $year ?? '' }} |
            Generated on {{ date('Y-m-d H:i:s') }}
        </p>
    </div>

    @if(empty($data))
        <p class="text-center">No data available for the selected criteria.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student Name</th>
                    <th>Admission No</th>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>Late</th>
                    <th>Leave</th>
                    <th>Total</th>
                    <th>%</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $row['student_name'] }}</td>
                        <td>{{ $row['admission_no'] }}</td>
                        <td>{{ $row['present'] }}</td>
                        <td>{{ $row['absent'] }}</td>
                        <td>{{ $row['late'] }}</td>
                        <td>{{ $row['leave'] }}</td>
                        <td>{{ $row['total'] }}</td>
                        <td>{{ $row['percentage'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
