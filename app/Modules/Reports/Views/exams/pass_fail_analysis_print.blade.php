@extends('layouts.admin')

@section("title", $title)
@section("page-title", $title)

@push('styles')
<style>
    @media print {
        body { font-size: 12pt; }
        .no-print { display: none !important; }
        .table-bordered th, .table-bordered td { border: 1px solid #000 !important; }
        .page-break { page-break-before: always; }
    }
</style>
@endpush

@section("content")
    <div class="text-end mb-3 no-print">
        <button onclick="window.print()" class="btn btn-primary">Print Document</button>
        <button onclick="window.close()" class="btn btn-secondary">Close</button>
    </div>

    @php $o = $data['overall']; @endphp
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-body">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-3">
                            <strong>Appeared:</strong> {{ $o['total_appeared'] }}<br>
                            <strong>Avg Class Pass %:</strong> {{ $data['avgPctOverall'] }}%
                        </div>
                        <div class="col-3">
                            <strong>Passed:</strong> {{ $o['total_passed'] }} ({{ $o['pass_percentage'] }}%)
                        </div>
                        <div class="col-3">
                            <strong>Failed:</strong> {{ $o['total_failed'] }} ({{ $o['fail_percentage'] }}%)
                        </div>
                        <div class="col-3">
                            <strong>Best Class:</strong> {{ $data['bestClass'] ?: '--' }}<br>
                            <strong>Lowest:</strong> {{ $data['lowestClass'] ?: '--' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Class Performance --}}
    @php $cp = $data['classPerformance'] ?? []; @endphp
    @if(!empty($cp))
        <h5 class="mb-2">Class-wise Performance</h5>
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th>Class & Section</th>
                        <th class="text-center">Appeared</th>
                        <th class="text-center">Passed</th>
                        <th class="text-center">Failed</th>
                        <th class="text-center">Pass %</th>
                        <th class="text-center">Avg Marks</th>
                        <th class="text-center">Avg %</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cp as $row)
                        <tr>
                            <td>{{ $row['class_section'] }}</td>
                            <td class="text-center">{{ $row['appeared'] }}</td>
                            <td class="text-center text-success fw-medium">{{ $row['passed'] }}</td>
                            <td class="text-center text-danger fw-medium">{{ $row['failed'] }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $row['pass_pct'] >= 80 ? 'success' : ($row['pass_pct'] >= 50 ? 'warning' : 'danger') }}">
                                    {{ $row['pass_pct'] }}%
                                </span>
                            </td>
                            <td class="text-center">{{ $row['avg_marks'] }}</td>
                            <td class="text-center">{{ $row['avg_percentage'] }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Subject Analysis --}}
    @php $sa = $data['subjectAnalysis'] ?? []; @endphp
    @if(!empty($sa))
        <h5 class="mb-2">Subject-wise Analysis</h5>
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th>Subject</th>
                        <th class="text-center">Appeared</th>
                        <th class="text-center">Passed</th>
                        <th class="text-center">Failed</th>
                        <th class="text-center">Pass %</th>
                        <th class="text-center">Fail %</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sa as $row)
                        <tr>
                            <td>{{ $row['subject'] }}</td>
                            <td class="text-center">{{ $row['appeared'] }}</td>
                            <td class="text-center text-success fw-medium">{{ $row['passed'] }}</td>
                            <td class="text-center text-danger fw-medium">{{ $row['failed'] }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $row['pass_pct'] >= 80 ? 'success' : ($row['pass_pct'] >= 50 ? 'warning' : 'danger') }}">
                                    {{ $row['pass_pct'] }}%
                                </span>
                            </td>
                            <td class="text-center">{{ $row['fail_pct'] }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Student Breakdown --}}
    @php $sa2 = $data['studentAnalysis'] ?? []; @endphp
    @if(!empty($sa2))
        <h5 class="mb-2">Student-wise Breakdown</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-sm table-striped">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th>Admission No</th>
                        <th>Class & Section</th>
                        <th>Exam</th>
                        <th class="text-center">Percentage</th>
                        <th class="text-center">Result</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sa2 as $i => $row)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $row['student_name'] }}</td>
                            <td>{{ $row['admission_no'] }}</td>
                            <td>{{ $row['class_section'] }}</td>
                            <td>{{ $row['exam_name'] }}</td>
                            <td class="text-center">{{ $row['percentage'] }}%</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $row['result'] == 'Pass' ? 'success' : 'danger' }}">
                                    {{ $row['result'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if(empty($cp) && empty($sa) && empty($sa2))
        <p class="text-center text-muted">No data available for the selected criteria.</p>
    @endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        window.print();
    });
</script>
@endpush
