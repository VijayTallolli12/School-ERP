@extends('layouts.admin')

@section('title', 'My Leave')
@section('page-title', 'My Leave Requests')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">My Leave</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title mb-0"><i class="ti ti-calendar-off text-primary me-2"></i>My Leave Requests</h3>
            <button class="btn btn-primary btn-sm ms-auto" id="createMyLeave">New Leave Request</button>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered w-100" id="myLeaveTable">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Leave Type</th>
                    <th>Period</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Approved By</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('modals')
    <x-erp.side-panel 
        id="myLeaveOffcanvas" 
        formId="myLeaveForm" 
        title="New Leave Request"
        action="{{ route('admin.teachers.my-leaves.store') }}" 
        method="POST" 
        width="700px" 
        saveButtonText="Submit Request"
    >
        <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Leave Details</h6>
        
        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label required fw-medium text-dark">Leave Type</label>
                <select class="form-select" name="leave_type" required>
                    <option value="">Select</option>
                    <option value="sick">Sick</option>
                    <option value="casual">Casual</option>
                    <option value="personal">Personal</option>
                    <option value="maternity">Maternity</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label required fw-medium text-dark">Start Date</label>
                <input class="form-control" type="date" name="start_date" required>
            </div>
            <div class="col-md-6">
                <label class="form-label required fw-medium text-dark">End Date</label>
                <input class="form-control" type="date" name="end_date" required>
            </div>
            <div class="col-12">
                <label class="form-label fw-medium text-dark">Reason</label>
                <textarea class="form-control" name="reason" rows="3"></textarea>
            </div>
        </div>
    </x-erp.side-panel>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => { (async () => { const DataTable = await window.lazyDT();
            const offcanvas = new bootstrap.Offcanvas(document.getElementById('myLeaveOffcanvas'));
            const form = $('#myLeaveForm');
            const table = $('#myLeaveTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('admin.teachers.my-leaves.data') }}',
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'leave_type', name: 'leave_type'},
                    {data: 'period', name: 'period', orderable: false, searchable: false},
                    {data: 'reason', name: 'reason'},
                    {data: 'status', name: 'status', orderable: false, searchable: false},
                    {data: 'approved_by', name: 'approved_by'},
                ],
            });

            $('#createMyLeave').on('click', () => {
                form[0].reset();
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback.dynamic').remove();
                form.data('is-dirty', false);
                offcanvas.show();
            });

            form.on('erp:success', () => {
                offcanvas.hide();
                table.ajax.reload(null, false);
            });
        })(); });
    </script>
@endpush