<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11.5px; color: #1e293b; margin: 0; padding: 20px; }
        .header { border-bottom: 2px solid #2563eb; padding-bottom: 12px; margin-bottom: 16px; }
        .header h1 { font-size: 18px; margin: 0 0 4px; color: #1e293b; }
        .header .school { font-size: 13px; color: #64748b; }
        .muted { color: #64748b; font-size: 10.5px; margin-bottom: 12px; line-height: 1.5; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        th { background: #f1f5f9; font-weight: 600; color: #334155; font-size: 10.5px; text-transform: uppercase; letter-spacing: .5px; }
        td { font-size: 11px; }
        .num { text-align: right; }
        .total { font-weight: 700; margin-top: 12px; text-align: right; font-size: 13px; color: #1e293b; }
        .footer { margin-top: 24px; padding-top: 8px; border-top: 1px solid #e2e8f0; font-size: 10px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Fee Receipt</h1>
        <div class="school">{{ setting('school_name', 'School ERP') }}</div>
    </div>
    <div class="muted">
        <strong>Receipt No:</strong> {{ $payment->receipt_number }} &mdash; {{ $payment->paid_on?->format('d M Y') }}
        &mdash; {{ \App\Modules\Fees\Models\FeePayment::paymentModes()[$payment->payment_mode] ?? $payment->payment_mode }}
    </div>
    <p><strong>Student:</strong> {{ $payment->student?->full_name }} ({{ $payment->student?->admission_no }})</p>
    <p><strong>Academic Year:</strong> {{ $payment->academicYear?->name }}</p>
    @if($payment->remarks)
        <p><strong>Remarks:</strong> {{ $payment->remarks }}</p>
    @endif
    <table>
        <thead>
            <tr>
                <th>Fee Head</th>
                <th class="num">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payment->items as $line)
                <tr>
                    <td>{{ $line->studentFeeItem?->feeCategory?->name ?? 'Fee' }}</td>
                    <td class="num">{{ number_format((float) $line->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="total">Total Paid: &#8377;{{ number_format((float) $payment->amount, 2) }}</div>
    <p class="muted">Collected by: {{ $payment->collector?->name ?? '-' }}</p>
    <div class="footer">Generated on {{ now()->format('d M Y, h:i A') }} &mdash; This is a computer-generated receipt.</div>
</body>
</html>
