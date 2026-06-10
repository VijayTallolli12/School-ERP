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

    @if(!empty($summary))
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card bg-body">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Present:</strong> {{ $summary['present'] }}
                            </div>
                            <div class="col-md-3">
                                <strong>Absent:</strong> {{ $summary['absent'] }}
                            </div>
                            <div class="col-md-3">
                                <strong>Late:</strong> {{ $summary['late'] }}
                            </div>
                            <div class="col-md-3">
                                <strong>Leave:</strong> {{ $summary['leave'] }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(empty($data))
        <p class="text-center">No data available for the selected criteria.</p>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th class="text-center">Present</th>
                        <th class="text-center">Absent</th>
                        <th class="text-center">Late</th>
                        <th class="text-center">Leave</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $index => $row)
                        @php $pct = ($row['total'] ?? 0) > 0 ? round(($row['present'] / $row['total']) * 100, 1) : 0; @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row['student'] }}</td>
                            <td class="text-center">{{ $row['present'] }}</td>
                            <td class="text-center">{{ $row['absent'] }}</td>
                            <td class="text-center">{{ $row['late'] }}</td>
                            <td class="text-center">{{ $row['leave'] }}</td>
                            <td class="text-center">{{ $row['total'] }}</td>
                            <td class="text-center">{{ $pct }}%</td>
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
