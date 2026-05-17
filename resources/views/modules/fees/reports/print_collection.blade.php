<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #1e293b; margin: 0; padding: 20px; }
        .header { border-bottom: 2px solid #2563eb; padding-bottom: 10px; margin-bottom: 14px; }
        .header h1 { font-size: 16px; margin: 0 0 3px; color: #1e293b; }
        .header .school { font-size: 12px; color: #64748b; }
        .meta { font-size: 9.5px; color: #64748b; margin-bottom: 12px; line-height: 1.5; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 5px 6px; text-align: left; }
        th { background: #f1f5f9; font-weight: 600; color: #334155; text-transform: uppercase; letter-spacing: .5px; font-size: 9.5px; }
        td { font-size: 10px; }
        .num { text-align: right; }
        .footer { margin-top: 20px; padding-top: 6px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="school">{{ setting('school_name', 'School ERP') }}</div>
    </div>
    <div class="meta">
        Generated: {{ now()->format('d-M-Y H:i') }}
        @if(!empty($filters['from_date']) || !empty($filters['to_date']))
            &mdash; {{ $filters['from_date'] ?? '…' }} to {{ $filters['to_date'] ?? '…' }}
        @endif
        @if(!empty($filters['payment_mode']))
            &mdash; Mode: {{ \App\Modules\Fees\Models\FeePayment::paymentModes()[$filters['payment_mode']] ?? $filters['payment_mode'] }}
        @endif
    </div>
    <table>
        <thead>
            <tr>
                <th>Receipt</th>
                <th>Date</th>
                <th>Student</th>
                <th>Admission</th>
                <th class="num">Amount</th>
                <th>Mode</th>
                <th>Collector</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['receipt_number'] }}</td>
                    <td>{{ $row['paid_on'] }}</td>
                    <td>{{ $row['student'] }}</td>
                    <td>{{ $row['admission_no'] }}</td>
                    <td class="num">{{ number_format($row['amount'], 2) }}</td>
                    <td>{{ $row['payment_mode'] }}</td>
                    <td>{{ $row['collector'] }}</td>
                </tr>
            @empty
                <tr><td colspan="7">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer">Generated on {{ now()->format('d M Y, h:i A') }}</div>
</body>
</html>
