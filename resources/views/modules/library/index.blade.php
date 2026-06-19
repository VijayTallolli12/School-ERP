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
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#booksPane" type="button"><i class="ti ti-book me-1"></i>Books</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#categoriesPane" type="button"><i class="ti ti-tags me-1"></i>Categories</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#authorsPane" type="button"><i class="ti ti-users me-1"></i>Authors</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#publishersPane" type="button"><i class="ti ti-building me-1"></i>Publishers</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#issuesPane" type="button"><i class="ti ti-arrow-up-down me-1"></i>Issue / Return</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#fineSettingsPane" type="button"><i class="ti ti-coin me-1"></i>Fine Settings</button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="booksPane">
                    <div class="d-flex mb-3">
                        @can('library.create')
                            <button class="btn btn-primary btn-sm ms-auto open-modal" data-modal="#bookModal">
                                <i class="ti ti-plus me-1"></i> Add Book
                            </button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="booksTable">
                        <thead><tr><th>ID</th><th>ISBN</th><th>Title</th><th>Category</th><th>Author</th><th>Publisher</th><th>Language</th><th>Qty</th><th>Available</th><th>Status</th><th width="120">Actions</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="categoriesPane">
                    <div class="d-flex mb-3">
                        @can('library.create')
                            <button class="btn btn-primary btn-sm ms-auto open-modal" data-modal="#categoryModal">
                                <i class="ti ti-plus me-1"></i> Add Category
                            </button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="categoriesTable">
                        <thead><tr><th>ID</th><th>Name</th><th>Description</th><th>Sort Order</th><th>Books</th><th>Status</th><th width="120">Actions</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="authorsPane">
                    <div class="d-flex mb-3">
                        @can('library.create')
                            <button class="btn btn-primary btn-sm ms-auto open-modal" data-modal="#authorModal">
                                <i class="ti ti-plus me-1"></i> Add Author
                            </button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="authorsTable">
                        <thead><tr><th>ID</th><th>Name</th><th>Biography</th><th>Books</th><th>Status</th><th width="120">Actions</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="publishersPane">
                    <div class="d-flex mb-3">
                        @can('library.create')
                            <button class="btn btn-primary btn-sm ms-auto open-modal" data-modal="#publisherModal">
                                <i class="ti ti-plus me-1"></i> Add Publisher
                            </button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="publishersTable">
                        <thead><tr><th>ID</th><th>Name</th><th>Contact</th><th>Books</th><th>Status</th><th width="120">Actions</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="issuesPane">
                    <div class="d-flex mb-3">
                        @can('library.create')
                            <button class="btn btn-primary btn-sm ms-auto open-modal" data-modal="#issueModal">
                                <i class="ti ti-plus me-1"></i> Issue Book
                            </button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="issuesTable">
                        <thead><tr><th>ID</th><th>Book</th><th>Borrower</th><th>Issue Date</th><th>Due Date</th><th>Return Date</th><th>Fine</th><th>Overdue</th><th>Status</th><th width="130">Actions</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="fineSettingsPane">
                    <div class="d-flex mb-3">
                        @can('library.create')
                            <button class="btn btn-primary btn-sm ms-auto open-modal" data-modal="#fineSettingModal">
                                <i class="ti ti-plus me-1"></i> Add Fine Configuration
                            </button>
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
    <div class="modal fade" id="bookModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form class="modal-content ajax-form library-form" method="POST" action="{{ route('admin.library.books.store') }}">
                @csrf <input type="hidden" name="_method" value="POST">
                <div class="modal-header"><h5 class="modal-title">Book</h5><button class="btn-close" data-bs-dismiss="modal" type="button"></button></div>
                <div class="modal-body row g-3">
                    <div class="col-md-6"><label class="form-label">ISBN</label><input class="form-control" name="isbn"></div>
                    <div class="col-md-6"><label class="form-label required">Title</label><input class="form-control" name="title" required></div>
                    <div class="col-md-4"><label class="form-label">Category</label><select class="form-select searchable-select" name="category_id"><option value="">Select</option>@foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label class="form-label">Author</label><select class="form-select searchable-select" name="author_id"><option value="">Select</option>@foreach($authors as $a)<option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label class="form-label">Publisher</label><select class="form-select searchable-select" name="publisher_id"><option value="">Select</option>@foreach($publishers as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label class="form-label">Edition</label><input class="form-control" name="edition"></div>
                    <div class="col-md-4"><label class="form-label">Language</label><input class="form-control" name="language" value="English"></div>
                    <div class="col-md-4"><label class="form-label">Rack Number</label><input class="form-control" name="rack_number"></div>
                    <div class="col-md-6"><label class="form-label required">Quantity</label><input class="form-control" type="number" name="quantity" min="1" value="1" required></div>
                    <div class="col-md-6"><label class="form-label required">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                    <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2"></textarea></div>
                </div>
                <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal" type="button">Cancel</button><button class="btn btn-primary py-2" type="submit"><i class="ti ti-device-floppy me-1"></i> Save</button></div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content ajax-form library-form" method="POST" action="{{ route('admin.library.categories.store') }}">
                @csrf <input type="hidden" name="_method" value="POST">
                <div class="modal-header"><h5 class="modal-title">Category</h5><button class="btn-close" data-bs-dismiss="modal" type="button"></button></div>
                <div class="modal-body row g-3">
                    <div class="col-12"><label class="form-label required">Name</label><input class="form-control" name="name" required></div>
                    <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2"></textarea></div>
                    <div class="col-md-6"><label class="form-label">Sort Order</label><input class="form-control" type="number" name="sort_order" min="0" value="0"></div>
                    <div class="col-md-6"><label class="form-label required">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                </div>
                <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal" type="button">Cancel</button><button class="btn btn-primary py-2" type="submit"><i class="ti ti-device-floppy me-1"></i> Save</button></div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="authorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content ajax-form library-form" method="POST" action="{{ route('admin.library.authors.store') }}">
                @csrf <input type="hidden" name="_method" value="POST">
                <div class="modal-header"><h5 class="modal-title">Author</h5><button class="btn-close" data-bs-dismiss="modal" type="button"></button></div>
                <div class="modal-body row g-3">
                    <div class="col-12"><label class="form-label required">Name</label><input class="form-control" name="name" required></div>
                    <div class="col-12"><label class="form-label">Biography</label><textarea class="form-control" name="biography" rows="3"></textarea></div>
                    <div class="col-md-6"><label class="form-label required">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                </div>
                <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal" type="button">Cancel</button><button class="btn btn-primary py-2" type="submit"><i class="ti ti-device-floppy me-1"></i> Save</button></div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="publisherModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content ajax-form library-form" method="POST" action="{{ route('admin.library.publishers.store') }}">
                @csrf <input type="hidden" name="_method" value="POST">
                <div class="modal-header"><h5 class="modal-title">Publisher</h5><button class="btn-close" data-bs-dismiss="modal" type="button"></button></div>
                <div class="modal-body row g-3">
                    <div class="col-12"><label class="form-label required">Name</label><input class="form-control" name="name" required></div>
                    <div class="col-md-6"><label class="form-label">Contact</label><input class="form-control" name="contact"></div>
                    <div class="col-12"><label class="form-label">Address</label><textarea class="form-control" name="address" rows="2"></textarea></div>
                    <div class="col-md-6"><label class="form-label required">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                </div>
                <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal" type="button">Cancel</button><button class="btn btn-primary py-2" type="submit"><i class="ti ti-device-floppy me-1"></i> Save</button></div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="issueModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form class="modal-content ajax-form library-form" method="POST" action="{{ route('admin.library.issues.store') }}">
                @csrf <input type="hidden" name="_method" value="POST">
                <div class="modal-header"><h5 class="modal-title">Issue Book</h5><button class="btn-close" data-bs-dismiss="modal" type="button"></button></div>
                <div class="modal-body row g-3">
                    <div class="col-md-6"><label class="form-label required">Book</label><select class="form-select searchable-select" name="book_id" required data-placeholder="Search book..."><option value="">Select</option>@foreach($books as $b)<option value="{{ $b->id }}" data-available="{{ $b->available_copies }}">{{ $b->title }} @if($b->isbn)({{ $b->isbn }})@endif (Available: {{ $b->available_copies }})</option>@endforeach</select></div>
                    <div class="col-md-6"><label class="form-label required">Borrower Type</label><select class="form-select" name="issueable_type" id="borrowerType" required><option value="">Select</option><option value="student">Student</option><option value="teacher">Teacher</option></select></div>
                    <div class="col-md-12"><label class="form-label required">Borrower</label>
                        <select class="form-select searchable-select" name="issueable_id" id="borrowerSelect" required data-placeholder="Search borrower..." disabled>
                            <option value=""></option>
                        </select>
                        <div id="studentSearchWrap" style="display:none"><select class="form-select searchable-select" data-ajax-url="{{ route('admin.library.search.students') }}" data-placeholder="Search student..." id="studentSearch"></select></div>
                        <div id="teacherSearchWrap" style="display:none"><select class="form-select searchable-select" data-ajax-url="{{ route('admin.library.search.teachers') }}" data-placeholder="Search teacher..." id="teacherSearch"></select></div>
                    </div>
                    <div class="col-md-6"><label class="form-label">Issue Date</label><input class="form-control" type="date" name="issue_date" id="issueDate"></div>
                    <div class="col-md-6"><label class="form-label">Due Date</label><input class="form-control" type="date" name="due_date" id="dueDate"></div>
                    <div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="2"></textarea></div>
                </div>
                <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal" type="button">Cancel</button><button class="btn btn-primary py-2" type="submit"><i class="ti ti-device-floppy me-1"></i> Issue Book</button></div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="returnModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content ajax-form library-form" method="POST" id="returnForm">
                @csrf <input type="hidden" name="_method" value="PUT">
                <div class="modal-header"><h5 class="modal-title">Return Book</h5><button class="btn-close" data-bs-dismiss="modal" type="button"></button></div>
                <div class="modal-body row g-3">
                    <div class="col-md-6"><label class="form-label">Return Date</label><input class="form-control" type="date" name="return_date"></div>
                    <div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="2"></textarea></div>
                    <div class="col-12" id="finePreview" style="display:none">
                        <div class="alert alert-info mb-0">
                            <strong>Fine Amount:</strong> <span id="fineAmount">₹ 0.00</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal" type="button">Cancel</button><button class="btn btn-success py-2" type="submit"><i class="ti ti-arrow-back-up me-1"></i> Return Book</button></div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="fineSettingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content ajax-form library-form" method="POST" action="{{ route('admin.library.fine-settings.store') }}">
                @csrf <input type="hidden" name="_method" value="POST">
                <div class="modal-header"><h5 class="modal-title">Fine Configuration</h5><button class="btn-close" data-bs-dismiss="modal" type="button"></button></div>
                <div class="modal-body row g-3">
                    <div class="col-md-6"><label class="form-label required">Fine Per Day (₹)</label><input class="form-control" type="number" name="fine_per_day" step="0.01" min="0" value="1" required></div>
                    <div class="col-md-6"><label class="form-label">Max Fine (₹)</label><input class="form-control" type="number" name="max_fine" step="0.01" min="0"></div>
                    <div class="col-md-6"><label class="form-label">Grace Period (Days)</label><input class="form-control" type="number" name="grace_period_days" min="0" value="0"></div>
                    <div class="col-md-6"><label class="form-label required">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                </div>
                <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal" type="button">Cancel</button><button class="btn btn-primary py-2" type="submit"><i class="ti ti-device-floppy me-1"></i> Save</button></div>
            </form>
        </div>
    </div>
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
                book: {modal: '#bookModal', store: '{{ route('admin.library.books.store') }}', table: tables.books},
                category: {modal: '#categoryModal', store: '{{ route('admin.library.categories.store') }}', table: tables.categories},
                author: {modal: '#authorModal', store: '{{ route('admin.library.authors.store') }}', table: tables.authors},
                publisher: {modal: '#publisherModal', store: '{{ route('admin.library.publishers.store') }}', table: tables.publishers},
                'fine-setting': {modal: '#fineSettingModal', store: '{{ route('admin.library.fine-settings.store') }}', table: tables.fineSettings}
            };

            $('.open-modal').on('click', function () {
                const modalId = $(this).data('modal');
                const form = $(`${modalId} form`);
                const setup = Object.values(config).find(item => item.modal === modalId);
                form[0].reset();
                form.attr('action', setup.store);
                form.find('[name="_method"]').val('POST');
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback.dynamic').remove();
                if (modalId === '#issueModal') {
                    const today = new Date().toISOString().split('T')[0];
                    const due = new Date(Date.now() + 14 * 86400000).toISOString().split('T')[0];
                    $('#issueDate').val(today);
                    $('#dueDate').val(due);
                    $('#borrowerSelect').val('').trigger('change').prop('disabled', true);
                    $('#borrowerType').val('');
                }
                bootstrap.Modal.getOrCreateInstance(document.querySelector(modalId)).show();
            });

            $('.library-form').on('erp:success', function () {
                bootstrap.Modal.getInstance($(this).closest('.modal')[0]).hide();
                Object.values(tables).forEach(table => table.ajax.reload(null, false));
            });

            $(document).on('click', '.edit-library', function () {
                const type = $(this).data('type');
                const setup = config[type];
                const form = $(`${setup.modal} form`);
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
                    bootstrap.Modal.getOrCreateInstance(document.querySelector(setup.modal)).show();
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
                bootstrap.Modal.getOrCreateInstance(document.getElementById('returnModal')).show();
            });

            $('#borrowerType').on('change', function () {
                const type = $(this).val();
                $('#borrowerSelect').val('').trigger('change');
                if (type === 'student') {
                    const $el = $('#studentSearch');
                    if (!$el.hasClass('searchable-select-initialized')) {
                        $el.addClass('searchable-select-initialized');
                        App.initSelect2($el, {ajax: true});
                    }
                    const student = $el.val();
                    if (student) {
                        $('#borrowerSelect').append(new Option($el.find('option:selected').text(), student, true, true)).val(student).trigger('change');
                    }
                    $('#studentSearchWrap').show();
                    $('#teacherSearchWrap').hide();
                } else if (type === 'teacher') {
                    const $el = $('#teacherSearch');
                    if (!$el.hasClass('searchable-select-initialized')) {
                        $el.addClass('searchable-select-initialized');
                        App.initSelect2($el, {ajax: true});
                    }
                    const teacher = $el.val();
                    if (teacher) {
                        $('#borrowerSelect').append(new Option($el.find('option:selected').text(), teacher, true, true)).val(teacher).trigger('change');
                    }
                    $('#studentSearchWrap').hide();
                    $('#teacherSearchWrap').show();
                } else {
                    $('#studentSearchWrap').hide();
                    $('#teacherSearchWrap').hide();
                    $('#borrowerSelect').prop('disabled', true);
                }
            });

            $(document).on('change', '#studentSearch, #teacherSearch', function () {
                const val = $(this).val();
                const text = $(this).find('option:selected').text();
                const $bs = $('#borrowerSelect');
                if (val) {
                    if (!$bs.find('option[value="'+val+'"]').length) {
                        $bs.append(new Option(text, val, true, true));
                    }
                    $bs.val(val).trigger('change').prop('disabled', false);
                } else {
                    $bs.val('').trigger('change').prop('disabled', true);
                }
            });
        })(); });
    </script>
@endpush
