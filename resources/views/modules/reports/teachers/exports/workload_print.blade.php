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

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-body">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-3"><strong>Total Teachers:</strong> {{ $summary['total_teachers'] ?? 0 }}</div>
                        <div class="col-3"><strong>Avg Workload:</strong> {{ $summary['avg_workload'] ?? 0 }}</div>
                        <div class="col-3"><strong>Avg Classes:</strong> {{ $summary['avg_classes'] ?? 0 }}</div>
                        <div class="col-3"><strong>Avg Subjects:</strong> {{ $summary['avg_subjects'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(empty($rows))
        <p class="text-center text-muted">No data available.</p>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-sm table-striped">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Teacher Name</th>
                        <th>Employee ID</th>
                        <th class="text-center">Subjects</th>
                        <th class="text-center">Classes</th>
                        <th class="text-center">Weekly Periods</th>
                        <th class="text-center">Workload Score</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row['teacher_name'] }}</td>
                            <td>{{ $row['employee_id'] }}</td>
                            <td class="text-center">{{ $row['assigned_subjects'] }}</td>
                            <td class="text-center">{{ $row['assigned_classes'] }}</td>
                            <td class="text-center">{{ $row['weekly_periods'] }}</td>
                            <td class="text-center fw-bold">{{ $row['workload_score'] }}</td>
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