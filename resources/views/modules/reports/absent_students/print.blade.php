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
    .badge-danger { background-color: #dc3545; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 11px; }
    .badge-warning { background-color: #ffc107; color: #000; padding: 2px 8px; border-radius: 4px; font-size: 11px; }
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
                            <div class="col">
                                <strong>Total:</strong> {{ $summary['total'] }}
                            </div>
                            <div class="col">
                                <strong>Present:</strong> {{ $summary['present'] }}
                            </div>
                            <div class="col">
                                <strong>Absent:</strong> {{ $summary['absent'] }}
                            </div>
                            <div class="col">
                                <strong>Late:</strong> {{ $summary['late'] }}
                            </div>
                            <div class="col">
                                <strong>Leave:</strong> {{ $summary['leave'] }}
                            </div>
                            <div class="col">
                                <strong>%:</strong> {{ $summary['percentage'] }}%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(empty($data))
        <p class="text-center">No absent students found for the selected period.</p>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th>Admission No</th>
                        <th>Class & Section</th>
                        <th>Parent Name</th>
                        <th>Parent Mobile</th>
                        <th>Attendance Date</th>
                        <th>Status</th>
                        <th>Consecutive Days</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row['student_name'] }}</td>
                            <td>{{ $row['admission_no'] }}</td>
                            <td>{{ $row['class_section'] }}</td>
                            <td>{{ $row['parent_name'] }}</td>
                            <td>{{ $row['parent_mobile'] }}</td>
                            <td>{{ $row['attendance_date'] }}</td>
                            <td>{{ $row['status'] }}</td>
                            <td>
                                @if($row['consecutive_days'] >= 3)
                                    <span class="badge-danger">{{ $row['consecutive_days'] }} days</span>
                                @else
                                    <span class="badge-warning">{{ $row['consecutive_days'] }} day{{ $row['consecutive_days'] > 1 ? 's' : '' }}</span>
                                @endif
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
