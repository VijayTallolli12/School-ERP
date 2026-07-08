@extends('layouts.admin')

@section("title", "Teacher Workload Report")
@section("page-title", "Teacher Workload Report")

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
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="probation">Probation</option>
                        <option value="retired">Retired</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Subject</label>
                    <select name="subject_id" class="form-select">
                        <option value="">All Subjects</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3 d-flex align-items-end">
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
                            <th>Teacher Name</th>
                            <th>Employee ID</th>
                            <th>Status</th>
                            <th>Subjects Taught</th>
                            <th>Class Sections</th>
                            <th>Total Periods/Week</th>
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
            url: "{{ route('reports.teachers.workload') }}",
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
            { data: 'status', name: 'status' },
            { 
                data: 'subjects', 
                name: 'subjects',
                render: function(data) {
                    if (!data || data.length === 0) return 'None';
                    return data.map(function(s) { return s.name; }).join(', ');
                }
            },
            { 
                data: 'class_sections', 
                name: 'class_sections',
                render: function(data) {
                    if (!data || data.length === 0) return 'None';
                    return data.length + ' sections';
                }
            },
            { 
                data: 'total_periods', 
                name: 'total_periods',
                render: function(data) {
                    return data || 0;
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
            url = "{{ route('reports.teachers.workload.export', ['type' => 'pdf']) }}";
        } else if (type === 'excel') {
            url = "{{ route('reports.teachers.workload.export', ['type' => 'excel']) }}";
        } else if (type === 'print') {
            url = "{{ route('reports.teachers.workload.export', ['type' => 'print']) }}";
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
