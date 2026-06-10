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
        .overall {
            margin-bottom: 20px;
        }
        .overall table {
            width: auto;
            margin: 0 auto;
        }
        .overall th, .overall td {
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
        <p>Date: {{ $date }} | Generated on {{ date('Y-m-d H:i:s') }}</p>
    </div>

    @if(empty($data))
        <p class="text-center">No data available for the selected criteria.</p>
    @else
        @php
            $totalPresent = collect($data)->sum('present');
            $totalAbsent = collect($data)->sum('absent');
            $totalLate = collect($data)->sum('late');
            $totalLeave = collect($data)->sum('leave');
            $totalRecords = collect($data)->sum('total');
            $overallPct = $totalRecords > 0 ? round(($totalPresent / $totalRecords) * 100, 1) : 0;
        @endphp

        <div class="overall">
            <table>
                <tr>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>Late</th>
                    <th>Leave</th>
                    <th>Total</th>
                    <th>Overall %</th>
                </tr>
                <tr class="text-center">
                    <td>{{ $totalPresent }}</td>
                    <td>{{ $totalAbsent }}</td>
                    <td>{{ $totalLate }}</td>
                    <td>{{ $totalLeave }}</td>
                    <td>{{ $totalRecords }}</td>
                    <td>{{ $overallPct }}%</td>
                </tr>
            </table>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Class Section</th>
                    <th class="text-center">Present</th>
                    <th class="text-center">Absent</th>
                    <th class="text-center">Late</th>
                    <th class="text-center">Leave</th>
                    <th class="text-center">Total</th>
                    <th class="text-right">Attendance %</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $row)
                    @php $pct = ($row['total'] ?? 0) > 0 ? round(($row['present'] / $row['total']) * 100, 1) : 0; @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $row['class_section'] }}</td>
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
