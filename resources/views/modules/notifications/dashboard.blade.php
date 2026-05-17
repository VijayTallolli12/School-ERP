@extends('layouts.admin')

@section('title', 'Notifications Dashboard')
@section('page-title', 'Notification Dashboard')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.notifications.index') }}">Notifications</a></li>
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $stats['total_sent'] }}</h3>
                    <small>Total Sent</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                    <small>Pending (Draft)</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $stats['failed'] }}</h3>
                    <small>Failed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $stats['unread_count'] }}</h3>
                    <small>Unread</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <strong><i class="ti ti-list-check me-1"></i> Notification History</strong>
                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-sm btn-outline-primary float-end">View All</a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-bordered w-100 m-0" id="dashboardNotificationsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Sent</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            $('#dashboardNotificationsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                pageLength: 10,
                order: [[0, 'desc']],
                ajax: '{{ route('admin.notifications.data') }}',
                columns: [
                    { data: 'id' },
                    { data: 'title' },
                    { data: 'type_label', orderable: false, searchable: false },
                    { data: 'priority_badge', orderable: false, searchable: false },
                    { data: 'status_badge', orderable: false, searchable: false },
                    { data: 'sent_at', orderable: false, searchable: false },
                ]
            });
        });
    </script>
@endpush