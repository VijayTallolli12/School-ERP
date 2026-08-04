@extends('layouts.admin')

@section('title', 'Student Lifecycle')
@section('page-title', 'Student Lifecycle Management')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Lifecycle</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center gap-2">
            <h3 class="card-title mb-0"><i class="ti ti-arrows-left-right text-primary me-2"></i>Promotions, Transfers & TC</h3>
            <div class="ms-auto d-flex flex-wrap gap-2">
                <select id="filterType" class="form-select form-select-sm" style="width: 200px;">
                    <option value="">All Types</option>
                    @foreach ($types as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                @can('student_lifecycle.promote')
                    <a href="{{ route('admin.lifecycle.promotions') }}" class="btn btn-primary btn-sm">
                        <i class="ti ti-arrow-up-circle me-1"></i> Bulk Promotion
                    </a>
                @endcan
                @can('student_lifecycle.transfer')
                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#transferModal">
                        <i class="ti ti-logout me-1"></i> Transfer Student
                    </button>
                @endcan
                @can('student_lifecycle.tc')
                    <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#tcModal">
                        <i class="ti ti-file-certificate me-1"></i> Issue TC
                    </button>
                @endcan
                @can('student_lifecycle.alumni')
                    <button class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#alumniModal">
                        <i class="ti ti-graduation-cap me-1"></i> Mark Alumni
                    </button>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered w-100" id="lifecycleTable">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Admission No</th>
                    <th>Type</th>
                    <th>From Class</th>
                    <th>To Class</th>
                    <th>Transferred On</th>
                    <th>TC No</th>
                    <th width="80">Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('modals')
    <div class="modal fade" id="transferModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Transfer Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Student</label>
                        <select class="form-select lifecycle-student-select" name="student_id" required></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Transferred On</label>
                        <input class="form-control" type="date" name="transferred_on" value="{{ now()->toDateString() }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Destination School</label>
                        <input class="form-control" name="destination_school" maxlength="255">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Reason</label>
                        <textarea class="form-control" name="reason" rows="3" maxlength="2000"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary lifecycle-submit" data-url="{{ route('admin.lifecycle.transfer') }}">Transfer</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="tcModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Issue Transfer Certificate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Student</label>
                        <select class="form-select lifecycle-student-select" name="student_id" required></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">TC No</label>
                        <input class="form-control" name="tc_no" maxlength="50" placeholder="Leave blank to auto-generate">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">Leaving Date</label>
                            <input class="form-control" type="date" name="transferred_on" value="{{ now()->toDateString() }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Issued On</label>
                            <input class="form-control" type="date" name="tc_issued_on" value="{{ now()->toDateString() }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Conduct</label>
                        <input class="form-control" name="conduct" maxlength="100" placeholder="e.g. Excellent, Good">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Destination School</label>
                        <input class="form-control" name="destination_school" maxlength="255">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Reason</label>
                        <textarea class="form-control" name="reason" rows="3" maxlength="2000"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary lifecycle-submit" data-url="{{ route('admin.lifecycle.tc') }}">Issue TC</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="alumniModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mark as Alumni</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Student</label>
                        <select class="form-select lifecycle-student-select" name="student_id" required></select>
                    </div>
                    <p class="text-muted mb-0">The student's active session will be closed and their status will be set to <strong>Alumni</strong>.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-dark lifecycle-alumni-submit">Mark Alumni</button>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => { (async () => { const DataTable = await window.lazyDT();
            const table = $('#lifecycleTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                order: [[0, 'desc']],
                ajax: {
                    url: '{{ route('admin.lifecycle.data') }}',
                    data: (d) => {
                        d.transfer_type = $('#filterType').val();
                    }
                },
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'student_name', name: 'student_name', orderable: false},
                    {data: 'admission_no', name: 'admission_no', orderable: false},
                    {data: 'transfer_type', name: 'transfer_type'},
                    {data: 'from_class', name: 'from_class', orderable: false, searchable: false},
                    {data: 'to_class', name: 'to_class', orderable: false, searchable: false},
                    {data: 'transferred_on', name: 'transferred_on'},
                    {data: 'tc_no', name: 'tc_no'},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false},
                ]
            });

            $('#filterType').on('change', () => table.ajax.reload());

            $('.lifecycle-student-select').each(function () {
                $(this).select2({
                    placeholder: 'Search student...',
                    dropdownParent: $(this).closest('.modal'),
                    minimumInputLength: 1,
                    ajax: {
                        url: '{{ route('admin.lifecycle.search-students') }}',
                        dataType: 'json',
                        delay: 250,
                        data: (params) => ({q: params.term}),
                        processResults: (data) => ({results: data.results})
                    }
                });
            });

            const collectModalData = (modal) => {
                const data = {_token: '{{ csrf_token() }}'};
                modal.find('input, select, textarea').each(function () {
                    const name = $(this).attr('name');
                    if (name) data[name] = $(this).val();
                });
                return data;
            };

            $('.lifecycle-submit').on('click', function () {
                const btn = $(this).prop('disabled', true);
                const modal = $(this).closest('.modal');
                const data = collectModalData(modal);

                $.ajax({
                    url: $(this).data('url'),
                    method: 'POST',
                    data,
                    success: (res) => {
                        modal.modal('hide');
                        if (res.success) {
                            alert(res.message);
                            table.ajax.reload(null, false);
                        } else {
                            alert(res.message || 'Action failed.');
                        }
                    },
                    error: (xhr) => {
                        const res = xhr.responseJSON || {};
                        alert(res.message || 'Something went wrong.');
                    },
                    complete: () => btn.prop('disabled', false)
                });
            });

            $('.lifecycle-alumni-submit').on('click', function () {
                const btn = $(this).prop('disabled', true);
                const modal = $(this).closest('.modal');
                const studentId = modal.find('select[name="student_id"]').val();

                if (!studentId) {
                    alert('Please select a student.');
                    btn.prop('disabled', false);
                    return;
                }

                if (!confirm('Mark this student as alumni? This cannot be easily undone.')) {
                    btn.prop('disabled', false);
                    return;
                }

                $.ajax({
                    url: '{{ route('admin.students.alumni', '__ID__') }}'.replace('__ID__', studentId),
                    method: 'POST',
                    data: {_token: '{{ csrf_token() }}'},
                    success: (res) => {
                        modal.modal('hide');
                        alert(res.message || 'Done.');
                        table.ajax.reload(null, false);
                    },
                    error: (xhr) => {
                        const res = xhr.responseJSON || {};
                        alert(res.message || 'Something went wrong.');
                    },
                    complete: () => btn.prop('disabled', false)
                });
            });
        })(); });
    </script>
@endpush
