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
                        <div class="col-3"><strong>Total:</strong> {{ $totals['total'] }}</div>
                        <div class="col-3"><strong>Male:</strong> {{ $totals['male'] }} ({{ $totals['total'] > 0 ? round(($totals['male'] / $totals['total']) * 100, 1) : 0 }}%)</div>
                        <div class="col-3"><strong>Female:</strong> {{ $totals['female'] }} ({{ $totals['total'] > 0 ? round(($totals['female'] / $totals['total']) * 100, 1) : 0 }}%)</div>
                        <div class="col-3"><strong>Other:</strong> {{ $totals['other'] }}</div>
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
                        <th>Class</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Male</th>
                        <th class="text-center">Female</th>
                        <th class="text-center">Other</th>
                        <th class="text-center">Male %</th>
                        <th class="text-center">Female %</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        <tr>
                            <td>{{ $row['class_name'] }}</td>
                            <td class="text-center fw-bold">{{ $row['total'] }}</td>
                            <td class="text-center">{{ $row['male'] }}</td>
                            <td class="text-center">{{ $row['female'] }}</td>
                            <td class="text-center">{{ $row['other'] }}</td>
                            <td class="text-center">{{ $row['male_pct'] }}%</td>
                            <td class="text-center">{{ $row['female_pct'] }}%</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td>Total</td>
                        <td class="text-center">{{ $totals['total'] }}</td>
                        <td class="text-center">{{ $totals['male'] }}</td>
                        <td class="text-center">{{ $totals['female'] }}</td>
                        <td class="text-center">{{ $totals['other'] }}</td>
                        <td class="text-center">{{ $totals['total'] > 0 ? round(($totals['male'] / $totals['total']) * 100, 1) : 0 }}%</td>
                        <td class="text-center">{{ $totals['total'] > 0 ? round(($totals['female'] / $totals['total']) * 100, 1) : 0 }}%</td>
                    </tr>
                </tfoot>
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
