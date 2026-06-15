@extends('layouts.admin')

@section("title", "Parent Activity Summary")
@section("page-title", "Parent Activity Summary")

@section("content")
    <div class="mb-3">
        <a href="{{ route('reports.parents.index') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i> Back to Parent Reports</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form id="filterForm" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All</option>
                        @foreach($parentStatuses as $st)
                            <option value="{{ $st }}">{{ ucfirst($st) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="from_date" class="form-label">From Date</label>
                    <input type="date" name="from_date" id="from_date" class="form-control">
                </div>
                <div class="col-md-2">
                    <label for="to_date" class="form-label">To Date</label>
                    <input type="date" name="to_date" id="to_date" class="form-control">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="button" id="filterBtn" class="btn btn-primary"><i class="ti ti-filter me-1"></i> Filter</button>
                    <button type="button" id="resetBtn" class="btn btn-outline-secondary"><i class="ti ti-refresh"></i> Reset</button>
                </div>
            </form>
            <div class="mt-3">
                <a id="exportExcel" href="#" class="btn btn-success me-2"><i class="ti ti-file-type-xls me-1"></i> Export Excel</a>
                <a id="exportPdf" href="#" class="btn btn-danger me-2"><i class="ti ti-file-type-pdf me-1"></i> Export PDF</a>
                <a id="exportPrint" href="#" class="btn btn-warning" target="_blank"><i class="ti ti-printer me-1"></i> Print</a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="ti ti-activity text-primary me-2"></i>Parent Activity Summary</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="activityTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Parent Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th class="text-center">Linked Students</th>
                            <th class="text-center">Notifications</th>
                            <th class="text-center">Attendance Access</th>
                            <th class="text-center">Fees Access</th>
                            <th class="text-center">Exam Access</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(async function() {
        const DataTable = await window.lazyDT();
        var table = $("#activityTable").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('reports.parents.activity_summary') }}",
                data: function(d) {
                    d.status = $('#status').val();
                    d.from_date = $('#from_date').val();
                    d.to_date = $('#to_date').val();
                }
            },
            columns: [
                {data: "DT_RowIndex", name: "DT_RowIndex", orderable: false, searchable: false},
                {data: "parent_name", name: "parent_name"},
                {data: "email", name: "email"},
                {data: "phone", name: "phone"},
                {data: "status", name: "status", className: "text-center", render: function(d) {
                    return '<span class="badge bg-' + (d === 'active' ? 'success' : 'secondary') + '">' + d + '</span>';
                }},
                {data: "linked_students", name: "linked_students", className: "text-center"},
                {data: "notifications_count", name: "notifications_count", className: "text-center"},
                {data: "attendance_access", name: "attendance_access", className: "text-center", render: function(d) {
                    return d > 0 ? '<span class="badge bg-success">' + d + '</span>' : '<span class="badge bg-secondary">0</span>';
                }},
                {data: "fees_access", name: "fees_access", className: "text-center", render: function(d) {
                    return d > 0 ? '<span class="badge bg-success">' + d + '</span>' : '<span class="badge bg-secondary">0</span>';
                }},
                {data: "exam_access", name: "exam_access", className: "text-center", render: function(d) {
                    return d > 0 ? '<span class="badge bg-success">' + d + '</span>' : '<span class="badge bg-secondary">0</span>';
                }},
            ],
            order: [[1, 'asc']],
            pageLength: 25,
        });

        function updateExportLinks() {
            var qs = $.param({ status: $('#status').val(), from_date: $('#from_date').val(), to_date: $('#to_date').val() });
            $('#exportExcel').attr('href', "{{ route('reports.parents.export.excel', ['type' => 'activity_summary']) }}" + (qs ? '?' + qs : ''));
            $('#exportPdf').attr('href', "{{ route('reports.parents.export.pdf', ['type' => 'activity_summary']) }}" + (qs ? '?' + qs : ''));
            $('#exportPrint').attr('href', "{{ route('reports.parents.print', ['type' => 'activity_summary']) }}" + (qs ? '?' + qs : ''));
        }

        $('#filterBtn').on('click', function() { table.ajax.reload(); updateExportLinks(); });
        $('#resetBtn').on('click', function() {
            $('#filterForm')[0].reset();
            table.ajax.reload();
            updateExportLinks();
        });

        updateExportLinks();
    });
</script>
@endpush
