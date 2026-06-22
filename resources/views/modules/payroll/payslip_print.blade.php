@extends('layouts.admin')

@section('title', 'Payslip - ' . $payslip->payslip_number)
@section('page-title', 'Payslip: ' . $payslip->payslip_number)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.payroll.index') }}">Payroll</a></li>
    <li class="breadcrumb-item active">Payslip</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body" id="payslip-print-area">
            <div class="text-center mb-4 border-bottom pb-3">
                @if ($school && $school->logo_path)
                    <img src="{{ asset('storage/' . $school->logo_path) }}" alt="Logo" style="max-height: 60px;" class="mb-2">
                @endif
                <h3 class="text-primary mb-0">{{ $school?->name ?? 'School' }}</h3>
                <p class="text-muted mb-0">Salary Payslip for {{ $run->month_name }} {{ $run->year }}</p>
                <small class="text-primary fw-bold">Payslip #{{ $payslip->payslip_number }}</small>
            </div>

            <table class="table table-bordered table-sm mb-4">
                <tr>
                    <td class="bg-light fw-bold" style="width:18%">Employee Name</td>
                    <td style="width:32%">{{ $payslip->employee_name }}</td>
                    <td class="bg-light fw-bold" style="width:18%">Employee ID</td>
                    <td style="width:32%">{{ $payslip->employee_id }}</td>
                </tr>
                <tr>
                    <td class="bg-light fw-bold">Department</td>
                    <td>{{ $payslip->department_name ?? '-' }}</td>
                    <td class="bg-light fw-bold">Designation</td>
                    <td>{{ $payslip->designation_name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="bg-light fw-bold">Payroll Period</td>
                    <td>{{ $run->month_name }} {{ $run->year }}</td>
                    <td class="bg-light fw-bold">Generated Date</td>
                    <td>{{ $payslip->generated_at?->format('d M Y') ?? '-' }}</td>
                </tr>
            </table>

            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="bg-primary text-white p-2 rounded">Earnings</h6>
                    <table class="table table-sm table-bordered mb-0">
                        @forelse ($earnings as $name => $amount)
                            <tr>
                                <td>{{ $name }}</td>
                                <td class="text-end">{{ number_format($amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-muted">No earnings</td></tr>
                        @endforelse
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="bg-danger text-white p-2 rounded">Deductions</h6>
                    <table class="table table-sm table-bordered mb-0">
                        @forelse ($deductions as $name => $amount)
                            <tr>
                                <td>{{ $name }}</td>
                                <td class="text-end">{{ number_format($amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-muted">No deductions</td></tr>
                        @endforelse
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 offset-md-6">
                    <table class="table table-sm table-bordered">
                        <tr>
                            <td class="fw-bold">Gross Salary</td>
                            <td class="text-end">{{ number_format($payslip->gross_salary, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Total Deductions</td>
                            <td class="text-end">{{ number_format($payslip->total_deductions, 2) }}</td>
                        </tr>
                        <tr class="table-primary">
                            <td class="fw-bold text-primary">Net Salary</td>
                            <td class="text-end fw-bold text-primary fs-5">{{ number_format($payslip->net_salary, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="text-center text-muted small mt-4 pt-2 border-top">
                This is a computer-generated payslip. Generated on {{ $payslip->generated_at?->format('d M Y H:i') ?? '-' }}
            </div>
        </div>
        <div class="card-footer text-center">
            <button class="btn btn-primary" onclick="window.print()"><i class="ti ti-printer me-1"></i> Print</button>
            <a class="btn btn-outline-secondary" href="{{ route('admin.payroll.payslips.pdf', $payslip->id) }}"><i class="ti ti-file-pdf me-1"></i> Download PDF</a>
            <a class="btn btn-light" href="{{ route('admin.payroll.index') }}">Back</a>
        </div>
    </div>
@endsection

@push('scripts')
    <script>window.print();</script>
@endpush
