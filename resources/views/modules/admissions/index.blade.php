@extends('layouts.admin')

@section('title', 'Admissions')
@section('page-title', 'Admission Management')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Admissions</li>
@endsection

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-2">
            <div class="card mb-0">
                <div class="card-body p-3 text-center">
                    <h4 class="mb-0 fw-bold">{{ $stats['total'] }}</h4>
                    <span class="text-muted small">Total</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card mb-0">
                <div class="card-body p-3 text-center">
                    <h4 class="mb-0 fw-bold text-info">{{ $stats['enquiry'] + $stats['application'] }}</h4>
                    <span class="text-muted small">In Progress</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card mb-0">
                <div class="card-body p-3 text-center">
                    <h4 class="mb-0 fw-bold text-primary">{{ $stats['verified'] }}</h4>
                    <span class="text-muted small">Verified</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card mb-0">
                <div class="card-body p-3 text-center">
                    <h4 class="mb-0 fw-bold text-success">{{ $stats['approved'] }}</h4>
                    <span class="text-muted small">Approved</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card mb-0">
                <div class="card-body p-3 text-center">
                    <h4 class="mb-0 fw-bold text-dark">{{ $stats['converted'] }}</h4>
                    <span class="text-muted small">Converted</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card mb-0">
                <div class="card-body p-3 text-center">
                    <h4 class="mb-0 fw-bold text-danger">{{ $stats['rejected'] }}</h4>
                    <span class="text-muted small">Rejected</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center gap-2">
            <h3 class="card-title mb-0"><i class="ti ti-clipboard-list text-primary me-2"></i>Admission Applications</h3>
            <div class="ms-auto d-flex flex-wrap gap-2">
                <select id="filterStatus" class="form-select form-select-sm" style="width: 160px;">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                @can('admissions.create')
                    <a href="{{ route('admin.admissions.create') }}" class="btn btn-primary btn-sm">
                        New Application
                    </a>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered w-100" id="admissionsTable">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Admission No</th>
                    <th>Applicant</th>
                    <th>Class</th>
                    <th>Academic Year</th>
                    <th>Status</th>
                    <th>Docs</th>
                    <th>Applied On</th>
                    <th width="130">Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => { (async () => { const DataTable = await window.lazyDT();
            const table = $('#admissionsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                order: [[0, 'desc']],
                ajax: {
                    url: '{{ route('admin.admissions.data') }}',
                    data: (d) => {
                        d.status = $('#filterStatus').val();
                    }
                },
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'admission_no', name: 'admission_no'},
                    {data: 'full_name', name: 'first_name'},
                    {data: 'class_section', name: 'class_section', orderable: false, searchable: false},
                    {data: 'academic_year', name: 'academic_year', orderable: false, searchable: false},
                    {data: 'status', name: 'status'},
                    {data: 'documents', name: 'documents', orderable: false, searchable: false},
                    {data: 'applied_on', name: 'applied_on'},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false},
                ]
            });

            $('#filterStatus').on('change', () => table.ajax.reload());
        })(); });
    </script>
@endpush
