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
        .summary {
            margin-bottom: 20px;
        }
        .summary table {
            width: auto;
            margin: 0 auto;
        }
        .summary th, .summary td {
            padding: 6px 12px;
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
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>{{ \Carbon\Carbon::createFromFormat('m', $month)->format('F') }} {{ $year }} | Generated on {{ date('Y-m-d H:i:s') }}</p>
    </div>

    @if(empty($data))
        <p class="text-center">No data available for the selected criteria.</p>
    @else
        <div class="summary">
            <table>
                <tr>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>Late</th>
                    <th>Leave</th>
                </tr>
                <tr class="text-center">
                    <td>{{ $summary['present'] }}</td>
                    <td>{{ $summary['absent'] }}</td>
                    <td>{{ $summary['late'] }}</td>
                    <td>{{ $summary['leave'] }}</td>
                </tr>
            </table>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student Name</th>
                    <th class="text-center">Present</th>
                    <th class="text-center">Absent</th>
                    <th class="text-center">Late</th>
                    <th class="text-center">Leave</th>
                    <th class="text-center">Total</th>
                    <th class="text-right">Percentage</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $row)
                    @php $pct = ($row['total'] ?? 0) > 0 ? round(($row['present'] / $row['total']) * 100, 1) : 0; @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $row['student'] }}</td>
                        <td class="text-center">{{ $row['present'] }}</td>
                        <td class="text-center">{{ $row['absent'] }}</td>
                        <td class="text-center">{{ $row['late'] }}</td>
                        <td class="text-center">{{ $row['leave'] }}</td>
                        <td class="text-center">{{ $row['total'] }}</td>
                        <td class="text-right">{{ $pct }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
