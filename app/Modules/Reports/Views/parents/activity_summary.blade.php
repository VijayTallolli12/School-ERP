@extends('layouts.admin')

@section("title", "Parent Activity Summary Report")
@section("page-title", "Parent Activity Summary Report")

@section("content")
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Filter Report</h5>
            <a href="{{ route('reports.parents.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to Dashboard</a>
        </div>
        <div class="card-body">
            <form id="filter-form" class="row">
                <div class="col-md-3 mb-3">
                    <label>Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        @foreach($parentStatuses as $status)
                            <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>From Date</label>
                    <input type="date" name="from_date" class="form-control">
                </div>
                <div class="col-md-3 mb-3">
                    <label>To Date</label>
                    <input type="date" name="to_date" class="form-control">
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
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
                            <th>#</th>
                            <th>Parent Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Linked Students</th>
                            <th>Notifications</th>
                            <th>Attendance Access</th>
                            <th>Fees Access</th>
                            <th>Exam Access</th>
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
            url: "{{ route('reports.parents.activity_summary') }}",
            data: function (d) {
                d.status = $('select[name="status"]').val();
                d.from_date = $('input[name="from_date"]').val();
                d.to_date = $('input[name="to_date"]').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'parent_name', name: 'parent_name' },
            { data: 'email', name: 'email' },
            { data: 'phone', name: 'phone', defaultContent: '-' },
            { data: 'linked_students', name: 'linked_students' },
            { data: 'notifications_count', name: 'notifications_count' },
            { data: 'attendance_access', name: 'attendance_access' },
            { data: 'fees_access', name: 'fees_access' },
            { data: 'exam_access', name: 'exam_access' }
        ]
    });

    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        table.draw();
    });

    $('#filter-form select, #filter-form input').on('change', function() {
        table.draw();
    });

    $('.export-btn').on('click', function(e) {
        e.preventDefault();
        let url = "";
        let type = $(this).data('type');
        if (type === 'pdf') {
            url = "{{ route('reports.parents.export.pdf', ['type' => 'activity_summary']) }}";
        } else if (type === 'excel') {
            url = "{{ route('reports.parents.export.excel', ['type' => 'activity_summary']) }}";
        } else if (type === 'print') {
            url = "{{ route('reports.parents.print', ['type' => 'activity_summary']) }}";
            window.open(url + '?' + $('#filter-form').serialize(), '_blank');
            return;
        }
        window.location.href = url + '?' + $('#filter-form').serialize();
    });
});
</script>
@endpush
