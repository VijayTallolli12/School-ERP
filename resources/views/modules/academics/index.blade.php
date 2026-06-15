@extends('layouts.admin')

@section('title', 'Academics')
@section('page-title', 'Academic Management')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Academics</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs" id="academicTabs" role="tablist">
                @foreach ([
                    'years' => 'ti-calendar',
                    'classes' => 'ti-school',
                    'sections' => 'ti-layout-columns',
                    'classSections' => 'ti-columns',
                    'subjects' => 'ti-book',
                    'classSubjects' => 'ti-book-2',
                ] as $id => $icon)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link @if($loop->first) active @endif" data-bs-toggle="tab" data-bs-target="#{{ $id }}Pane" type="button"><i class="{{ $icon }} me-1"></i>{{ str_replace('classSections', 'Class Sections', str_replace('classSubjects', 'Class Subjects', ucfirst($id))) }}</button>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="yearsPane">
                    <div class="d-flex mb-3">
                        @can('academics.create')
                            <button class="btn btn-primary btn-sm ms-auto open-modal" data-modal="#yearModal">
                                <i class="ti ti-plus me-1"></i> Add Academic Year
                            </button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="yearsTable">
                        <thead><tr><th>ID</th><th>Name</th><th>Start</th><th>End</th><th>Active</th><th>Status</th><th>Terms</th><th width="120">Actions</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="classesPane">
                    <div class="d-flex mb-3">
                        @can('academics.create')
                            <button class="btn btn-primary btn-sm ms-auto open-modal" data-modal="#classModal">
                                <i class="ti ti-plus me-1"></i> Add Class
                            </button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="classesTable">
                        <thead><tr><th>ID</th><th>Name</th><th>Code</th><th>Order</th><th>Status</th><th>Sections</th><th>Subjects</th><th width="120">Actions</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="sectionsPane">
                    <div class="d-flex mb-3">
                        @can('academics.create')
                            <button class="btn btn-primary btn-sm ms-auto open-modal" data-modal="#sectionModal">
                                <i class="ti ti-plus me-1"></i> Add Section
                            </button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="sectionsTable">
                        <thead><tr><th>ID</th><th>Name</th><th>Code</th><th>Capacity</th><th>Status</th><th>Classes</th><th width="120">Actions</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="classSectionsPane">
                    <div class="d-flex mb-3">
                        @can('academics.create')
                            <button class="btn btn-primary btn-sm ms-auto open-modal" data-modal="#classSectionModal">
                                <i class="ti ti-plus me-1"></i> Add Class Section
                            </button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="classSectionsTable">
                        <thead><tr><th>ID</th><th>Class</th><th>Section</th><th>Class Teacher</th><th>Status</th><th width="120">Actions</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="subjectsPane">
                    <div class="d-flex mb-3">
                        @can('academics.create')
                            <button class="btn btn-primary btn-sm ms-auto open-modal" data-modal="#subjectModal">
                                <i class="ti ti-plus me-1"></i> Add Subject
                            </button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="subjectsTable">
                        <thead><tr><th>ID</th><th>Name</th><th>Code</th><th>Type</th><th>Credit Hours</th><th>Status</th><th>Classes</th><th width="120">Actions</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="classSubjectsPane">
                    <div class="d-flex mb-3">
                        @can('academics.create')
                            <button class="btn btn-primary btn-sm ms-auto open-modal" data-modal="#classSubjectModal">
                                <i class="ti ti-plus me-1"></i> Assign Subject
                            </button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="classSubjectsTable">
                        <thead><tr><th>ID</th><th>Academic Year</th><th>Class</th><th>Subject</th><th>Teacher</th><th>Weekly Periods</th><th>Status</th><th width="90">Actions</th></tr></thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    <div class="modal fade" id="yearModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content ajax-form academic-form" method="POST" action="{{ route('admin.academics.academic-years.store') }}">
                @csrf <input type="hidden" name="_method" value="POST">
                <div class="modal-header"><h5 class="modal-title">Academic Year</h5><button class="btn-close" data-bs-dismiss="modal" type="button"></button></div>
                <div class="modal-body row g-3">
                    <div class="col-12"><label class="form-label required">Name</label><input class="form-control" name="name" required></div>
                    <div class="col-md-6"><label class="form-label required">Starts On</label><input class="form-control" type="date" name="starts_on" required></div>
                    <div class="col-md-6"><label class="form-label required">Ends On</label><input class="form-control" type="date" name="ends_on" required></div>
                    <div class="col-md-6"><label class="form-label required">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option><option value="archived">Archived</option></select></div>
                    <div class="col-md-6 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActiveYear"><label class="form-check-label" for="isActiveYear">Current active year</label></div></div>
                </div>
                <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal" type="button">Cancel</button><button class="btn btn-primary py-2" type="submit"><i class="ti ti-device-floppy me-1"></i> Save</button></div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="classModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content ajax-form academic-form" method="POST" action="{{ route('admin.academics.classes.store') }}">
                @csrf <input type="hidden" name="_method" value="POST">
                <div class="modal-header"><h5 class="modal-title">Class</h5><button class="btn-close" data-bs-dismiss="modal" type="button"></button></div>
                <div class="modal-body row g-3">
                    <div class="col-md-6"><label class="form-label required">Name</label><input class="form-control" name="name" required></div>
                    <div class="col-md-6"><label class="form-label required">Code</label><input class="form-control" name="code" required></div>
                    <div class="col-md-6"><label class="form-label">Sort Order</label><input class="form-control" type="number" name="sort_order" min="0" value="0"></div>
                    <div class="col-md-6"><label class="form-label required">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                </div>
                <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal" type="button">Cancel</button><button class="btn btn-primary py-2" type="submit"><i class="ti ti-device-floppy me-1"></i> Save</button></div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="sectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content ajax-form academic-form" method="POST" action="{{ route('admin.academics.sections.store') }}">
                @csrf <input type="hidden" name="_method" value="POST">
                <div class="modal-header"><h5 class="modal-title">Section</h5><button class="btn-close" data-bs-dismiss="modal" type="button"></button></div>
                <div class="modal-body row g-3">
                    <div class="col-md-6"><label class="form-label required">Name</label><input class="form-control" name="name" required></div>
                    <div class="col-md-6"><label class="form-label required">Code</label><input class="form-control" name="code" required></div>
                    <div class="col-md-6"><label class="form-label">Capacity</label><input class="form-control" type="number" name="capacity" min="1"></div>
                    <div class="col-md-6"><label class="form-label required">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                </div>
                <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal" type="button">Cancel</button><button class="btn btn-primary py-2" type="submit"><i class="ti ti-device-floppy me-1"></i> Save</button></div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="classSectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content ajax-form academic-form" method="POST" action="{{ route('admin.academics.class-sections.store') }}">
                @csrf <input type="hidden" name="_method" value="POST">
                <div class="modal-header"><h5 class="modal-title">Class Section</h5><button class="btn-close" data-bs-dismiss="modal" type="button"></button></div>
                <div class="modal-body row g-3">
                    <div class="col-md-6"><label class="form-label required">Class</label><select class="form-select" name="class_id" required><option value="">Select</option>@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->name }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label class="form-label required">Section</label><select class="form-select" name="section_id" required><option value="">Select</option>@foreach($sections as $section)<option value="{{ $section->id }}">{{ $section->name }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label class="form-label">Class Teacher</label><select class="form-select" name="class_teacher_id"><option value="">Unassigned</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->name }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label class="form-label required">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                </div>
                <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal" type="button">Cancel</button><button class="btn btn-primary py-2" type="submit"><i class="ti ti-device-floppy me-1"></i> Save</button></div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="subjectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content ajax-form academic-form" method="POST" action="{{ route('admin.academics.subjects.store') }}">
                @csrf <input type="hidden" name="_method" value="POST">
                <div class="modal-header"><h5 class="modal-title">Subject</h5><button class="btn-close" data-bs-dismiss="modal" type="button"></button></div>
                <div class="modal-body row g-3">
                    <div class="col-md-6"><label class="form-label required">Name</label><input class="form-control" name="name" required></div>
                    <div class="col-md-6"><label class="form-label required">Code</label><input class="form-control" name="code" required></div>
                    <div class="col-md-6"><label class="form-label required">Type</label><select class="form-select" name="type"><option value="core">Core</option><option value="elective">Elective</option><option value="optional">Optional</option><option value="co_scholastic">Co-scholastic</option></select></div>
                    <div class="col-md-6"><label class="form-label">Credit Hours</label><input class="form-control" type="number" name="credit_hours" min="0" value="0"></div>
                    <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3"></textarea></div>
                    <div class="col-md-6"><label class="form-label required">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                </div>
                <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal" type="button">Cancel</button><button class="btn btn-primary py-2" type="submit"><i class="ti ti-device-floppy me-1"></i> Save</button></div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="classSubjectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content ajax-form academic-form" method="POST" action="{{ route('admin.academics.class-subjects.store') }}">
                @csrf <input type="hidden" name="_method" value="POST">
                <div class="modal-header"><h5 class="modal-title">Assign Subject</h5><button class="btn-close" data-bs-dismiss="modal" type="button"></button></div>
                <div class="modal-body row g-3">
                    <div class="col-md-6"><label class="form-label required">Academic Year</label><select class="form-select" name="academic_year_id" required>@foreach($academicYears as $year)<option value="{{ $year->id }}" @selected($year->is_active)>{{ $year->name }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label class="form-label required">Class</label><select class="form-select" name="class_id" required>@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->name }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label class="form-label required">Subject</label><select class="form-select" name="subject_id" required>@foreach($subjects as $subject)<option value="{{ $subject->id }}">{{ $subject->name }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label class="form-label">Teacher</label><select class="form-select" name="teacher_id"><option value="">Unassigned</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->name }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label class="form-label">Weekly Periods</label><input class="form-control" type="number" name="weekly_periods" min="0" value="0"></div>
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
                years: $('#yearsTable').DataTable({processing: true, serverSide: true, responsive: true, ajax: '{{ route('admin.academics.academic-years.data') }}', columns: [
                    {data:'id'}, {data:'name'}, {data:'starts_on'}, {data:'ends_on'}, {data:'active_badge', orderable:false, searchable:false}, {data:'status'}, {data:'terms_count', searchable:false}, {data:'actions', orderable:false, searchable:false}
                ]}),
                classes: $('#classesTable').DataTable({processing: true, serverSide: true, responsive: true, ajax: '{{ route('admin.academics.classes.data') }}', columns: [
                    {data:'id'}, {data:'name'}, {data:'code'}, {data:'sort_order'}, {data:'status'}, {data:'sections_count', searchable:false}, {data:'class_subjects_count', searchable:false}, {data:'actions', orderable:false, searchable:false}
                ]}),
                sections: $('#sectionsTable').DataTable({processing: true, serverSide: true, responsive: true, ajax: '{{ route('admin.academics.sections.data') }}', columns: [
                    {data:'id'}, {data:'name'}, {data:'code'}, {data:'capacity'}, {data:'status'}, {data:'classes_count', searchable:false}, {data:'actions', orderable:false, searchable:false}
                ]}),
                classSections: $('#classSectionsTable').DataTable({processing: true, serverSide: true, responsive: true, ajax: '{{ route('admin.academics.class-sections.data') }}', columns: [
                    {data:'id'}, {data:'class_name'}, {data:'section_name'}, {data:'teacher_name', orderable:false, searchable:false}, {data:'status'}, {data:'actions', orderable:false, searchable:false}
                ]}),
                subjects: $('#subjectsTable').DataTable({processing: true, serverSide: true, responsive: true, ajax: '{{ route('admin.academics.subjects.data') }}', columns: [
                    {data:'id'}, {data:'name'}, {data:'code'}, {data:'type_label', name:'type'}, {data:'credit_hours'}, {data:'status'}, {data:'class_subjects_count', searchable:false}, {data:'actions', orderable:false, searchable:false}
                ]}),
                classSubjects: $('#classSubjectsTable').DataTable({processing: true, serverSide: true, responsive: true, ajax: '{{ route('admin.academics.class-subjects.data') }}', columns: [
                    {data:'id', name:'class_subjects.id'}, {data:'academic_year', name:'academicYear.name'}, {data:'class_name', name:'schoolClass.name'}, {data:'subject_name', name:'subject.name'}, {data:'teacher_name', orderable:false}, {data:'weekly_periods', name:'class_subjects.weekly_periods'}, {data:'status', name:'class_subjects.status'}, {data:'actions', orderable:false, searchable:false}
                ]})
            };

            const classSubjectStoreUrl = '{{ route('admin.academics.class-subjects.store') }}';
            const config = {
                'academic-year': {modal: '#yearModal', store: '{{ route('admin.academics.academic-years.store') }}', table: tables.years},
                class: {modal: '#classModal', store: '{{ route('admin.academics.classes.store') }}', table: tables.classes},
                section: {modal: '#sectionModal', store: '{{ route('admin.academics.sections.store') }}', table: tables.sections},
                'class-section': {modal: '#classSectionModal', store: '{{ route('admin.academics.class-sections.store') }}', table: tables.classSections},
                subject: {modal: '#subjectModal', store: '{{ route('admin.academics.subjects.store') }}', table: tables.subjects},
                'class-subject': {modal: '#classSubjectModal', store: classSubjectStoreUrl, table: tables.classSubjects}
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
                bootstrap.Modal.getOrCreateInstance(document.querySelector(modalId)).show();
            });

            $('.academic-form').on('erp:success', function () {
                bootstrap.Modal.getInstance($(this).closest('.modal')[0]).hide();
                Object.values(tables).forEach(table => table.ajax.reload(null, false));
            });

            $(document).on('click', '.edit-academic', function () {
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

            $(document).on('click', '.delete-academic', function () {
                App.confirmDelete({
                    url: $(this).data('url'),
                    onSuccess: () => Object.values(tables).forEach(table => table.ajax.reload(null, false))
                });
            });
        });
    </script>
@endpush
