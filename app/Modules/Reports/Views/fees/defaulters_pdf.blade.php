<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
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
        .text-end { text-align: right; }
        .bg-success { background: #d1e7dd; color: #0f5132; padding: 1px 4px; }
        .bg-danger { background: #f8d7da; color: #842029; padding: 1px 4px; }
        .bg-warning { background: #fff3cd; color: #664d03; padding: 1px 4px; }
        .bg-dark { background: #333; color: #fff; padding: 1px 4px; }
        .summary-table { width: 100%; margin-bottom: 12px; border-collapse: collapse; }
        .summary-table td { padding: 4px 8px; text-align: center; border: 1px solid #ddd; font-size: 9px; }
        .summary-table th { background-color: #f5f5f5; padding: 4px 8px; border: 1px solid #ddd; font-size: 9px; }
        .fw-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Generated on {{ date('Y-m-d H:i:s') }}</p>
    </div>

    @php $s = $data['summary']; @endphp
    <table class="summary-table">
        <tr>
            <th>Total Assigned</th>
            <th>Total Collected</th>
            <th>Total Outstanding</th>
            <th>Collection %</th>
            <th>Students with Dues</th>
            <th>Overdue Students</th>
            <th>Highest Outstanding</th>
            <th>Avg Outstanding</th>
        </tr>
        <tr>
            <td class="text-center fw-bold">₹ {{ number_format($s['total_assigned'], 2) }}</td>
            <td class="text-center">₹ {{ number_format($s['total_collected'], 2) }}</td>
            <td class="text-center">₹ {{ number_format($s['total_outstanding'], 2) }}</td>
            <td class="text-center">{{ $s['collection_percentage'] }}%</td>
            <td class="text-center">{{ $s['students_with_dues'] }}</td>
            <td class="text-center">{{ $s['overdue_students'] }}</td>
            <td class="text-center">₹ {{ number_format($s['highest_outstanding'], 2) }}</td>
            <td class="text-center">₹ {{ number_format($s['average_outstanding'], 2) }}</td>
        </tr>
    </table>

    @php $rows = $data['defaulters'] ?? []; @endphp
    @if(empty($rows))
        <p class="text-center">No defaulters found for the selected criteria.</p>
    @else
        <div class="section-title">Defaulter List</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student Name</th>
                    <th>Adm No</th>
                    <th>Class</th>
                    <th>Parent Name</th>
                    <th>Parent Mobile</th>
                    <th>Fee Structure</th>
                    <th class="text-end">Total Fee</th>
                    <th class="text-end">Paid</th>
                    <th class="text-end">Outstanding</th>
                    <th>Due Date</th>
                    <th class="text-center">Overdue</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $i => $row)
                    @php
                        $odClass = $row['days_overdue'] > 60 ? 'bg-dark' : ($row['days_overdue'] > 30 ? 'bg-danger' : ($row['days_overdue'] > 0 ? 'bg-warning' : ''));
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $row['student_name'] }}</td>
                        <td>{{ $row['admission_no'] }}</td>
                        <td>{{ $row['class_section'] }}</td>
                        <td>{{ $row['parent_name'] }}</td>
                        <td>{{ $row['parent_mobile'] }}</td>
                        <td>{{ $row['fee_structure'] }}</td>
                        <td class="text-end">₹ {{ number_format($row['total_fee'], 2) }}</td>
                        <td class="text-end">₹ {{ number_format($row['amount_paid'], 2) }}</td>
                        <td class="text-end fw-bold">₹ {{ number_format($row['outstanding'], 2) }}</td>
                        <td>{{ $row['due_date'] }}</td>
                        <td class="text-center">
                            <span class="{{ $odClass }}">{{ $row['days_overdue'] > 0 ? $row['days_overdue'] . ' days' : '--' }}</span>
                        </td>
                        <td class="text-center">
                            <span class="bg-{{ $row['status'] == 'Paid' ? 'success' : ($row['status'] == 'Overdue' ? 'danger' : 'warning') }}">
                                {{ $row['status'] }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
