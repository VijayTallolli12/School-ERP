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
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#inventoryPane" type="button">Books Inventory</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#issuedPane" type="button">Issued Books</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#overduePane" type="button">Overdue Books</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#finePane" type="button">Fine Collection</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#studentHistPane" type="button">Student History</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#teacherHistPane" type="button">Teacher History</button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="inventoryPane">
                    <div class="row g-2 mb-3">
                        <div class="col-auto"><select class="form-select form-select-sm" id="invFilterCategory"><option value="">All Categories</option>@foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach</select></div>
                        <div class="col-auto"><select class="form-select form-select-sm" id="invFilterStatus"><option value="">All Status</option><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                        <div class="col-auto"><button class="btn  btn-outline-primary h-100" id="invFilterBtn">Filter</button></div>
                        <div class="col-auto ms-auto">
                            <x-erp.export-buttons excelUrl="{{ route('admin.library.reports.export.excel', 'books_inventory') }}" pdfUrl="{{ route('admin.library.reports.export.pdf', 'books_inventory') }}" printUrl="{{ route('admin.library.reports.print', 'books_inventory') }}" excelId="invExcel" pdfId="invPdf" printId="invPrint" />
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
                        <div class="col-auto"><button class="btn  btn-outline-primary h-100" id="issFilterBtn">Filter</button></div>
                        <div class="col-auto ms-auto">
                            <x-erp.export-buttons excelUrl="{{ route('admin.library.reports.export.excel', 'issued_books') }}" pdfUrl="{{ route('admin.library.reports.export.pdf', 'issued_books') }}" printUrl="{{ route('admin.library.reports.print', 'issued_books') }}" excelId="issExcel" pdfId="issPdf" printId="issPrint" />
                        </div>
                    </div>
                    <table class="table table-striped table-bordered w-100" id="issuedTable">
                        <thead><tr><th>#</th><th>Book</th><th>Borrower</th><th>Type</th><th>Issue Date</th><th>Due Date</th><th>Overdue Days</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="overduePane">
                    <div class="row g-2 mb-3">
                        <div class="col-auto"><select class="form-select form-select-sm" id="ovFilterBook"><option value="">All Books</option>@foreach($books as $b)<option value="{{ $b->id }}">{{ $b->title }}</option>@endforeach</select></div>
                        <div class="col-auto"><button class="btn  btn-outline-primary h-100" id="ovFilterBtn">Filter</button></div>
                        <div class="col-auto ms-auto">
                            <x-erp.export-buttons excelUrl="{{ route('admin.library.reports.export.excel', 'overdue_books') }}" pdfUrl="{{ route('admin.library.reports.export.pdf', 'overdue_books') }}" printUrl="{{ route('admin.library.reports.print', 'overdue_books') }}" excelId="ovExcel" pdfId="ovPdf" printId="ovPrint" />
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
                        <div class="col-auto"><button class="btn  btn-outline-primary h-100" id="fineFilterBtn">Filter</button></div>
                        <div class="col-auto ms-auto">
                            <x-erp.export-buttons excelUrl="{{ route('admin.library.reports.export.excel', 'fine_collection') }}" pdfUrl="{{ route('admin.library.reports.export.pdf', 'fine_collection') }}" printUrl="{{ route('admin.library.reports.print', 'fine_collection') }}" excelId="fineExcel" pdfId="finePdf" printId="finePrint" />
                        </div>
                    </div>
                    <table class="table table-striped table-bordered w-100" id="fineTable">
                        <thead><tr><th>#</th><th>Book</th><th>Borrower</th><th>Return Date</th><th>Fine Amount</th><th>Status</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="studentHistPane">
                    <div class="row g-2 mb-3">
                        <div class="col-auto"><select class="form-select form-select-sm" id="shFilterStudent"><option value="">All Students</option>@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->full_name }} ({{ $s->admission_no }})</option>@endforeach</select></div>
                        <div class="col-auto"><button class="btn  btn-outline-primary h-100" id="shFilterBtn">Filter</button></div>
                        <div class="col-auto ms-auto">
                            <x-erp.export-buttons excelUrl="{{ route('admin.library.reports.export.excel', 'student_history') }}" pdfUrl="{{ route('admin.library.reports.export.pdf', 'student_history') }}" printUrl="{{ route('admin.library.reports.print', 'student_history') }}" excelId="shExcel" pdfId="shPdf" printId="shPrint" />
                        </div>
                    </div>
                    <table class="table table-striped table-bordered w-100" id="studentHistTable">
                        <thead><tr><th>#</th><th>Student</th><th>Book</th><th>ISBN</th><th>Issue Date</th><th>Due Date</th><th>Return Date</th><th>Fine</th><th>Status</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="teacherHistPane">
                    <div class="row g-2 mb-3">
                        <div class="col-auto"><select class="form-select form-select-sm" id="thFilterTeacher"><option value="">All Teachers</option>@foreach($teachers as $t)<option value="{{ $t->id }}">{{ $t->full_name }}</option>@endforeach</select></div>
                        <div class="col-auto"><button class="btn  btn-outline-primary h-100" id="thFilterBtn">Filter</button></div>
                        <div class="col-auto ms-auto">
                            <x-erp.export-buttons excelUrl="{{ route('admin.library.reports.export.excel', 'teacher_history') }}" pdfUrl="{{ route('admin.library.reports.export.pdf', 'teacher_history') }}" printUrl="{{ route('admin.library.reports.print', 'teacher_history') }}" excelId="thExcel" pdfId="thPdf" printId="thPrint" />
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
