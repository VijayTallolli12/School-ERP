@extends('layouts.admin')

@section("title", "Parent List Report")
@section("page-title", "Parent List Report")

@section("content")
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Filter Report</h5>
            <a href="{{ route('reports.parents.index') }}" class="btn btn-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i> Back to Dashboard</a>
        </div>
        <div class="card-body">
            <form id="filter-form" class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        @foreach($parentStatuses as $status)
                            <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Occupation</label>
                    <input type="text" name="occupation" class="form-control" placeholder="Search occupation">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Class/Section</label>
                    <select name="class_section_id" class="form-select">
                        <option value="">All Classes/Sections</option>
                        @foreach($classSections as $section)
                            <option value="{{ $section->id }}">{{ $section->schoolClass->name }} - {{ $section->section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="ti ti-filter me-1"></i> Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Report Results</h5>
            <div>
                <a href="#" class="btn btn-sm btn-danger export-btn" data-type="pdf"><i class="ti ti-file-type-pdf me-1"></i> PDF</a>
                <a href="#" class="btn btn-sm btn-success export-btn" data-type="excel"><i class="ti ti-file-spreadsheet me-1"></i> Excel</a>
                <a href="#" class="btn btn-sm btn-info export-btn" data-type="print" target="_blank"><i class="ti ti-printer me-1"></i> Print</a>
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
                            <th>Occupation</th>
                            <th>Status</th>
                            <th>Linked Students</th>
                            <th>Classes</th>
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
$(async function() {
    const DataTable = await window.lazyDT();
    let table = $('#report-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('reports.parents.list') }}",
            data: function (d) {
                d.status = $('select[name="status"]').val();
                d.occupation = $('input[name="occupation"]').val();
                d.class_section_id = $('select[name="class_section_id"]').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'parent_name', name: 'parent_name' },
            { data: 'email', name: 'email' },
            { data: 'phone', name: 'phone', defaultContent: '-' },
            { data: 'occupation', name: 'occupation', defaultContent: '-' },
            { data: 'status', name: 'status' },
            { data: 'linked_students', name: 'linked_students' },
            { data: 'classes', name: 'classes', defaultContent: '-' }
        ]
    });

    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        table.draw();
    });

    $('#filter-form select').on('change', function() {
        table.draw();
    });

    $('.export-btn').on('click', function(e) {
        e.preventDefault();
        let url = "";
        let type = $(this).data('type');
        if (type === 'pdf') {
            url = "{{ route('reports.parents.export.pdf', ['type' => 'list']) }}";
        } else if (type === 'excel') {
            url = "{{ route('reports.parents.export.excel', ['type' => 'list']) }}";
        } else if (type === 'print') {
            url = "{{ route('reports.parents.print', ['type' => 'list']) }}";
            window.open(url + '?' + $('#filter-form').serialize(), '_blank');
            return;
        }
        window.location.href = url + '?' + $('#filter-form').serialize();
    });
});
</script>
@endpush
