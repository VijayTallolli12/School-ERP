@extends('layouts.parent')

@section('title', 'Fees')
@section('page-title', 'Student Fees')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.parent-portal.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Fees</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="ti ti-wallet text-primary me-2"></i>Student Fees</h5>
        </div>
        <div class="card-body">
            @foreach($students as $student)
                <div class="mb-4">
                    <h6>{{ $student->full_name }} ({{ $student->admission_no }})</h6>
                    
                    @php
                        $studentFees = \App\Modules\Fees\Models\StudentFee::with(['items.feeCategory', 'feeStructure'])
                            ->where('student_id', $student->id)
                            ->where('academic_year_id', app(\App\Core\Tenant\SchoolContext::class)->getAcademicYearId())
                            ->get();
                    @endphp

                    @if($studentFees->isEmpty())
                        <p class="text-muted">No fee records found for this academic year.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Fee Structure</th>
                                        <th>Category</th>
                                        <th>Amount</th>
                                        <th>Paid</th>
                                        <th>Balance</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($studentFees as $fee)
                                        @foreach($fee->items as $item)
                                            <tr>
                                                <td>{{ $fee->feeStructure->name }}</td>
                                                <td>{{ $item->feeCategory->name }}</td>
                                                <td>${{ number_format($item->amount, 2) }}</td>
                                                <td>${{ number_format($item->paid_amount, 2) }}</td>
                                                <td>${{ number_format($item->balance, 2) }}</td>
                                                <td>{{ $item->due_date ? $item->due_date->format('M d, Y') : '-' }}</td>
                                                <td>
                                                    @if($item->balance <= 0)
                                                        <span class="badge bg-success">Paid</span>
                                                    @elseif($item->is_overdue)
                                                        <span class="badge bg-danger">Overdue</span>
                                                    @else
                                                        <span class="badge bg-warning">Pending</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endsection
