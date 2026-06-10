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
        .summary-table td { padding: 4px 8px; text-align: center; border: 1px solid #ddd; font-size: 10px; }
        .summary-table th { background-color: #f5f5f5; padding: 4px 8px; border: 1px solid #ddd; }
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
            <th>Total Students</th>
            <th>Male</th>
            <th>Female</th>
            <th>Other</th>
        </tr>
        <tr>
            <td class="text-center fw-bold">{{ $totals['total'] }}</td>
            <td class="text-center">{{ $totals['male'] }} ({{ $totals['total'] > 0 ? round(($totals['male'] / $totals['total']) * 100, 1) : 0 }}%)</td>
            <td class="text-center">{{ $totals['female'] }} ({{ $totals['total'] > 0 ? round(($totals['female'] / $totals['total']) * 100, 1) : 0 }}%)</td>
            <td class="text-center">{{ $totals['other'] }}</td>
        </tr>
    </table>

    @if(empty($rows))
        <p class="text-center">No data available.</p>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th>Class</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">Male</th>
                    <th class="text-center">Female</th>
                    <th class="text-center">Other</th>
                    <th class="text-center">Male %</th>
                    <th class="text-center">Female %</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr>
                        <td>{{ $row['class_name'] }}</td>
                        <td class="text-center fw-bold">{{ $row['total'] }}</td>
                        <td class="text-center">{{ $row['male'] }}</td>
                        <td class="text-center">{{ $row['female'] }}</td>
                        <td class="text-center">{{ $row['other'] }}</td>
                        <td class="text-center">{{ $row['male_pct'] }}%</td>
                        <td class="text-center">{{ $row['female_pct'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th>Total</th>
                    <th class="text-center">{{ $totals['total'] }}</th>
                    <th class="text-center">{{ $totals['male'] }}</th>
                    <th class="text-center">{{ $totals['female'] }}</th>
                    <th class="text-center">{{ $totals['other'] }}</th>
                    <th class="text-center">{{ $totals['total'] > 0 ? round(($totals['male'] / $totals['total']) * 100, 1) : 0 }}%</th>
                    <th class="text-center">{{ $totals['total'] > 0 ? round(($totals['female'] / $totals['total']) * 100, 1) : 0 }}%</th>
                </tr>
            </tfoot>
        </table>
    @endif
</body>
</html>
