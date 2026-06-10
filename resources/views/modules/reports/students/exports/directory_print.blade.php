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
                        <div class="col-3"><strong>Total Students:</strong> {{ count($rows) }}</div>
                        <div class="col-3"><strong>Active:</strong> {{ count(array_filter($rows, fn($r) => $r['status'] === 'Active')) }}</div>
                        <div class="col-3"><strong>Inactive:</strong> {{ count(array_filter($rows, fn($r) => $r['status'] === 'Inactive')) }}</div>
                        <div class="col-3"><strong>Generated:</strong> {{ date('Y-m-d H:i:s') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(empty($rows))
        <p class="text-center text-muted">No students found for the selected criteria.</p>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-sm table-striped">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Admission No</th>
                        <th>Student Name</th>
                        <th>Class & Section</th>
                        <th>Gender</th>
                        <th>Date of Birth</th>
                        <th>Parent Name</th>
                        <th>Parent Mobile</th>
                        <th>Email</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $i => $row)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $row['admission_no'] }}</td>
                            <td>{{ $row['student_name'] }}</td>
                            <td>{{ $row['class_section'] }}</td>
                            <td>{{ $row['gender'] }}</td>
                            <td>{{ $row['date_of_birth'] }}</td>
                            <td>{{ $row['parent_name'] }}</td>
                            <td>{{ $row['parent_mobile'] }}</td>
                            <td>{{ $row['email'] }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $row['status'] == 'Active' ? 'success' : 'danger' }}">{{ $row['status'] }}</span>
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
