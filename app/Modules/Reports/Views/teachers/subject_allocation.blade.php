@extends('layouts.admin')

@section("title", "Subject Allocation Report")
@section("page-title", "Subject Allocation Report")

@section("content")
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Filter Report</h5>
            <div>
                <a href="{{ route('reports.teachers.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to Dashboard</a>
            </div>
        </div>
        <div class="card-body">
            <form id="filter-form" class="row">
                <div class="col-md-4 mb-3">
                    <label>Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Subject</label>
                    <select name="subject_id" class="form-select">
                        <option value="">All Subjects</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Report Results</h5>
            <div>
                <a href="#" class="btn btn-sm btn-danger export-btn" data-type="pdf"><i class="fa fa-file-pdf"></i> PDF</a>
                <a href="#" class="btn btn-sm btn-success export-btn" data-type="excel"><i class="fa fa-file-excel"></i> Excel</a>
                <a href="#" class="btn btn-sm btn-info export-btn" data-type="print" target="_blank"><i class="fa fa-print"></i> Print</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="report-table">
                    <thead>
                        <tr>
                            <th>Teacher Name</th>
                            <th>Employee ID</th>
                            <th>Allocated Subjects</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push("scripts")
<script>
$(document).ready(function() {
    let table = $('#report-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('reports.teachers.subject_allocation') }}",
            data: function (d) {
                d.status = $('select[name="status"]').val();
                d.subject_id = $('select[name="subject_id"]').val();
            }
        },
        columns: [
            { 
                data: null, 
                render: function(data) {
                    return (data.first_name || '') + ' ' + (data.last_name || '');
                }
            },
            { data: 'employee_id', name: 'employee_id' },
            { 
                data: 'subjects', 
                name: 'subjects',
                render: function(data) {
                    if (!data || data.length === 0) return 'None';
                    return data.map(function(s) { return s.name; }).join(', ');
                }
            }
        ]
    });

    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        table.draw();
    });

    $('.export-btn').on('click', function(e) {
        e.preventDefault();
        let type = $(this).data('type');
        let url = "";
        if (type === 'pdf') {
            url = "{{ route('reports.teachers.export.pdf', ['type' => 'subject_allocation']) }}";
        } else if (type === 'excel') {
            url = "{{ route('reports.teachers.export.excel', ['type' => 'subject_allocation']) }}";
        } else if (type === 'print') {
            url = "{{ route('reports.teachers.print', ['type' => 'subject_allocation']) }}";
            let params = $('#filter-form').serialize();
            window.open(url + '?' + params, '_blank');
            return;
        }
        let params = $('#filter-form').serialize();
        window.location.href = url + '?' + params;
    });
});
</script>
@endpush