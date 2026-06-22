<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Payslip - {{ $payslip->payslip_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1a73e8; padding-bottom: 15px; }
        .header img { max-height: 60px; margin-bottom: 5px; }
        .header h2 { margin: 5px 0; color: #1a73e8; font-size: 18px; }
        .header .subtitle { font-size: 12px; color: #666; }
        .payslip-no { text-align: right; font-size: 12px; font-weight: bold; color: #1a73e8; margin-bottom: 10px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .info-table td { padding: 4px 8px; border: 1px solid #ddd; }
        .info-table .label { background: #f5f5f5; font-weight: bold; width: 25%; }
        .breakdown-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .breakdown-table th { background: #1a73e8; color: #fff; padding: 6px 8px; text-align: left; font-size: 11px; }
        .breakdown-table td { padding: 4px 8px; border: 1px solid #ddd; }
        .breakdown-table .amount { text-align: right; }
        .breakdown-table .total-row { font-weight: bold; background: #f0f7ff; }
        .summary-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .summary-table td { padding: 6px 10px; border: 1px solid #ddd; font-weight: bold; }
        .summary-table .label { background: #f5f5f5; width: 40%; }
        .summary-table .amount { text-align: right; font-size: 13px; }
        .summary-table .net-salary { color: #1a73e8; font-size: 15px; }
        .footer { text-align: center; font-size: 10px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="payslip-no">Payslip #{{ $payslip->payslip_number }}</div>

    <div class="header">
        @if ($school && $school->logo_path)
            <img src="{{ public_path('storage/' . $school->logo_path) }}" alt="Logo">
        @endif
        <h2>{{ $school?->name ?? 'School' }}</h2>
        <div class="subtitle">Salary Payslip for {{ $run->month_name }} {{ $run->year }}</div>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Employee Name</td>
            <td>{{ $payslip->employee_name }}</td>
            <td class="label">Employee ID</td>
            <td>{{ $payslip->employee_id }}</td>
        </tr>
        <tr>
            <td class="label">Department</td>
            <td>{{ $payslip->department_name ?? '-' }}</td>
            <td class="label">Designation</td>
            <td>{{ $payslip->designation_name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Payroll Period</td>
            <td>{{ $run->month_name }} {{ $run->year }}</td>
            <td class="label">Generated Date</td>
            <td>{{ $payslip->generated_at?->format('d M Y') ?? '-' }}</td>
        </tr>
    </table>

    <table class="breakdown-table">
        <thead>
            <tr>
                <th style="width:50%">Earnings</th>
                <th class="amount">Amount ({{ $school?->currency ?? 'INR' }})</th>
                <th style="width:50%">Deductions</th>
                <th class="amount">Amount ({{ $school?->currency ?? 'INR' }})</th>
            </tr>
        </thead>
        <tbody>
            @php
                $earningKeys = array_keys($earnings);
                $deductionKeys = array_keys($deductions);
                $maxRows = max(count($earningKeys), count($deductionKeys), 1);
            @endphp
            @for ($i = 0; $i < $maxRows; $i++)
                <tr>
                    <td>{{ $earningKeys[$i] ?? '' }}</td>
                    <td class="amount">{{ isset($earningKeys[$i]) ? number_format($earnings[$earningKeys[$i]], 2) : '' }}</td>
                    <td>{{ $deductionKeys[$i] ?? '' }}</td>
                    <td class="amount">{{ isset($deductionKeys[$i]) ? number_format($deductions[$deductionKeys[$i]], 2) : '' }}</td>
                </tr>
            @endfor
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td class="label">Gross Salary</td>
            <td class="amount">{{ number_format($payslip->gross_salary, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Total Deductions</td>
            <td class="amount">{{ number_format($payslip->total_deductions, 2) }}</td>
        </tr>
        <tr>
            <td class="label net-salary">Net Salary</td>
            <td class="amount net-salary">{{ number_format($payslip->net_salary, 2) }}</td>
        </tr>
    </table>

    <div class="footer">
        This is a computer-generated payslip. Generated on {{ $payslip->generated_at?->format('d M Y H:i') ?? '-' }}
    </div>
</body>
</html>
