<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        .header { text-align: center; margin-bottom: 12px; }
        .header h1 { margin: 0; font-size: 16px; }
        .header p { margin: 3px 0; color: #666; }
        .summary-table { width: 100%; margin-bottom: 12px; border-collapse: collapse; }
        .summary-table th, .summary-table td { padding: 4px 8px; text-align: center; border: 1px solid #ddd; font-size: 10px; }
        .summary-table th { background-color: #f5f5f5; }
        table.data { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 4px 6px; text-align: left; font-size: 9px; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Generated on {{ date('Y-m-d H:i:s') }}</p>
    </div>

    <table class="summary-table">
        <tr>
            <th>Total Teachers</th>
            <th>Avg Workload Score</th>
            <th>Avg Classes/Teacher</th>
            <th>Avg Subjects/Teacher</th>
        </tr>
        <tr>
            <td class="text-center fw-bold">{{ $summary['total_teachers'] ?? 0 }}</td>
            <td class="text-center">{{ $summary['avg_workload'] ?? 0 }}</td>
            <td class="text-center">{{ $summary['avg_classes'] ?? 0 }}</td>
            <td class="text-center">{{ $summary['avg_subjects'] ?? 0 }}</td>
        </tr>
    </table>

    @if(empty($rows))
        <p class="text-center">No data available.</p>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Teacher Name</th>
                    <th>Employee ID</th>
                    <th class="text-center">Subjects</th>
                    <th class="text-center">Classes</th>
                    <th class="text-center">Weekly Periods</th>
                    <th class="text-center">Workload Score</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $row['teacher_name'] }}</td>
                        <td>{{ $row['employee_id'] }}</td>
                        <td class="text-center">{{ $row['assigned_subjects'] }}</td>
                        <td class="text-center">{{ $row['assigned_classes'] }}</td>
                        <td class="text-center">{{ $row['weekly_periods'] }}</td>
                        <td class="text-center fw-bold">{{ $row['workload_score'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>