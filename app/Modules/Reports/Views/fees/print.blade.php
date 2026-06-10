@extends('layouts.admin')

@section("title", $title)
@section("page-title", $title)

@push('styles')
<style>
    @media print {
        body { font-size: 12pt; }
        .no-print { display: none !important; }
        .table-bordered th, .table-bordered td { border: 1px solid #000 !important; }
    }
</style>
@endpush

@section("content")
    <div class="text-end mb-3 no-print">
        <button onclick="window.print()" class="btn btn-primary">Print Document</button>
        <button onclick="window.close()" class="btn btn-secondary">Close</button>
    </div>

    @if(empty($data))
        <p class="text-center">No data available for the selected criteria.</p>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
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
                                <td class="text-danger fw-bold">{{ number_format($row['balance'], 2) }}</td>
                                <td>{{ $row['due_date'] }}</td>
                                @if($type === 'pending')
                                    <td>
                                        @if($row['overdue'] === 'Yes')
                                            <span class="badge badge-danger">Yes</span>
                                        @else
                                            <span class="badge badge-success">No</span>
                                        @endif
                                    </td>
                                @endif
                            @elseif($type === 'collection_summary')
                                <td>{{ $row['class_section'] }}</td>
                                <td>{{ number_format($row['total_due'], 2) }}</td>
                                <td>{{ number_format($row['total_paid'], 2) }}</td>
                                <td class="{{ $row['balance'] > 0 ? 'text-danger' : 'text-success' }} fw-bold">
                                    {{ number_format($row['balance'], 2) }}
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        window.print();
    });
</script>
@endpush
