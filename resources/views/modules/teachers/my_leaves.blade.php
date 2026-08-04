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
            <button class="btn btn-primary btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#myLeaveModal">
                <i class="ti ti-plus me-1"></i> New Leave Request
            </button>
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
    <div class="modal fade" id="myLeaveModal" tabindex="-1" aria-hidden="true">
        <form class="modal-content ajax-form" method="POST" action="{{ route('admin.teachers.my-leaves.store') }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">New Leave Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Leave Type</label>
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
                        <label class="form-label required">Start Date</label>
                        <input class="form-control" type="date" name="start_date" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">End Date</label>
                        <input class="form-control" type="date" name="end_date" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Reason</label>
                        <textarea class="form-control" name="reason" rows="3"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="ti ti-x me-1"></i>Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Submit Request</button>
            </div>
        </form>
    </div>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => { (async () => { const DataTable = await window.lazyDT();
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

            $('#myLeaveTable').on('erp:success', () => {
                $('#myLeaveModal').modal('hide');
                table.ajax.reload(null, false);
            });
        })(); });
    </script>
@endpush