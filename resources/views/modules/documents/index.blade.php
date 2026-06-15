@extends('layouts.admin')

@section('title', 'Student Documents')
@section('page-title', 'Student Documents')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Student Documents</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center flex-wrap gap-2">
            <h3 class="card-title mb-0"><i class="ti ti-file-text text-primary me-2"></i>All Documents</h3>
            @can('student_documents.create')
                <button class="btn btn-primary btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#documentModal" id="createDocument">
                    <i class="ti ti-plus me-1"></i> Upload Document
                </button>
            @endcan
        </div>
        <div class="card-body">
            <!-- Filters -->
            <div class="row g-2 mb-3">
                <div class="col-md-3">
                    <select class="form-select form-select-sm" id="filterStudent">
                        <option value="">All Students</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select form-select-sm" id="filterClass">
                        <option value="">All Classes</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select form-select-sm" id="filterDocumentType">
                        <option value="">All Types</option>
                        @foreach ($documentTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select form-select-sm" id="filterVerified">
                        <option value="">All Status</option>
                        <option value="1">Verified</option>
                        <option value="0">Pending</option>
                    </select>
                </div>
            </div>

            <table class="table table-striped table-bordered w-100" id="documentsTable">
                <thead>
                <tr>
                    <th>Student</th>
                    <th>Class</th>
                    <th>Document Type</th>
                    <th>Title</th>
                    <th>Uploaded Date</th>
                    <th>Issue Date</th>
                    <th>Expiry Date</th>
                    <th>Verification</th>
                    <th>Uploaded By</th>
                    <th width="150">Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('modals')
    <!-- Create/Edit Document Modal -->
    <div class="modal fade" id="documentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form class="modal-content ajax-form" id="documentForm" method="POST" action="{{ route('admin.documents.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" value="POST" id="documentMethod">
                <div class="modal-header">
                    <h5 class="modal-title" id="documentModalTitle">Upload Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Student</label>
                            <select class="form-select" name="student_id" id="docStudentId" required>
                                <option value="">Select Student</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Document Type</label>
                            <select class="form-select" name="document_type" id="docDocumentType" required>
                                <option value="">Select Type</option>
                                @foreach ($documentCategories as $category => $types)
                                    <optgroup label="{{ ucfirst($category) }}">
                                        @foreach ($types as $type)
                                            <option value="{{ $type }}">{{ $documentTypes[$type] }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label required">Title</label>
                            <input class="form-control" name="title" id="docTitle" required maxlength="255" placeholder="Document title">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label required">File</label>
                            <input class="form-control" type="file" name="file" id="docFile" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            <div class="form-text">Allowed: PDF, JPG, PNG, DOC, DOCX. Max 10 MB.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Issue Date</label>
                            <input class="form-control" type="date" name="issue_date" id="docIssueDate">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Expiry Date</label>
                            <input class="form-control" type="date" name="expiry_date" id="docExpiryDate">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" id="docRemarks" rows="3" maxlength="1000" placeholder="Optional remarks"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="documentSubmit"><i class="ti ti-upload me-1"></i>Upload</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Document Modal -->
    <div class="modal fade" id="viewDocumentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Document Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3" id="viewDocumentContent">
                        <div class="col-md-6">
                            <label class="fw-semibold text-secondary small">Student</label>
                            <p id="viewStudent" class="mb-0">-</p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-semibold text-secondary small">Document Type</label>
                            <p id="viewDocumentType" class="mb-0">-</p>
                        </div>
                        <div class="col-12">
                            <label class="fw-semibold text-secondary small">Title</label>
                            <p id="viewTitle" class="mb-0">-</p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-semibold text-secondary small">File Name</label>
                            <p id="viewFileName" class="mb-0">-</p>
                        </div>
                        <div class="col-md-3">
                            <label class="fw-semibold text-secondary small">File Size</label>
                            <p id="viewFileSize" class="mb-0">-</p>
                        </div>
                        <div class="col-md-3">
                            <label class="fw-semibold text-secondary small">File Type</label>
                            <p id="viewMimeType" class="mb-0">-</p>
                        </div>
                        <div class="col-md-4">
                            <label class="fw-semibold text-secondary small">Issue Date</label>
                            <p id="viewIssueDate" class="mb-0">-</p>
                        </div>
                        <div class="col-md-4">
                            <label class="fw-semibold text-secondary small">Expiry Date</label>
                            <p id="viewExpiryDate" class="mb-0">-</p>
                        </div>
                        <div class="col-md-4">
                            <label class="fw-semibold text-secondary small">Verification Status</label>
                            <p id="viewVerified" class="mb-0">-</p>
                        </div>
                        <div class="col-12">
                            <label class="fw-semibold text-secondary small">Remarks</label>
                            <p id="viewRemarks" class="mb-0">-</p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-semibold text-secondary small">Uploaded By</label>
                            <p id="viewUploadedBy" class="mb-0">-</p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-semibold text-secondary small">Uploaded At</label>
                            <p id="viewCreatedAt" class="mb-0">-</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-primary" id="viewDownloadBtn" target="_blank">
                        <i class="ti ti-download me-1"></i> Download
                    </a>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', async () => { (async () => { const DataTable = await window.lazyDT();
            // Load students and populate filter & select
            const loadStudents = (selectId, selectedId = null) => {
                fetch('{{ route('admin.students.data') }}')
                    .then(res => res.json())
                    .then(data => {
                        const select = document.getElementById(selectId);
                        select.innerHTML = '<option value="">All Students</option>';
                        (data.data || []).forEach(s => {
                            const opt = document.createElement('option');
                            opt.value = s.id;
                            opt.textContent = s.full_name + ' (' + (s.admission_no || '') + ')';
                            if (selectedId && s.id == selectedId) opt.selected = true;
                            select.appendChild(opt);
                        });
                    });
            };

            const filterStudent = document.getElementById('filterStudent');
            const docStudentId = document.getElementById('docStudentId');
            loadStudents('filterStudent');
            loadStudents('docStudentId');

            // DataTable
            const table = $('#documentsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('admin.documents.data') }}',
                    data: (d) => {
                        d.student_id = filterStudent.value;
                        d.class_id = document.getElementById('filterClass').value;
                        d.document_type = document.getElementById('filterDocumentType').value;
                        d.is_verified = document.getElementById('filterVerified').value;
                    },
                },
                columns: [
                    { data: 'student', name: 'student' },
                    { data: 'class', name: 'class', orderable: false, searchable: false },
                    { data: 'document_type', name: 'document_type' },
                    { data: 'title', name: 'title' },
                    { data: 'uploaded_at', name: 'created_at' },
                    { data: 'issue_date', name: 'issue_date' },
                    { data: 'expiry_date', name: 'expiry_date' },
                    { data: 'is_verified', name: 'is_verified' },
                    { data: 'uploaded_by', name: 'uploaded_by', orderable: false, searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false },
                ],
                order: [[4, 'desc']],
                columnDefs: [{ targets: [4, 5, 6], className: 'text-nowrap' }],
            });

            // Filter reload
            [filterStudent, document.getElementById('filterClass'),
                document.getElementById('filterDocumentType'), document.getElementById('filterVerified')
            ].forEach(el => {
                el.addEventListener('change', () => table.ajax.reload());
            });

            // Create button
            document.getElementById('createDocument')?.addEventListener('click', () => {
                document.getElementById('documentForm').reset();
                document.getElementById('documentMethod').value = 'POST';
                document.getElementById('documentForm').action = '{{ route('admin.documents.store') }}';
                document.getElementById('documentModalTitle').textContent = 'Upload Document';
                document.getElementById('documentSubmit').textContent = 'Upload';
                document.getElementById('docFile').required = true;
                loadStudents('docStudentId');
            });

            // Edit button (via event delegation)
            $(document).on('click', '.edit-document', function () {
                const id = this.dataset.id;
                fetch('{{ url('admin/documents') }}/' + id)
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) return App.toast?.('error', data.message);
                        const doc = data.document;
                        document.getElementById('documentMethod').value = 'POST';
                        document.getElementById('documentForm').action = '{{ url('admin/documents') }}/' + id;
                        document.getElementById('documentModalTitle').textContent = 'Edit Document';
                        document.getElementById('documentSubmit').textContent = 'Update';
                        document.getElementById('docFile').required = false;
                        loadStudents('docStudentId', doc.student_id);
                        document.getElementById('docDocumentType').value = doc.document_type;
                        document.getElementById('docTitle').value = doc.title;
                        document.getElementById('docIssueDate').value = doc.issue_date || '';
                        document.getElementById('docExpiryDate').value = doc.expiry_date || '';
                        document.getElementById('docRemarks').value = doc.remarks || '';
                        const modal = new bootstrap.Modal(document.getElementById('documentModal'));
                        modal.show();
                    });
            });

            // View button
            $(document).on('click', '.view-document', function () {
                const id = this.dataset.id;
                fetch('{{ url('admin/documents') }}/' + id)
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) return App.toast?.('error', data.message);
                        const doc = data.document;
                        document.getElementById('viewStudent').textContent = doc.student_name;
                        document.getElementById('viewDocumentType').innerHTML = '<span class="badge bg-secondary">' + doc.document_type_label + '</span>';
                        document.getElementById('viewTitle').textContent = doc.title;
                        document.getElementById('viewFileName').textContent = doc.file_name;
                        document.getElementById('viewFileSize').textContent = doc.file_size;
                        document.getElementById('viewMimeType').textContent = doc.mime_type;
                        document.getElementById('viewIssueDate').textContent = doc.issue_date || '-';
                        document.getElementById('viewExpiryDate').textContent = doc.expiry_date || '-';
                        document.getElementById('viewVerified').innerHTML = doc.is_verified
                            ? '<span class="badge bg-success">Verified</span>'
                            : '<span class="badge bg-warning text-dark">Pending</span>';
                        document.getElementById('viewRemarks').textContent = doc.remarks || '-';
                        document.getElementById('viewUploadedBy').textContent = doc.uploader_name || '-';
                        document.getElementById('viewCreatedAt').textContent = doc.created_at;
                        document.getElementById('viewDownloadBtn').href = '{{ url('admin/documents') }}/' + id + '/download';
                        const modal = new bootstrap.Modal(document.getElementById('viewDocumentModal'));
                        modal.show();
                    });
            });

            // Toggle verify
            $(document).on('click', '.toggle-verify', async function () {
                const id = this.dataset.id;
                const btn = this;
                const Swal = await window.lazySwal();
                Swal.fire({
                    title: 'Toggle Verification?',
                    text: 'Change the verification status of this document.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, toggle',
                }).then((result) => {
                    if (!result.isConfirmed) return;
                    fetch('{{ url('admin/documents') }}/' + id + '/toggle-verify', {
                        method: 'PATCH',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                App.toast?.('success', data.message);
                                table.ajax.reload();
                            } else {
                                App.toast?.('error', data.message);
                            }
                        });
                });
            });

            // Delete
            $(document).on('click', '.delete-document', async function () {
                const id = this.dataset.id;
                App.confirmDelete({
                    url: '{{ url('admin/documents') }}/' + id,
                    onSuccess: () => table.ajax.reload(),
                });
            });
        });
    </script>
@endpush
