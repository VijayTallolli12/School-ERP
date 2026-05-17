@extends('layouts.admin')

@section("title", "Parent Student Mapping Report")
@section("page-title", "Parent Student Mapping Report")

@section("content")
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Filter Report</h5>
            <a href="{{ route('reports.parents.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to Dashboard</a>
        </div>
        <div class="card-body">
            <form id="filter-form" class="row">
                <div class="col-md-4 mb-3">
                    <label>Parent Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        @foreach($parentStatuses as $status)
                            <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Class/Section</label>
                    <select name="class_section_id" class="form-select">
                        <option value="">All Classes/Sections</option>
                        @foreach($classSections as $section)
                            <option value="{{ $section->id }}">{{ $section->schoolClass->name }} - {{ $section->section->name }}</option>
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
                            <th>#</th>
                            <th>Parent Name</th>
                            <th>Parent Email</th>
                            <th>Student Name</th>
                            <th>Admission No</th>
                            <th>Class/Section</th>
                            <th>Relationship</th>
                            <th>Primary</th>
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
            url: "{{ route('reports.parents.mapping') }}",
            data: function (d) {
                d.status = $('select[name="status"]').val();
                d.class_section_id = $('select[name="class_section_id"]').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'parent_name', name: 'parent_name' },
            { data: 'parent_email', name: 'parent_email' },
            { data: 'student_name', name: 'student_name' },
            { data: 'admission_no', name: 'admission_no' },
            { data: 'class_section', name: 'class_section' },
            { data: 'relationship', name: 'relationship' },
            {
                data: 'is_primary',
                name: 'is_primary',
                render: function(data) {
                    return data ? 'Yes' : 'No';
                }
            }
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
            url = "{{ route('reports.parents.export.pdf', ['type' => 'mapping']) }}";
        } else if (type === 'excel') {
            url = "{{ route('reports.parents.export.excel', ['type' => 'mapping']) }}";
        } else if (type === 'print') {
            url = "{{ route('reports.parents.print', ['type' => 'mapping']) }}";
            window.open(url + '?' + $('#filter-form').serialize(), '_blank');
            return;
        }
        window.location.href = url + '?' + $('#filter-form').serialize();
    });
});
</script>
@endpush
