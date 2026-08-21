@extends('layouts.admin')

@section('title', 'Library Management')
@section('page-title', 'Library Management')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Library</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs" id="libraryTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#booksPane" type="button">Books</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#issuesPane" type="button">Issue / Return</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#categoriesPane" type="button">Categories</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#authorsPane" type="button">Authors</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#publishersPane" type="button">Publishers</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#fineSettingsPane" type="button">Fine Settings</button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="booksPane">
                    <div class="d-flex mb-3">
                        @can('library.create')
                            <button class="btn btn-primary btn-sm ms-auto open-offcanvas" data-offcanvas="#bookOffcanvas">Add Book</button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="booksTable">
                        <thead><tr><th>ID</th><th>ISBN</th><th>Title</th><th>Category</th><th>Author</th><th>Publisher</th><th>Language</th><th>Qty</th><th>Available</th><th>Status</th><th width="120">Actions</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="issuesPane">
                    <div class="d-flex mb-3">
                        @can('library.create')
                            <button class="btn btn-primary btn-sm ms-auto open-offcanvas" data-offcanvas="#issueOffcanvas">Issue Book</button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="issuesTable">
                        <thead><tr><th>ID</th><th>Book</th><th>Borrower</th><th>Issue Date</th><th>Due Date</th><th>Return Date</th><th>Fine</th><th>Overdue</th><th>Status</th><th width="130">Actions</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="categoriesPane">
                    <div class="d-flex mb-3">
                        @can('library.create')
                            <button class="btn btn-primary btn-sm ms-auto open-offcanvas" data-offcanvas="#categoryOffcanvas">Add Category</button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="categoriesTable">
                        <thead><tr><th>ID</th><th>Name</th><th>Description</th><th>Sort Order</th><th>Books</th><th>Status</th><th width="120">Actions</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="authorsPane">
                    <div class="d-flex mb-3">
                        @can('library.create')
                            <button class="btn btn-primary btn-sm ms-auto open-offcanvas" data-offcanvas="#authorOffcanvas">Add Author</button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="authorsTable">
                        <thead><tr><th>ID</th><th>Name</th><th>Biography</th><th>Books</th><th>Status</th><th width="120">Actions</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="publishersPane">
                    <div class="d-flex mb-3">
                        @can('library.create')
                            <button class="btn btn-primary btn-sm ms-auto open-offcanvas" data-offcanvas="#publisherOffcanvas">Add Publisher</button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="publishersTable">
                        <thead><tr><th>ID</th><th>Name</th><th>Contact</th><th>Books</th><th>Status</th><th width="120">Actions</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="fineSettingsPane">
                    <div class="d-flex mb-3">
                        @can('library.create')
                            <button class="btn btn-primary btn-sm ms-auto open-offcanvas" data-offcanvas="#fineSettingOffcanvas">Add Fine Configuration</button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="fineSettingsTable">
                        <thead><tr><th>ID</th><th>Fine Per Day</th><th>Max Fine</th><th>Grace Period (Days)</th><th>Status</th><th width="120">Actions</th></tr></thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    <x-erp.side-panel
        id="bookOffcanvas"
        formId="bookForm"
        title="Book"
        action="{{ route('admin.library.books.store') }}"
        method="POST"
        width="800px"
        saveButtonText="Save"
    >
        <input type="hidden" name="_method" value="POST" id="bookMethod">
        <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Book Details</h6>
        <div class="row g-4">
            <div class="col-md-6"><label class="form-label fw-medium text-dark">ISBN</label><input class="form-control" name="isbn"></div>
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Title</label><input class="form-control" name="title" required></div>
            <div class="col-md-4"><label class="form-label fw-medium text-dark">Category</label><select class="form-select searchable-select" name="category_id"><option value="">Select</option>@foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-medium text-dark">Author</label><select class="form-select searchable-select" name="author_id"><option value="">Select</option>@foreach($authors as $a)<option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-medium text-dark">Publisher</label><select class="form-select searchable-select" name="publisher_id"><option value="">Select</option>@foreach($publishers as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-medium text-dark">Edition</label><input class="form-control" name="edition"></div>
            <div class="col-md-4"><label class="form-label fw-medium text-dark">Language</label><input class="form-control" name="language" value="English"></div>
            <div class="col-md-4"><label class="form-label fw-medium text-dark">Rack Number</label><input class="form-control" name="rack_number"></div>
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Quantity</label><input class="form-control" type="number" name="quantity" min="1" value="1" required></div>
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
            <div class="col-12"><label class="form-label fw-medium text-dark">Description</label><textarea class="form-control" name="description" rows="2"></textarea></div>
        </div>
    </x-erp.side-panel>

    <x-erp.side-panel
        id="categoryOffcanvas"
        formId="categoryForm"
        title="Category"
        action="{{ route('admin.library.categories.store') }}"
        method="POST"
        width="600px"
        saveButtonText="Save"
    >
        <input type="hidden" name="_method" value="POST" id="categoryMethod">
        <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Category Details</h6>
        <div class="row g-4">
            <div class="col-12"><label class="form-label required fw-medium text-dark">Name</label><input class="form-control" name="name" required></div>
            <div class="col-12"><label class="form-label fw-medium text-dark">Description</label><textarea class="form-control" name="description" rows="2"></textarea></div>
            <div class="col-md-6"><label class="form-label fw-medium text-dark">Sort Order</label><input class="form-control" type="number" name="sort_order" min="0" value="0"></div>
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
        </div>
    </x-erp.side-panel>

    <x-erp.side-panel
        id="authorOffcanvas"
        formId="authorForm"
        title="Author"
        action="{{ route('admin.library.authors.store') }}"
        method="POST"
        width="600px"
        saveButtonText="Save"
    >
        <input type="hidden" name="_method" value="POST" id="authorMethod">
        <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Author Details</h6>
        <div class="row g-4">
            <div class="col-12"><label class="form-label required fw-medium text-dark">Name</label><input class="form-control" name="name" required></div>
            <div class="col-12"><label class="form-label fw-medium text-dark">Biography</label><textarea class="form-control" name="biography" rows="3"></textarea></div>
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
        </div>
    </x-erp.side-panel>

    <x-erp.side-panel
        id="publisherOffcanvas"
        formId="publisherForm"
        title="Publisher"
        action="{{ route('admin.library.publishers.store') }}"
        method="POST"
        width="600px"
        saveButtonText="Save"
    >
        <input type="hidden" name="_method" value="POST" id="publisherMethod">
        <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Publisher Details</h6>
        <div class="row g-4">
            <div class="col-12"><label class="form-label required fw-medium text-dark">Name</label><input class="form-control" name="name" required></div>
            <div class="col-md-6"><label class="form-label fw-medium text-dark">Contact</label><input class="form-control" name="contact"></div>
            <div class="col-12"><label class="form-label fw-medium text-dark">Address</label><textarea class="form-control" name="address" rows="2"></textarea></div>
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
        </div>
    </x-erp.side-panel>

    <x-erp.side-panel
        id="issueOffcanvas"
        formId="issueForm"
        title="Issue Book"
        action="{{ route('admin.library.issues.store') }}"
        method="POST"
        width="700px"
        saveButtonText="Issue Book"
    >
        <input type="hidden" name="_method" value="POST" id="issueMethod">
        <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Issue Details</h6>
        <div class="row g-4">
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Book</label><select class="form-select searchable-select" name="book_id" required data-placeholder="Search book..."><option value="">Select</option>@foreach($books as $b)<option value="{{ $b->id }}" data-available="{{ $b->available_copies }}">{{ $b->title }} @if($b->isbn)({{ $b->isbn }})@endif (Available: {{ $b->available_copies }})</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Borrower Type</label><select class="form-select" name="issueable_type" id="borrowerType" required><option value="">Select</option><option value="student">Student</option><option value="teacher">Teacher</option></select></div>
            <div class="col-md-12"><label class="form-label required fw-medium text-dark">Borrower</label>
                <input type="hidden" name="issueable_id" id="borrowerSelect" required>
                <div id="studentSearchWrap" style="display:none"><select class="form-select searchable-select borrower-search" data-ajax-url="{{ route('admin.library.search.students') }}" data-placeholder="Search student..." id="studentSearch"><option value=""></option></select></div>
                <div id="teacherSearchWrap" style="display:none"><select class="form-select searchable-select borrower-search" data-ajax-url="{{ route('admin.library.search.teachers') }}" data-placeholder="Search teacher..." id="teacherSearch"><option value=""></option></select></div>
            </div>
            <div class="col-md-6"><label class="form-label fw-medium text-dark">Issue Date</label><input class="form-control" type="date" name="issue_date" id="issueDate"></div>
            <div class="col-md-6"><label class="form-label fw-medium text-dark">Due Date</label><input class="form-control" type="date" name="due_date" id="dueDate"></div>
            <div class="col-12"><label class="form-label fw-medium text-dark">Notes</label><textarea class="form-control" name="notes" rows="2"></textarea></div>
        </div>
    </x-erp.side-panel>

    <x-erp.side-panel
        id="returnOffcanvas"
        formId="returnForm"
        title="Return Book"
        action=""
        method="POST"
        width="600px"
        saveButtonText="Return Book"
    >
        <input type="hidden" name="_method" value="PUT" id="returnMethod">
        <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Return Details</h6>
        <div class="row g-4">
            <div class="col-md-6"><label class="form-label fw-medium text-dark">Return Date</label><input class="form-control" type="date" name="return_date"></div>
            <div class="col-12"><label class="form-label fw-medium text-dark">Notes</label><textarea class="form-control" name="notes" rows="2"></textarea></div>
            <div class="col-12" id="finePreview" style="display:none">
                <div class="alert alert-info mb-0">
                    <strong>Fine Amount:</strong> <span id="fineAmount">₹ 0.00</span>
                </div>
            </div>
        </div>
    </x-erp.side-panel>

    <x-erp.side-panel
        id="fineSettingOffcanvas"
        formId="fineSettingForm"
        title="Fine Configuration"
        action="{{ route('admin.library.fine-settings.store') }}"
        method="POST"
        width="600px"
        saveButtonText="Save"
    >
        <input type="hidden" name="_method" value="POST" id="fineSettingMethod">
        <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Fine Settings Details</h6>
        <div class="row g-4">
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Fine Per Day (₹)</label><input class="form-control" type="number" name="fine_per_day" step="0.01" min="0" value="1" required></div>
            <div class="col-md-6"><label class="form-label fw-medium text-dark">Max Fine (₹)</label><input class="form-control" type="number" name="max_fine" step="0.01" min="0"></div>
            <div class="col-md-6"><label class="form-label fw-medium text-dark">Grace Period (Days)</label><input class="form-control" type="number" name="grace_period_days" min="0" value="0"></div>
            <div class="col-md-6"><label class="form-label required fw-medium text-dark">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
        </div>
    </x-erp.side-panel>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => { (async () => { const DataTable = await window.lazyDT();
            const tables = {
                books: $('#booksTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: '{{ route('admin.library.books.data') }}', columns: [
                    {data:'id'}, {data:'isbn'}, {data:'title'}, {data:'category_name', orderable:false, searchable:false}, {data:'author_name', orderable:false, searchable:false}, {data:'publisher_name', orderable:false, searchable:false}, {data:'language'}, {data:'quantity'}, {data:'available_copies'}, {data:'status'}, {data:'actions', orderable:false, searchable:false}
                ]}),
                categories: $('#categoriesTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: '{{ route('admin.library.categories.data') }}', columns: [
                    {data:'id'}, {data:'name'}, {data:'description'}, {data:'sort_order'}, {data:'books_count', searchable:false}, {data:'status'}, {data:'actions', orderable:false, searchable:false}
                ]}),
                authors: $('#authorsTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: '{{ route('admin.library.authors.data') }}', columns: [
                    {data:'id'}, {data:'name'}, {data:'biography'}, {data:'books_count', searchable:false}, {data:'status'}, {data:'actions', orderable:false, searchable:false}
                ]}),
                publishers: $('#publishersTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: '{{ route('admin.library.publishers.data') }}', columns: [
                    {data:'id'}, {data:'name'}, {data:'contact'}, {data:'books_count', searchable:false}, {data:'status'}, {data:'actions', orderable:false, searchable:false}
                ]}),
                issues: $('#issuesTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: '{{ route('admin.library.issues.data') }}', columns: [
                    {data:'id'}, {data:'book_title', orderable:false, searchable:false}, {data:'borrower', orderable:false, searchable:false}, {data:'issue_date'}, {data:'due_date'}, {data:'return_date', orderable:false, searchable:false}, {data:'fine_amount', orderable:false, searchable:false}, {data:'is_overdue', orderable:false, searchable:false}, {data:'status'}, {data:'actions', orderable:false, searchable:false}
                ]}),
                fineSettings: $('#fineSettingsTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: '{{ route('admin.library.fine-settings.data') }}', columns: [
                    {data:'id'}, {data:'fine_per_day'}, {data:'max_fine'}, {data:'grace_period_days'}, {data:'status'}, {data:'actions', orderable:false, searchable:false}
                ]})
            };
            initTabPersistence('#libraryTabs');

            const config = {
                book: {offcanvas: '#bookOffcanvas', store: '{{ route('admin.library.books.store') }}', table: tables.books},
                category: {offcanvas: '#categoryOffcanvas', store: '{{ route('admin.library.categories.store') }}', table: tables.categories},
                author: {offcanvas: '#authorOffcanvas', store: '{{ route('admin.library.authors.store') }}', table: tables.authors},
                publisher: {offcanvas: '#publisherOffcanvas', store: '{{ route('admin.library.publishers.store') }}', table: tables.publishers},
                'fine-setting': {offcanvas: '#fineSettingOffcanvas', store: '{{ route('admin.library.fine-settings.store') }}', table: tables.fineSettings},
                issue: {offcanvas: '#issueOffcanvas', store: '{{ route('admin.library.issues.store') }}', table: tables.issues}
            };

            $('.open-offcanvas').on('click', function () {
                const offcanvasId = $(this).data('offcanvas');
                const form = $(`${offcanvasId} form`);
                const setup = Object.values(config).find(item => item.offcanvas === offcanvasId);
                form[0].reset();
                form.attr('action', setup.store);
                form.find('[name="_method"]').val('POST');
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback.dynamic').remove();
                if (offcanvasId === '#issueOffcanvas') {
                    const today = new Date().toISOString().split('T')[0];
                    const due = new Date(Date.now() + 14 * 86400000).toISOString().split('T')[0];
                    $('#issueDate').val(today);
                    $('#dueDate').val(due);
                    resetBorrowerFields();
                    $('#borrowerType').val('');
                }
                const typeName = form.attr('id').replace('Form', '');
                $('#' + offcanvasId.substring(1) + 'Title').text('Add ' + typeName.charAt(0).toUpperCase() + typeName.slice(1));
                form.find('select').trigger('change.select2');

                bootstrap.Offcanvas.getOrCreateInstance(document.querySelector(offcanvasId)).show();
            });

            $('.ajax-form').on('erp:success', function () {
                const offcanvasEl = $(this).closest('.offcanvas')[0];
                if (offcanvasEl) {
                    bootstrap.Offcanvas.getInstance(offcanvasEl).hide();
                }
                Object.values(tables).forEach(table => table.ajax.reload(null, false));
            });

            $(document).on('click', '.edit-library', function () {
                const type = $(this).data('type');
                const setup = config[type];
                const form = $(`${setup.offcanvas} form`);
                $.get($(this).data('url'), (response) => {
                    form[0].reset();
                    form.attr('action', $(this).data('update-url'));
                    form.find('[name="_method"]').val('PUT');
                    form.find('.is-invalid').removeClass('is-invalid');
                    form.find('.invalid-feedback.dynamic').remove();
                    Object.entries(response.data).forEach(([key, value]) => {
                        const field = form.find(`[name="${key}"]`);
                        if (field.attr('type') === 'checkbox') {
                            field.prop('checked', Boolean(value));
                        } else {
                            field.val(value);
                        }
                    });
                    
                    let titleName = type;
                    if (titleName === 'fine-setting') titleName = 'Fine Setting';
                    $('#' + setup.offcanvas.substring(1) + 'Title').text('Edit ' + titleName.charAt(0).toUpperCase() + titleName.slice(1));
                    form.find('select').trigger('change.select2');

                    bootstrap.Offcanvas.getOrCreateInstance(document.querySelector(setup.offcanvas)).show();
                });
            });

            $(document).on('click', '.delete-library', function () {
                App.confirmDelete({
                    url: $(this).data('url'),
                    onSuccess: () => Object.values(tables).forEach(table => table.ajax.reload(null, false))
                });
            });

            // Return book
            $(document).on('click', '.return-book', function () {
                const url = $(this).data('url');
                const form = $('#returnForm');
                form.attr('action', url);
                const today = new Date().toISOString().split('T')[0];
                form.find('[name="return_date"]').val(today);
                form.find('[name="notes"]').val('');
                $('#finePreview').hide();
                
                $('#returnOffcanvasTitle').text('Return Book');
                form.find('select').trigger('change.select2');

                bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('returnOffcanvas')).show();
            });

            function resetBorrowerFields() {
                $('#borrowerSelect').val('');
                $('.borrower-search').each(function () {
                    const $select = $(this);
                    $select.val(null).trigger('change');
                });
                $('#studentSearchWrap, #teacherSearchWrap').hide();
            }

            function showBorrowerSelector(type) {
                const selectors = {
                    student: {active: '#studentSearchWrap', inactive: '#teacherSearchWrap'},
                    teacher: {active: '#teacherSearchWrap', inactive: '#studentSearchWrap'},
                };

                resetBorrowerFields();

                if (!selectors[type]) {
                    return;
                }

                $(selectors[type].inactive).hide();
                $(selectors[type].active).show();
                App.initSearchableSelects($(selectors[type].active));
            }

            $('#borrowerType').on('change', function () {
                showBorrowerSelector($(this).val());
            });

            $(document).on('change', '#studentSearch, #teacherSearch', function () {
                const val = $(this).val();
                const $bs = $('#borrowerSelect');
                $bs.val(val || '');
            });
        })(); });
    </script>
@endpush
