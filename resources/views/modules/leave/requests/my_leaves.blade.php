@extends('layouts.admin')

@section('title', 'My Leave Requests')
@section('page-title', 'My Leave Requests')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">My Leave Requests</li>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <h3 class="card-title fw-semibold mb-0">
                        <i class="ti ti-calendar-stats text-primary me-1"></i> My Leave Requests
                    </h3>
                    @can('leave_management.create')
                        <div class="d-flex align-items-center gap-3">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#leaveRequestModal" id="createLeaveRequest">
                                <i class="ti ti-plus me-1"></i> New Leave Request
                            </button>
                        </div>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="filterStatus">
                                <option value="">All Statuses</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Leave Type</label>
                            <select class="form-select" id="filterLeaveType">
                                <option value="">All Types</option>
                                @foreach ($leaveTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">From Date</label>
                            <input class="form-control" type="date" id="filterFromDate">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">To Date</label>
                            <input class="form-control" type="date" id="filterToDate">
                        </div>
                    </div>

                    <table class="table table-striped table-bordered w-100" id="leaveRequestsTable">
                        <thead>
                        <tr>
                            <th>Student</th>
                            <th>Leave Type</th>
                            <th>From Date</th>
                            <th>To Date</th>
                            <th>Days</th>
                            <th>Status</th>
                            <th width="140">Actions</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    <!-- Create Leave Request Modal -->
    <div class="modal fade" id="leaveRequestModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form class="modal-content ajax-form" id="leaveRequestForm" method="POST" action="{{ route('admin.leave-requests.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" value="POST" id="leaveRequestMethod">
                <div class="modal-header">
                    <h5 class="modal-title" id="leaveRequestModalTitle">New Leave Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Student</label>
                            <select class="form-select" name="student_id" required>
                                <option value="">Select student</option>
                                @foreach ($students as $student)
                                    <option value="{{ $student->id }}">{{ $student->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Leave Type</label>
                            <select class="form-select" name="leave_type_id" required>
                                <option value="">Select type</option>
                                @foreach ($leaveTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">From Date</label>
                            <input class="form-control" type="date" name="from_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">To Date</label>
                            <input class="form-control" type="date" name="to_date" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Reason</label>
                            <textarea class="form-control" name="reason" rows="3" maxlength="2000"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Attachment (optional)</label>
                            <input class="form-control" type="file" name="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <div class="form-text">Allowed: PDF, DOC, DOCX, JPG, PNG (max 5MB)</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary py-2"><i class="ti ti-device-floppy me-1"></i> Submit Request</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Leave Request Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detailModalBody">
                    <div class="text-center py-3">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => { (async () => { const DataTable = await window.lazyDT();
            const requestModal = new bootstrap.Modal('#leaveRequestModal');
            const detailModal = new bootstrap.Modal('#detailModal');
            const form = $('#leaveRequestForm');

            const filterStatus = $('#filterStatus');
            const filterLeaveType = $('#filterLeaveType');
            const filterFromDate = $('#filterFromDate');
            const filterToDate = $('#filterToDate');

            const table = $('#leaveRequestsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route('admin.my-leaves.data') }}',
                    data: function (d) {
                        d.status = filterStatus.val();
                        d.leave_type_id = filterLeaveType.val();
                        d.from_date = filterFromDate.val();
                        d.to_date = filterToDate.val();
                    },
                },
                columns: [
                    {data: 'student_name', name: 'student_name'},
                    {data: 'leave_type', name: 'leave_type', orderable: false},
                    {data: 'from_date', name: 'from_date'},
                    {data: 'to_date', name: 'to_date'},
                    {data: 'days', name: 'days'},
                    {data: 'status_badge', name: 'status', orderable: false, searchable: false},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false},
                ],
                order: [[2, 'desc']],
            });

            filterStatus.on('change', () => table.ajax.reload());
            filterLeaveType.on('change', () => table.ajax.reload());
            filterFromDate.on('change', () => table.ajax.reload());
            filterToDate.on('change', () => table.ajax.reload());

            $('#createLeaveRequest').on('click', () => {
                form[0].reset();
                $('#leaveRequestMethod').val('POST');
                form.attr('action', '{{ route('admin.leave-requests.store') }}');
                $('#leaveRequestModalTitle').text('New Leave Request');
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback.dynamic').remove();
            });

            $('#leaveRequestsTable').on('click', '.view-leave-request', function () {
                const url = $(this).data('url');

                $.get(url, (response) => {
                    const d = response.data;
                    const badgeClass = d.status_badge;
                    const attachmentHtml = d.attachment_url
                        ? `<a href="${d.attachment_url}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="ti ti-download me-1"></i> View Attachment</a>`
                        : '<span class="text-muted">None</span>';

                    $('#detailModalBody').html(`
                        <div class="mb-3">
                            <span class="badge ${badgeClass} fs-6 mb-2">${d.status_label}</span>
                        </div>
                        <table class="table table-bordered mb-0">
                            <tr><th style="width:140px">Student</th><td>${d.student_name}</td></tr>
                            <tr><th>Leave Type</th><td>${d.leave_type}</td></tr>
                            <tr><th>From Date</th><td>${d.from_date}</td></tr>
                            <tr><th>To Date</th><td>${d.to_date}</td></tr>
                            <tr><th>Days</th><td>${d.days}</td></tr>
                            <tr><th>Reason</th><td>${d.reason || '-'}</td></tr>
                            <tr><th>Attachment</th><td>${attachmentHtml}</td></tr>
                            <tr><th>Submitted By</th><td>${d.submitted_by}</td></tr>
                            <tr><th>Submitted At</th><td>${d.created_at}</td></tr>
                            ${d.approved_by ? `<tr><th>Approved By</th><td>${d.approved_by}</td></tr>` : ''}
                            ${d.approved_at ? `<tr><th>Action Date</th><td>${d.approved_at}</td></tr>` : ''}
                            ${d.remarks ? `<tr><th>Remarks</th><td>${d.remarks}</td></tr>` : ''}
                        </table>
                    `);
                    detailModal.show();
                });
            });

            $('#leaveRequestsTable').on('click', '.delete-leave-request', function () {
                App.confirmDelete({
                    url: $(this).data('url'),
                    onSuccess: () => table.ajax.reload(null, false),
                });
            });

            form.on('erp:success', () => {
                requestModal.hide();
                table.ajax.reload(null, false);
            });
        })(); });
    </script>
@endpush
