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
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 10px;
        }
        .bg-danger { background-color: #dc3545; color: white; }
        .bg-success { background-color: #28a745; color: white; }
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
                    @if($type === 'paid')
                        <th>Receipt No</th>
                        <th>Date</th>
                        <th>Student</th>
                        <th>Admission No</th>
                        <th>Amount</th>
                        <th>Payment Mode</th>
                        <th>Collector</th>
                    @elseif($type === 'pending' || $type === 'overdue')
                        <th>Student</th>
                        <th>Admission No</th>
                        <th>Academic Year</th>
                        <th>Category</th>
                        <th>Amount Due</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Due Date</th>
                        @if($type === 'pending')
                            <th>Overdue</th>
                        @endif
                    @elseif($type === 'collection_summary')
                        <th>Class & Section</th>
                        <th>Total Due</th>
                        <th>Total Paid</th>
                        <th>Balance</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        @if($type === 'paid')
                            <td>{{ $row['receipt_number'] }}</td>
                            <td>{{ $row['paid_on'] }}</td>
                            <td>{{ $row['student'] }}</td>
                            <td>{{ $row['admission_no'] }}</td>
                            <td>{{ number_format($row['amount'], 2) }}</td>
                            <td>{{ ucfirst($row['payment_mode']) }}</td>
                            <td>{{ $row['collector'] }}</td>
                        @elseif($type === 'pending' || $type === 'overdue')
                            <td>{{ $row['student'] }}</td>
                            <td>{{ $row['admission_no'] }}</td>
                            <td>{{ $row['academic_year'] }}</td>
                            <td>{{ $row['category'] }}</td>
                            <td>{{ number_format($row['amount'], 2) }}</td>
                            <td>{{ number_format($row['paid'], 2) }}</td>
                            <td>{{ number_format($row['balance'], 2) }}</td>
                            <td>{{ $row['due_date'] }}</td>
                            @if($type === 'pending')
                                <td>{{ $row['overdue'] }}</td>
                            @endif
                        @elseif($type === 'collection_summary')
                            <td>{{ $row['class_section'] }}</td>
                            <td>{{ number_format($row['total_due'], 2) }}</td>
                            <td>{{ number_format($row['total_paid'], 2) }}</td>
                            <td>{{ number_format($row['balance'], 2) }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
