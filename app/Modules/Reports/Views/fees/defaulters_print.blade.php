@extends('layouts.admin')

@section("title", $title)
@section("page-title", $title)

@push('styles')
<style>
    @media print {
        body { font-size: 10pt; }
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

    @php $s = $data['summary']; @endphp
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-body">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-3"><strong>Total Assigned:</strong> ₹ {{ number_format($s['total_assigned'], 2) }}</div>
                        <div class="col-3"><strong>Total Collected:</strong> ₹ {{ number_format($s['total_collected'], 2) }}</div>
                        <div class="col-3"><strong>Total Outstanding:</strong> ₹ {{ number_format($s['total_outstanding'], 2) }}</div>
                        <div class="col-3"><strong>Collection %:</strong> {{ $s['collection_percentage'] }}%</div>
                    </div>
                    <div class="row text-center mt-2">
                        <div class="col-3"><strong>Students with Dues:</strong> {{ $s['students_with_dues'] }}</div>
                        <div class="col-3"><strong>Overdue Students:</strong> {{ $s['overdue_students'] }}</div>
                        <div class="col-3"><strong>Highest Outstanding:</strong> ₹ {{ number_format($s['highest_outstanding'], 2) }}</div>
                        <div class="col-3"><strong>Avg Outstanding:</strong> ₹ {{ number_format($s['average_outstanding'], 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php $rows = $data['defaulters'] ?? []; @endphp
    @if(empty($rows))
        <p class="text-center text-muted">No defaulters found for the selected criteria.</p>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-sm table-striped">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th>Adm No</th>
                        <th>Class & Section</th>
                        <th>Parent Name</th>
                        <th>Parent Mobile</th>
                        <th>Fee Structure</th>
                        <th class="text-end">Total Fee</th>
                        <th class="text-end">Paid</th>
                        <th class="text-end">Outstanding</th>
                        <th>Due Date</th>
                        <th class="text-center">Days Overdue</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $i => $row)
                        @php
                            $odClass = $row['days_overdue'] > 60 ? 'bg-dark text-white' : ($row['days_overdue'] > 30 ? 'bg-danger text-white' : ($row['days_overdue'] > 0 ? 'bg-warning' : 'bg-success'));
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
                            <td class="text-center"><span class="badge {{ $odClass }}">{{ $row['days_overdue'] > 0 ? $row['days_overdue'] . ' days' : '--' }}</span></td>
                            <td class="text-center">
                                <span class="badge bg-{{ $row['status'] == 'Paid' ? 'success' : ($row['status'] == 'Overdue' ? 'danger' : 'warning') }}">
                                    {{ $row['status'] }}
                                </span>
                            </td>
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
