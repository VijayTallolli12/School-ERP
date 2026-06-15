@extends('layouts.admin')

@section("title", "Teacher List Report")
@section("page-title", "Teacher List Report")

@section("content")
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Filter Report</h5>
            <div>
                <a href="{{ route('reports.teachers.index') }}" class="btn btn-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i> Back to Dashboard</a>
            </div>
        </div>
        <div class="card-body">
            <form id="filter-form" class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="probation">Probation</option>
                        <option value="retired">Retired</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Subject</label>
                    <select name="subject_id" class="form-select">
                        <option value="">All Subjects</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
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
                <div class="col-md-3 mb-3">
                    <label class="form-label">Joining Date From</label>
                    <input type="date" name="joining_date_from" class="form-control">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Joining Date To</label>
                    <input type="date" name="joining_date_to" class="form-control">
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
                            <th>ID</th>
                            <th>Name</th>
                            <th>Employee ID</th>
                            <th>Status</th>
                            <th>Joining Date</th>
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
            url: "{{ route('reports.teachers.list') }}",
            data: function (d) {
                d.status = $('select[name="status"]').val();
                d.subject_id = $('select[name="subject_id"]').val();
                d.class_section_id = $('select[name="class_section_id"]').val();
                d.joining_date_from = $('input[name="joining_date_from"]').val();
                d.joining_date_to = $('input[name="joining_date_to"]').val();
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { 
                data: null, 
                render: function(data) {
                    return (data.first_name || '') + ' ' + (data.last_name || '');
                }
            },
            { data: 'employee_id', name: 'employee_id' },
            { data: 'status', name: 'status' },
            { data: 'joining_date', name: 'joining_date' }
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
            url = "{{ route('reports.teachers.export.pdf', ['type' => 'list']) }}";
        } else if (type === 'excel') {
            url = "{{ route('reports.teachers.export.excel', ['type' => 'list']) }}";
        } else if (type === 'print') {
            url = "{{ route('reports.teachers.print', ['type' => 'list']) }}";
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