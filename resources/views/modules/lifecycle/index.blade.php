@extends('layouts.admin')

@section('title', 'Student Lifecycle')
@section('page-title', 'Student Lifecycle Management')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Student Lifecycle</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs" id="lifecycleTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#promotionPane" type="button"><i class="ti ti-arrow-up-circle me-1"></i>Promotion</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#transferPane" type="button"><i class="ti ti-logout me-1"></i>Transfer Student</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tcPane" type="button"><i class="ti ti-file-certificate me-1"></i>Transfer Certificate</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#alumniPane" type="button"><i class="ti ti-graduation-cap me-1"></i>Alumni</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#historyPane" type="button"><i class="ti ti-history me-1"></i>History</button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                {{-- ============ PROMOTION TAB ============ --}}
                <div class="tab-pane fade show active" id="promotionPane" role="tabpanel">
                    <div class="d-flex mb-3">
                        <h3 class="card-title mb-0 me-3"><i class="ti ti-arrow-up-circle text-primary me-2"></i>Bulk Promotion</h3>
                        <div class="ms-auto">
                            <a href="{{ route('admin.lifecycle.promotions') }}" class="btn btn-outline-primary btn-sm" title="Open full-page promotion view">
                                <i class="ti ti-maximize me-1"></i> Full Page
                            </a>
                        </div>
                    </div>
                    @include('modules.lifecycle._promote')
                </div>

                {{-- ============ TRANSFER TAB ============ --}}
                <div class="tab-pane fade" id="transferPane" role="tabpanel">
                    <div class="d-flex mb-3 align-items-center">
                        <h3 class="card-title mb-0"><i class="ti ti-logout text-primary me-2"></i>Transfer Student</h3>
                        <div class="ms-auto">
                            <button type="button" class="btn btn-primary btn-sm" id="toggleTransferFormBtn">
                                <i class="ti ti-plus me-1"></i> Add Transfer Student
                            </button>
                        </div>
                    </div>

                    <!-- Table View -->
                    <div id="transferListView">
                        <table class="table table-striped table-bordered w-100" id="transferListTable">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student</th>
                                <th>Admission No</th>
                                <th>From Class</th>
                                <th>Destination School</th>
                                <th>Transferred On</th>
                                <th width="80">Actions</th>
                            </tr>
                            </thead>
                        </table>
                    </div>

                    <!-- Form View -->
                    <div id="transferFormView" style="display: none;">
                        <div class="d-flex mb-3">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="backToTransferListBtn">
                                <i class="ti ti-arrow-left me-1"></i> Back to List
                            </button>
                        </div>
                        <div class="row">
                            <div class="col-xl-6 col-lg-8">
                                <div class="card card-flat mb-0">
                                    <div class="card-body">
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
                                    <div class="card-footer d-flex gap-2">
                                        <button type="button" class="btn btn-primary lifecycle-submit" data-url="{{ route('admin.lifecycle.transfer') }}">
                                            <i class="ti ti-logout me-1"></i> Transfer Student
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ============ TC TAB ============ --}}
                <div class="tab-pane fade" id="tcPane" role="tabpanel">
                    <div class="d-flex mb-3">
                        <h3 class="card-title mb-0"><i class="ti ti-file-certificate text-primary me-2"></i>Issue Transfer Certificate</h3>
                    </div>
                    <div class="row">
                        <div class="col-xl-6 col-lg-8">
                            <div class="card card-flat mb-0">
                                <div class="card-body">
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
                                <div class="card-footer d-flex gap-2">
                                    <button type="button" class="btn btn-primary lifecycle-submit" data-url="{{ route('admin.lifecycle.tc') }}">
                                        <i class="ti ti-file-certificate me-1"></i> Issue TC
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ============ ALUMNI TAB ============ --}}
                <div class="tab-pane fade" id="alumniPane" role="tabpanel">
                    <div class="d-flex mb-3">
                        <h3 class="card-title mb-0"><i class="ti ti-graduation-cap text-primary me-2"></i>Mark as Alumni</h3>
                    </div>
                    <div class="row">
                        <div class="col-xl-6 col-lg-8">
                            <div class="card card-flat mb-0">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label required">Student</label>
                                        <select class="form-select lifecycle-student-select" name="student_id" required></select>
                                    </div>
                                    <p class="text-muted mb-0">The student's active session will be closed and their status will be set to <strong>Alumni</strong>.</p>
                                </div>
                                <div class="card-footer d-flex gap-2">
                                    <button type="button" class="btn btn-secondary lifecycle-alumni-submit">
                                        <i class="ti ti-graduation-cap me-1"></i> Mark Alumni
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ============ HISTORY TAB ============ --}}
                <div class="tab-pane fade" id="historyPane" role="tabpanel">
                    <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
                        <h3 class="card-title mb-0 me-3"><i class="ti ti-history text-primary me-2"></i>Lifecycle History</h3>
                        <div class="ms-auto d-flex flex-wrap gap-2">
                            <select id="filterType" class="form-select form-select-sm" style="width: 200px;">
                                <option value="">All Types</option>
                                @foreach ($types as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
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
        </div>
    </div>
@endsection


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

            const transferTable = $('#transferListTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                order: [[0, 'desc']],
                ajax: {
                    url: '{{ route('admin.lifecycle.data') }}',
                    data: (d) => {
                        d.transfer_type = 'transfer';
                    }
                },
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'student_name', name: 'student_name', orderable: false},
                    {data: 'admission_no', name: 'admission_no', orderable: false},
                    {data: 'from_class', name: 'from_class', orderable: false, searchable: false},
                    {data: 'destination_school', name: 'destination_school'},
                    {data: 'transferred_on', name: 'transferred_on'},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false},
                ]
            });

            $('#toggleTransferFormBtn').on('click', function() {
                $('#transferListView, #toggleTransferFormBtn').hide();
                $('#transferFormView').fadeIn();
            });

            $('#backToTransferListBtn').on('click', function() {
                $('#transferFormView').hide();
                $('#transferListView, #toggleTransferFormBtn').fadeIn();
                transferTable.ajax.reload(null, false);
            });

            $('.lifecycle-student-select').each(function () {
                $(this).select2({
                    placeholder: 'Search student...',
                    width: '100%',
                    dropdownParent: $(this).closest('.card-flat'),
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

            const collectPanelData = (panel) => {
                const data = {_token: '{{ csrf_token() }}'};
                panel.find('input, select, textarea').each(function () {
                    const name = $(this).attr('name');
                    if (name) data[name] = $(this).val();
                });
                return data;
            };

            $('.lifecycle-submit').on('click', function () {
                const btn = $(this).prop('disabled', true);
                const panel = $(this).closest('.card-flat');
                const data = collectPanelData(panel);

                $.ajax({
                    url: $(this).data('url'),
                    method: 'POST',
                    data,
                    success: (res) => {
                        if (res.success) {
                            App.toast('success', res.message);
                            if ($('#lifecycleTable').length) table.ajax.reload(null, false);
                            if (typeof transferTable !== 'undefined') transferTable.ajax.reload(null, false);
                            panel.find('select, input, textarea').not('[type="hidden"]').val('').trigger('change');
                            // If we are in the transfer form view, go back to list
                            if (panel.closest('#transferFormView').length) {
                                $('#backToTransferListBtn').click();
                            }
                        } else {
                            App.toast('error', res.message || 'Action failed.');
                        }
                    },
                    error: (xhr) => {
                        const res = xhr.responseJSON || {};
                        App.toast('error', res.message || 'Something went wrong.');
                    },
                    complete: () => btn.prop('disabled', false)
                });
            });

            $('.lifecycle-alumni-submit').on('click', function () {
                const btn = $(this).prop('disabled', true);
                const panel = $(this).closest('.card-flat');
                const studentId = panel.find('select[name="student_id"]').val();

                if (!studentId) {
                    App.toast('error', 'Please select a student.');
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
                        App.toast('success', res.message || 'Done.');
                        if ($('#lifecycleTable').length) table.ajax.reload(null, false);
                        panel.find('select').val('').trigger('change');
                    },
                    error: (xhr) => {
                        const res = xhr.responseJSON || {};
                        App.toast('error', res.message || 'Something went wrong.');
                    },
                    complete: () => btn.prop('disabled', false)
                });
            });

            initTabPersistence('#lifecycleTabs');
        })(); });
    </script>
@endpush
