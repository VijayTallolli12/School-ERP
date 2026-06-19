@extends('layouts.admin')

@section('title', 'Library Reports')
@section('page-title', 'Library Reports')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.library.index') }}">Library</a></li>
    <li class="breadcrumb-item active">Reports</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs" id="reportTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#inventoryPane" type="button"><i class="ti ti-book me-1"></i>Books Inventory</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#issuedPane" type="button"><i class="ti ti-arrow-up me-1"></i>Issued Books</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#overduePane" type="button"><i class="ti ti-alert-triangle me-1"></i>Overdue Books</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#finePane" type="button"><i class="ti ti-coin me-1"></i>Fine Collection</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#studentHistPane" type="button"><i class="ti ti-users me-1"></i>Student History</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#teacherHistPane" type="button"><i class="ti ti-school me-1"></i>Teacher History</button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="inventoryPane">
                    <div class="row g-2 mb-3">
                        <div class="col-auto"><select class="form-select form-select-sm" id="invFilterCategory"><option value="">All Categories</option>@foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach</select></div>
                        <div class="col-auto"><select class="form-select form-select-sm" id="invFilterStatus"><option value="">All Status</option><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                        <div class="col-auto"><button class="btn btn-sm btn-outline-primary" id="invFilterBtn"><i class="ti ti-filter me-1"></i>Filter</button></div>
                        <div class="col-auto ms-auto">
                            <a class="btn btn-sm btn-outline-success" href="{{ route('admin.library.reports.export.excel', 'books_inventory') }}" id="invExcel"><i class="ti ti-file-spreadsheet me-1"></i>Excel</a>
                            <a class="btn btn-sm btn-outline-danger" href="{{ route('admin.library.reports.export.pdf', 'books_inventory') }}" id="invPdf"><i class="ti ti-file-pdf me-1"></i>PDF</a>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.library.reports.print', 'books_inventory') }}" target="_blank"><i class="ti ti-printer me-1"></i>Print</a>
                        </div>
                    </div>
                    <table class="table table-striped table-bordered w-100" id="inventoryTable">
                        <thead><tr><th>#</th><th>ISBN</th><th>Title</th><th>Category</th><th>Author</th><th>Publisher</th><th>Language</th><th>Qty</th><th>Available</th><th>Status</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="issuedPane">
                    <div class="row g-2 mb-3">
                        <div class="col-auto"><select class="form-select form-select-sm" id="issFilterBook"><option value="">All Books</option>@foreach($books as $b)<option value="{{ $b->id }}">{{ $b->title }}</option>@endforeach</select></div>
                        <div class="col-auto"><select class="form-select form-select-sm" id="issFilterType"><option value="">All</option><option value="student">Student</option><option value="teacher">Teacher</option></select></div>
                        <div class="col-auto"><button class="btn btn-sm btn-outline-primary" id="issFilterBtn"><i class="ti ti-filter me-1"></i>Filter</button></div>
                        <div class="col-auto ms-auto">
                            <a class="btn btn-sm btn-outline-success" href="{{ route('admin.library.reports.export.excel', 'issued_books') }}" id="issExcel"><i class="ti ti-file-spreadsheet me-1"></i>Excel</a>
                            <a class="btn btn-sm btn-outline-danger" href="{{ route('admin.library.reports.export.pdf', 'issued_books') }}" id="issPdf"><i class="ti ti-file-pdf me-1"></i>PDF</a>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.library.reports.print', 'issued_books') }}" target="_blank"><i class="ti ti-printer me-1"></i>Print</a>
                        </div>
                    </div>
                    <table class="table table-striped table-bordered w-100" id="issuedTable">
                        <thead><tr><th>#</th><th>Book</th><th>Borrower</th><th>Type</th><th>Issue Date</th><th>Due Date</th><th>Overdue Days</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="overduePane">
                    <div class="row g-2 mb-3">
                        <div class="col-auto"><select class="form-select form-select-sm" id="ovFilterBook"><option value="">All Books</option>@foreach($books as $b)<option value="{{ $b->id }}">{{ $b->title }}</option>@endforeach</select></div>
                        <div class="col-auto"><button class="btn btn-sm btn-outline-primary" id="ovFilterBtn"><i class="ti ti-filter me-1"></i>Filter</button></div>
                        <div class="col-auto ms-auto">
                            <a class="btn btn-sm btn-outline-success" href="{{ route('admin.library.reports.export.excel', 'overdue_books') }}" id="ovExcel"><i class="ti ti-file-spreadsheet me-1"></i>Excel</a>
                            <a class="btn btn-sm btn-outline-danger" href="{{ route('admin.library.reports.export.pdf', 'overdue_books') }}" id="ovPdf"><i class="ti ti-file-pdf me-1"></i>PDF</a>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.library.reports.print', 'overdue_books') }}" target="_blank"><i class="ti ti-printer me-1"></i>Print</a>
                        </div>
                    </div>
                    <table class="table table-striped table-bordered w-100" id="overdueTable">
                        <thead><tr><th>#</th><th>Book</th><th>Borrower</th><th>Issue Date</th><th>Due Date</th><th>Overdue Days</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="finePane">
                    <div class="row g-2 mb-3">
                        <div class="col-auto"><input class="form-control form-control-sm" type="date" id="fineFromDate"></div>
                        <div class="col-auto"><input class="form-control form-control-sm" type="date" id="fineToDate"></div>
                        <div class="col-auto"><button class="btn btn-sm btn-outline-primary" id="fineFilterBtn"><i class="ti ti-filter me-1"></i>Filter</button></div>
                        <div class="col-auto ms-auto">
                            <a class="btn btn-sm btn-outline-success" href="{{ route('admin.library.reports.export.excel', 'fine_collection') }}" id="fineExcel"><i class="ti ti-file-spreadsheet me-1"></i>Excel</a>
                            <a class="btn btn-sm btn-outline-danger" href="{{ route('admin.library.reports.export.pdf', 'fine_collection') }}" id="finePdf"><i class="ti ti-file-pdf me-1"></i>PDF</a>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.library.reports.print', 'fine_collection') }}" target="_blank"><i class="ti ti-printer me-1"></i>Print</a>
                        </div>
                    </div>
                    <table class="table table-striped table-bordered w-100" id="fineTable">
                        <thead><tr><th>#</th><th>Book</th><th>Borrower</th><th>Return Date</th><th>Fine Amount</th><th>Status</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="studentHistPane">
                    <div class="row g-2 mb-3">
                        <div class="col-auto"><select class="form-select form-select-sm" id="shFilterStudent"><option value="">All Students</option>@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->full_name }} ({{ $s->admission_no }})</option>@endforeach</select></div>
                        <div class="col-auto"><button class="btn btn-sm btn-outline-primary" id="shFilterBtn"><i class="ti ti-filter me-1"></i>Filter</button></div>
                        <div class="col-auto ms-auto">
                            <a class="btn btn-sm btn-outline-success" href="{{ route('admin.library.reports.export.excel', 'student_history') }}" id="shExcel"><i class="ti ti-file-spreadsheet me-1"></i>Excel</a>
                            <a class="btn btn-sm btn-outline-danger" href="{{ route('admin.library.reports.export.pdf', 'student_history') }}" id="shPdf"><i class="ti ti-file-pdf me-1"></i>PDF</a>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.library.reports.print', 'student_history') }}" target="_blank"><i class="ti ti-printer me-1"></i>Print</a>
                        </div>
                    </div>
                    <table class="table table-striped table-bordered w-100" id="studentHistTable">
                        <thead><tr><th>#</th><th>Student</th><th>Book</th><th>ISBN</th><th>Issue Date</th><th>Due Date</th><th>Return Date</th><th>Fine</th><th>Status</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="teacherHistPane">
                    <div class="row g-2 mb-3">
                        <div class="col-auto"><select class="form-select form-select-sm" id="thFilterTeacher"><option value="">All Teachers</option>@foreach($teachers as $t)<option value="{{ $t->id }}">{{ $t->full_name }}</option>@endforeach</select></div>
                        <div class="col-auto"><button class="btn btn-sm btn-outline-primary" id="thFilterBtn"><i class="ti ti-filter me-1"></i>Filter</button></div>
                        <div class="col-auto ms-auto">
                            <a class="btn btn-sm btn-outline-success" href="{{ route('admin.library.reports.export.excel', 'teacher_history') }}" id="thExcel"><i class="ti ti-file-spreadsheet me-1"></i>Excel</a>
                            <a class="btn btn-sm btn-outline-danger" href="{{ route('admin.library.reports.export.pdf', 'teacher_history') }}" id="thPdf"><i class="ti ti-file-pdf me-1"></i>PDF</a>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.library.reports.print', 'teacher_history') }}" target="_blank"><i class="ti ti-printer me-1"></i>Print</a>
                        </div>
                    </div>
                    <table class="table table-striped table-bordered w-100" id="teacherHistTable">
                        <thead><tr><th>#</th><th>Teacher</th><th>Book</th><th>ISBN</th><th>Issue Date</th><th>Due Date</th><th>Return Date</th><th>Fine</th><th>Status</th></tr></thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => { (async () => { const DataTable = await window.lazyDT();

            const baseExcel = '{{ route('admin.library.reports.export.excel', 'REPLACE') }}';
            const basePdf = '{{ route('admin.library.reports.export.pdf', 'REPLACE') }}';
            const basePrint = '{{ route('admin.library.reports.print', 'REPLACE') }}';

            function updateExportLinks(prefix, reportKey, params) {
                const qs = $.param(params);
                $(`#${prefix}Excel`).attr('href', baseExcel.replace('REPLACE', reportKey) + '?' + qs);
                $(`#${prefix}Pdf`).attr('href', basePdf.replace('REPLACE', reportKey) + '?' + qs);
                $(`#${prefix}Print`).attr('href', basePrint.replace('REPLACE', reportKey) + '?' + qs);
            }

            const invTable = $('#inventoryTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: {url: '{{ route('admin.library.reports.books-inventory.data') }}', data: d => { d.category_id = $('#invFilterCategory').val(); d.status = $('#invFilterStatus').val(); }}, columns: [
                {data:'id'}, {data:'isbn'}, {data:'title'}, {data:'category_name', orderable:false}, {data:'author_name', orderable:false}, {data:'publisher_name', orderable:false}, {data:'language'}, {data:'quantity'}, {data:'available_copies'}, {data:'status'}
            ]});
            $('#invFilterBtn').on('click', () => { invTable.ajax.reload(); updateExportLinks('inv', 'books_inventory', {category_id: $('#invFilterCategory').val(), status: $('#invFilterStatus').val()}); });

            const issTable = $('#issuedTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: {url: '{{ route('admin.library.reports.issued-books.data') }}', data: d => { d.book_id = $('#issFilterBook').val(); d.borrower_type = $('#issFilterType').val(); }}, columns: [
                {data:'id'}, {data:'book_title', orderable:false}, {data:'borrower', orderable:false}, {data:'issueable_type'}, {data:'issue_date'}, {data:'due_date'}, {data:'overdue_days', searchable:false}
            ]});
            $('#issFilterBtn').on('click', () => { issTable.ajax.reload(); updateExportLinks('iss', 'issued_books', {book_id: $('#issFilterBook').val(), borrower_type: $('#issFilterType').val()}); });

            const ovTable = $('#overdueTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: {url: '{{ route('admin.library.reports.overdue-books.data') }}', data: d => { d.book_id = $('#ovFilterBook').val(); }}, columns: [
                {data:'id'}, {data:'book_title', orderable:false}, {data:'borrower', orderable:false}, {data:'issue_date'}, {data:'due_date'}, {data:'overdue_days', searchable:false}
            ]});
            $('#ovFilterBtn').on('click', () => { ovTable.ajax.reload(); updateExportLinks('ov', 'overdue_books', {book_id: $('#ovFilterBook').val()}); });

            const fineTable = $('#fineTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: {url: '{{ route('admin.library.reports.fine-collection.data') }}', data: d => { d.from_date = $('#fineFromDate').val(); d.to_date = $('#fineToDate').val(); }}, columns: [
                {data:'id'}, {data:'book_title', orderable:false}, {data:'borrower', orderable:false}, {data:'return_date', orderable:false}, {data:'fine_amount', orderable:false}, {data:'fine_paid', orderable:false}
            ]});
            $('#fineFilterBtn').on('click', () => { fineTable.ajax.reload(); updateExportLinks('fine', 'fine_collection', {from_date: $('#fineFromDate').val(), to_date: $('#fineToDate').val()}); });

            const shTable = $('#studentHistTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: {url: '{{ route('admin.library.reports.student-history.data') }}', data: d => { d.student_id = $('#shFilterStudent').val(); }}, columns: [
                {data:'id'}, {data:'student'}, {data:'book_title', orderable:false}, {data:'isbn', orderable:false}, {data:'issue_date'}, {data:'due_date'}, {data:'return_date', orderable:false}, {data:'fine_amount', orderable:false}, {data:'status'}
            ]});
            $('#shFilterBtn').on('click', () => { shTable.ajax.reload(); updateExportLinks('sh', 'student_history', {student_id: $('#shFilterStudent').val()}); });

            const thTable = $('#teacherHistTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: {url: '{{ route('admin.library.reports.teacher-history.data') }}', data: d => { d.teacher_id = $('#thFilterTeacher').val(); }}, columns: [
                {data:'id'}, {data:'teacher'}, {data:'book_title', orderable:false}, {data:'isbn', orderable:false}, {data:'issue_date'}, {data:'due_date'}, {data:'return_date', orderable:false}, {data:'fine_amount', orderable:false}, {data:'status'}
            ]});
            $('#thFilterBtn').on('click', () => { thTable.ajax.reload(); updateExportLinks('th', 'teacher_history', {teacher_id: $('#thFilterTeacher').val()}); });

            initTabPersistence('#reportTabs');
        })(); });
    </script>
@endpush
