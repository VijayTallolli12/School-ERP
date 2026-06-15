@extends('layouts.admin')

@section("title", "Parent-Student Mapping Report")
@section("page-title", "Parent-Student Mapping Report")

@section("content")
    <div class="mb-3">
        <a href="{{ route('reports.parents.index') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i> Back to Parent Reports</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form id="filterForm" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All</option>
                        @foreach($parentStatuses as $st)
                            <option value="{{ $st }}">{{ ucfirst($st) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="class_section_id" class="form-label">Class & Section</label>
                    <select name="class_section_id" id="class_section_id" class="form-select">
                        <option value="">All</option>
                        @foreach($classSections as $cs)
                            <option value="{{ $cs->id }}">{{ $cs->schoolClass->name }} - {{ $cs->section->name }}</option>
                        @endforeach
                    </select>
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
            <h5 class="card-title mb-0"><i class="ti ti-link text-primary me-2"></i>Parent-Student Mappings</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="mappingTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Parent Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Student Name</th>
                            <th>Admission No</th>
                            <th>Class & Section</th>
                            <th>Relationship</th>
                            <th>Primary</th>
                            <th>Status</th>
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
        var table = $("#mappingTable").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('reports.parents.mapping') }}",
                data: function(d) {
                    d.status = $('#status').val();
                    d.class_section_id = $('#class_section_id').val();
                }
            },
            columns: [
                {data: "DT_RowIndex", name: "DT_RowIndex", orderable: false, searchable: false},
                {data: "parent_name", name: "parent_name"},
                {data: "parent_email", name: "parent_email"},
                {data: "parent_phone", name: "parent_phone"},
                {data: "student_name", name: "student_name"},
                {data: "admission_no", name: "admission_no"},
                {data: "class_section", name: "class_section"},
                {data: "relationship", name: "relationship"},
                {data: "is_primary", name: "is_primary", className: "text-center", render: function(d) {
                    return d ? '<span class="badge bg-primary">Yes</span>' : '<span class="badge bg-secondary">No</span>';
                }},
                {data: "status", name: "status", className: "text-center", render: function(d) {
                    return '<span class="badge bg-' + (d === 'active' ? 'success' : 'secondary') + '">' + d + '</span>';
                }},
            ],
            order: [[1, 'asc']],
            pageLength: 25,
        });

        function updateExportLinks() {
            var qs = $.param({ status: $('#status').val(), class_section_id: $('#class_section_id').val() });
            $('#exportExcel').attr('href', "{{ route('reports.parents.export.excel', ['type' => 'mapping']) }}" + (qs ? '?' + qs : ''));
            $('#exportPdf').attr('href', "{{ route('reports.parents.export.pdf', ['type' => 'mapping']) }}" + (qs ? '?' + qs : ''));
            $('#exportPrint').attr('href', "{{ route('reports.parents.print', ['type' => 'mapping']) }}" + (qs ? '?' + qs : ''));
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
