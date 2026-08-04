@extends('layouts.admin')

@section('title', 'My Attendance')
@section('page-title', 'My Attendance')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">My Attendance</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0"><i class="ti ti-calendar-check text-primary me-2"></i>My Attendance Records</h3>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered w-100" id="myAttendanceTable">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Remarks</th>
                    <th>Marked By</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => { (async () => { await window.lazyDT();
            $('#myAttendanceTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('admin.teachers.my-attendance.data') }}',
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'attendance_date', name: 'attendance_date'},
                    {data: 'status', name: 'status', orderable: false, searchable: false},
                    {data: 'remarks', name: 'remarks'},
                    {data: 'marked_by', name: 'marked_by'},
                ],
            });
        })(); });
    </script>
@endpush