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

    @php $summary = $data['summary'] ?? []; @endphp
    @if(!empty($summary) && ($summary['students_evaluated'] ?? 0) > 0)
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card bg-body">
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-3">
                                <strong>Highest %:</strong> {{ $summary['highest_percentage'] }}%
                            </div>
                            <div class="col-3">
                                <strong>Top Student:</strong> {{ $summary['top_student'] }}
                            </div>
                            <div class="col-3">
                                <strong>Class Average:</strong> {{ $summary['class_average'] }}%
                            </div>
                            <div class="col-3">
                                <strong>Students:</strong> {{ $summary['students_evaluated'] }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @php $ranked = $data['ranked'] ?? []; @endphp
    @if(empty($ranked))
        <p class="text-center">No data available for the selected criteria.</p>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th class="text-center">Rank</th>
                        <th>Student Name</th>
                        <th>Admission No</th>
                        <th>Class & Section</th>
                        <th>Exam Name</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Obtained</th>
                        <th class="text-center">%</th>
                        <th class="text-center">Grade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ranked as $row)
                        @php
                            $pct = $row['percentage'];
                            $pctClass = $pct >= 80 ? 'bg-success' : ($pct >= 60 ? 'bg-warning' : ($pct >= 40 ? 'bg-info' : 'bg-danger'));
                        @endphp
                        <tr>
                            <td class="text-center fw-bold">{{ $row['rank'] }}</td>
                            <td>{{ $row['student_name'] }}</td>
                            <td>{{ $row['admission_no'] }}</td>
                            <td>{{ $row['class_section'] }}</td>
                            <td>{{ $row['exam_name'] }}</td>
                            <td class="text-center">{{ $row['total_marks'] }}</td>
                            <td class="text-center">{{ $row['obtained_marks'] }}</td>
                            <td class="text-center">
                                <span class="badge {{ $pctClass }}">{{ $pct }}%</span>
                            </td>
                            <td class="text-center">{{ $row['grade'] }}</td>
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
