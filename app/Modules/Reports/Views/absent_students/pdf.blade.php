<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Absent Students Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 9px; }
        .header { text-align: center; margin-bottom: 12px; }
        .header h1 { margin: 0; font-size: 16px; }
        .header p { margin: 3px 0; color: #666; font-size: 10px; }
        .section-title { font-size: 11px; font-weight: bold; margin: 10px 0 5px; padding-bottom: 3px; border-bottom: 2px solid #333; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { border: 1px solid #ddd; padding: 3px 4px; text-align: left; font-size: 8px; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .text-center { text-align: center; }
        .summary-table td { padding: 4px 8px; text-align: center; border: 1px solid #ddd; font-size: 9px; }
        .summary-table th { background-color: #f5f5f5; padding: 4px 8px; border: 1px solid #ddd; font-size: 9px; }
        .fw-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Absent Students Report</h1>
        <p>Generated on {{ date('Y-m-d H:i:s') }}</p>
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
        <tr>
            <td class="text-center fw-bold">{{ $summary['total'] ?? 0 }}</td>
            <td class="text-center">{{ $summary['present'] ?? 0 }}</td>
            <td class="text-center">{{ $summary['absent'] ?? 0 }}</td>
            <td class="text-center">{{ $summary['late'] ?? 0 }}</td>
            <td class="text-center">{{ $summary['leave'] ?? 0 }}</td>
            <td class="text-center">{{ $summary['percentage'] ?? 0 }}%</td>
        </tr>
    </table>
    @endif

    @php $rows = $data ?? []; @endphp
    @if(empty($rows))
        <p class="text-center">No records found for the selected criteria.</p>
    @else
        <div class="section-title">Absent Student Records</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student Name</th>
                    <th>Admission No</th>
                    <th>Class & Section</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th class="text-center">Consecutive Days</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $row['student_name'] }}</td>
                        <td>{{ $row['admission_no'] }}</td>
                        <td>{{ $row['class_section'] }}</td>
                        <td>{{ $row['attendance_date'] }}</td>
                        <td>{{ ucfirst($row['status']) }}</td>
                        <td class="text-center">{{ $row['consecutive_days'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
