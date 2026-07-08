@extends('layouts.admin')

@section("title", "Absent Students Report - Print")
@section("page-title", "Absent Students Report")

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

    @if(!empty($summary))
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-body">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-2"><strong>Total:</strong> {{ $summary['total'] ?? 0 }}</div>
                        <div class="col-2"><strong>Present:</strong> {{ $summary['present'] ?? 0 }}</div>
                        <div class="col-2"><strong>Absent:</strong> {{ $summary['absent'] ?? 0 }}</div>
                        <div class="col-2"><strong>Late:</strong> {{ $summary['late'] ?? 0 }}</div>
                        <div class="col-2"><strong>Leave:</strong> {{ $summary['leave'] ?? 0 }}</div>
                        <div class="col-2"><strong>%:</strong> {{ $summary['percentage'] ?? 0 }}%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @php $rows = $data ?? []; @endphp
    @if(empty($rows))
        <p class="text-center text-muted">No records found for the selected criteria.</p>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-sm table-striped">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th>Admission No</th>
                        <th>Class & Section</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th class="text-center">Consecutive Days</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $i => $row)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $row['student_name'] }}</td>
                            <td>{{ $row['admission_no'] }}</td>
                            <td>{{ $row['class_section'] }}</td>
                            <td>{{ $row['attendance_date'] }}</td>
                            <td>
                                @php
                                    $badge = match($row['status']) {
                                        'absent' => 'bg-danger',
                                        'late' => 'bg-warning text-dark',
                                        'leave' => 'bg-info',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badge }}">{{ ucfirst($row['status']) }}</span>
                            </td>
                            <td class="text-center">{{ $row['consecutive_days'] }}</td>
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
