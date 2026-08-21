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
                        <button class="nav-link @if($loop->first) active @endif" data-bs-toggle="tab" data-bs-target="#{{ $id }}Pane" type="button"> {{ str_replace('classSections', 'Class Sections', str_replace('classSubjects', 'Class Subjects', ucfirst($id))) }}</button>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="yearsPane">
                    <div class="d-flex mb-3">
                        @can('academics.create')
                            <button class="btn btn-primary btn-sm ms-auto open-offcanvas" data-offcanvas="#yearOffcanvas">Add Academic Year</button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="yearsTable">
                        <thead><tr><th>ID</th><th>Name</th><th>Start</th><th>End</th><th>Active</th><th>Status</th><th>Terms</th><th width="120">Actions</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="classesPane">
                    <div class="d-flex mb-3">
                        @can('academics.create')
                            <button class="btn btn-primary btn-sm ms-auto open-offcanvas" data-offcanvas="#classOffcanvas">Add Class</button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="classesTable">
                        <thead><tr><th>ID</th><th>Name</th><th>Code</th><th>Order</th><th>Status</th><th>Sections</th><th>Subjects</th><th width="120">Actions</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="sectionsPane">
                    <div class="d-flex mb-3">
                        @can('academics.create')
                            <button class="btn btn-primary btn-sm ms-auto open-offcanvas" data-offcanvas="#sectionOffcanvas">Add Section</button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="sectionsTable">
                        <thead><tr><th>ID</th><th>Name</th><th>Code</th><th>Capacity</th><th>Status</th><th>Classes</th><th width="120">Actions</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="classSectionsPane">
                    <div class="d-flex mb-3">
                        @can('academics.create')
                            <button class="btn btn-primary btn-sm ms-auto open-offcanvas" data-offcanvas="#classSectionOffcanvas">Add Class Section</button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="classSectionsTable">
                        <thead><tr><th>ID</th><th>Class</th><th>Section</th><th>Class Teacher</th><th>Status</th><th width="120">Actions</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="subjectsPane">
                    <div class="d-flex mb-3">
                        @can('academics.create')
                            <button class="btn btn-primary btn-sm ms-auto open-offcanvas" data-offcanvas="#subjectOffcanvas">Add Subject</button>
                        @endcan
                    </div>
                    <table class="table table-striped table-bordered w-100" id="subjectsTable">
                        <thead><tr><th>ID</th><th>Name</th><th>Code</th><th>Type</th><th>Credit Hours</th><th>Status</th><th>Classes</th><th width="120">Actions</th></tr></thead>
                    </table>
                </div>

                <div class="tab-pane fade" id="classSubjectsPane">
                    <div class="d-flex mb-3">
                        @can('academics.create')
                            <button class="btn btn-primary btn-sm ms-auto open-offcanvas" data-offcanvas="#classSubjectOffcanvas">Assign Subject</button>
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
    <x-erp.side-panel id="yearOffcanvas" formId="yearForm" formClass="ajax-form" title="Academic Year" action="{{ route('admin.academics.academic-years.store') }}" method="POST" width="700px" saveButtonText="Save">
        <input type="hidden" name="_method" value="POST" id="yearMethod">
        <div class="tab-content pt-2">
            <div class="tab-pane fade show active" id="yearBasic">
                <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Academic Year Details</h6>
                <div class="row g-4">
                    <div class="col-12"><label class="form-label required fw-medium text-dark">Name</label><input class="form-control" name="name" required></div>
                    <div class="col-md-6"><label class="form-label required fw-medium text-dark">Starts On</label><input class="form-control" type="date" name="starts_on" required></div>
                    <div class="col-md-6"><label class="form-label required fw-medium text-dark">Ends On</label><input class="form-control" type="date" name="ends_on" required></div>
                    <div class="col-md-6"><label class="form-label required fw-medium text-dark">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option><option value="archived">Archived</option></select></div>
                    <div class="col-md-6 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActiveYear"><label class="form-check-label fw-medium text-dark" for="isActiveYear">Current active year</label></div></div>
                </div>
            </div>
        </div>
    </x-erp.side-panel>

    <x-erp.side-panel id="classOffcanvas" formId="classForm" formClass="ajax-form" title="Class" action="{{ route('admin.academics.classes.store') }}" method="POST" width="700px" saveButtonText="Save">
        <input type="hidden" name="_method" value="POST" id="classMethod">
        <div class="tab-content pt-2">
            <div class="tab-pane fade show active" id="classBasic">
                <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Class Details</h6>
                <div class="row g-4">
                    <div class="col-md-6"><label class="form-label required fw-medium text-dark">Name</label><input class="form-control" name="name" required></div>
                    <div class="col-md-6"><label class="form-label required fw-medium text-dark">Code</label><input class="form-control" name="code" required></div>
                    <div class="col-md-6"><label class="form-label fw-medium text-dark">Sort Order</label><input class="form-control" type="number" name="sort_order" min="0" value="0"></div>
                    <div class="col-md-6"><label class="form-label required fw-medium text-dark">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                </div>
            </div>
        </div>
    </x-erp.side-panel>

    <x-erp.side-panel id="sectionOffcanvas" formId="sectionForm" formClass="ajax-form" title="Section" action="{{ route('admin.academics.sections.store') }}" method="POST" width="700px" saveButtonText="Save">
        <input type="hidden" name="_method" value="POST" id="sectionMethod">
        <div class="tab-content pt-2">
            <div class="tab-pane fade show active" id="sectionBasic">
                <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Section Details</h6>
                <div class="row g-4">
                    <div class="col-md-6"><label class="form-label required fw-medium text-dark">Name</label><input class="form-control" name="name" required></div>
                    <div class="col-md-6"><label class="form-label required fw-medium text-dark">Code</label><input class="form-control" name="code" required></div>
                    <div class="col-md-6"><label class="form-label fw-medium text-dark">Capacity</label><input class="form-control" type="number" name="capacity" min="1"></div>
                    <div class="col-md-6"><label class="form-label required fw-medium text-dark">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                </div>
            </div>
        </div>
    </x-erp.side-panel>

    <x-erp.side-panel id="classSectionOffcanvas" formId="classSectionForm" formClass="ajax-form" title="Class Section" action="{{ route('admin.academics.class-sections.store') }}" method="POST" width="700px" saveButtonText="Save">
        <input type="hidden" name="_method" value="POST" id="classSectionMethod">
        <div class="tab-content pt-2">
            <div class="tab-pane fade show active" id="classSectionBasic">
                <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Class Section Details</h6>
                <div class="row g-4">
                    <div class="col-md-6"><label class="form-label required fw-medium text-dark">Class</label><select class="form-select" name="class_id" required><option value="">Select</option>@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->name }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label class="form-label required fw-medium text-dark">Section</label><select class="form-select" name="section_id" required><option value="">Select</option>@foreach($sections as $section)<option value="{{ $section->id }}">{{ $section->name }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label class="form-label fw-medium text-dark">Class Teacher</label><select class="form-select" name="class_teacher_id"><option value="">Unassigned</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->name }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label class="form-label required fw-medium text-dark">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                </div>
            </div>
        </div>
    </x-erp.side-panel>

    <x-erp.side-panel id="subjectOffcanvas" formId="subjectForm" formClass="ajax-form" title="Subject" action="{{ route('admin.academics.subjects.store') }}" method="POST" width="700px" saveButtonText="Save">
        <input type="hidden" name="_method" value="POST" id="subjectMethod">
        <div class="tab-content pt-2">
            <div class="tab-pane fade show active" id="subjectBasic">
                <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Subject Details</h6>
                <div class="row g-4">
                    <div class="col-md-6"><label class="form-label required fw-medium text-dark">Name</label><input class="form-control" name="name" required></div>
                    <div class="col-md-6"><label class="form-label required fw-medium text-dark">Code</label><input class="form-control" name="code" required></div>
                    <div class="col-md-6"><label class="form-label required fw-medium text-dark">Type</label><select class="form-select" name="type"><option value="core">Core</option><option value="elective">Elective</option><option value="optional">Optional</option><option value="co_scholastic">Co-scholastic</option></select></div>
                    <div class="col-md-6"><label class="form-label fw-medium text-dark">Credit Hours</label><input class="form-control" type="number" name="credit_hours" min="0" value="0"></div>
                    <div class="col-12"><label class="form-label fw-medium text-dark">Description</label><textarea class="form-control" name="description" rows="3"></textarea></div>
                    <div class="col-md-6"><label class="form-label required fw-medium text-dark">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                </div>
            </div>
        </div>
    </x-erp.side-panel>

    <x-erp.side-panel id="classSubjectOffcanvas" formId="classSubjectForm" formClass="ajax-form" title="Assign Subject" action="{{ route('admin.academics.class-subjects.store') }}" method="POST" width="700px" saveButtonText="Save">
        <input type="hidden" name="_method" value="POST" id="classSubjectMethod">
        <div class="tab-content pt-2">
            <div class="tab-pane fade show active" id="classSubjectBasic">
                <h6 class="fw-bold text-uppercase text-muted mb-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Class Subject Details</h6>
                <div class="row g-4">
                    <div class="col-md-6"><label class="form-label required fw-medium text-dark">Academic Year</label><select class="form-select" name="academic_year_id" required>@foreach($academicYears as $year)<option value="{{ $year->id }}" @selected($year->is_active)>{{ $year->name }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label class="form-label required fw-medium text-dark">Class</label><select class="form-select" name="class_id" required>@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->name }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label class="form-label required fw-medium text-dark">Subject</label><select class="form-select" name="subject_id" required>@foreach($subjects as $subject)<option value="{{ $subject->id }}">{{ $subject->name }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label class="form-label fw-medium text-dark">Teacher</label><select class="form-select" name="teacher_id"><option value="">Unassigned</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->name }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label class="form-label fw-medium text-dark">Weekly Periods</label><input class="form-control" type="number" name="weekly_periods" min="0" value="0"></div>
                    <div class="col-md-6"><label class="form-label required fw-medium text-dark">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                </div>
            </div>
        </div>
    </x-erp.side-panel>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => { (async () => { const DataTable = await window.lazyDT();
            const tables = {
                years: $('#yearsTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: '{{ route('admin.academics.academic-years.data') }}', columns: [
                    {data:'id'}, {data:'name'}, {data:'starts_on'}, {data:'ends_on'}, {data:'active_badge', orderable:false, searchable:false}, {data:'status'}, {data:'terms_count', searchable:false}, {data:'actions', orderable:false, searchable:false}
                ]}),
                classes: $('#classesTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: '{{ route('admin.academics.classes.data') }}', columns: [
                    {data:'id'}, {data:'name'}, {data:'code'}, {data:'sort_order'}, {data:'status'}, {data:'sections_count', searchable:false}, {data:'class_subjects_count', searchable:false}, {data:'actions', orderable:false, searchable:false}
                ]}),
                sections: $('#sectionsTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: '{{ route('admin.academics.sections.data') }}', columns: [
                    {data:'id'}, {data:'name'}, {data:'code'}, {data:'capacity'}, {data:'status'}, {data:'classes_count', searchable:false}, {data:'actions', orderable:false, searchable:false}
                ]}),
                classSections: $('#classSectionsTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: '{{ route('admin.academics.class-sections.data') }}', columns: [
                    {data:'id'}, {data:'class_name'}, {data:'section_name'}, {data:'teacher_name', orderable:false, searchable:false}, {data:'status'}, {data:'actions', orderable:false, searchable:false}
                ]}),
                subjects: $('#subjectsTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: '{{ route('admin.academics.subjects.data') }}', columns: [
                    {data:'id'}, {data:'name'}, {data:'code'}, {data:'type_label', name:'type'}, {data:'credit_hours'}, {data:'status'}, {data:'class_subjects_count', searchable:false}, {data:'actions', orderable:false, searchable:false}
                ]}),
                classSubjects: $('#classSubjectsTable').DataTable({processing: true, serverSide: true, responsive: true, stateSave: true, ajax: '{{ route('admin.academics.class-subjects.data') }}', columns: [
                    {data:'id', name:'class_subjects.id'}, {data:'academic_year', name:'academicYear.name'}, {data:'class_name', name:'schoolClass.name'}, {data:'subject_name', name:'subject.name'}, {data:'teacher_name', orderable:false}, {data:'weekly_periods', name:'class_subjects.weekly_periods'}, {data:'status', name:'class_subjects.status'}, {data:'actions', orderable:false, searchable:false}
                ]})
            };
            initTabPersistence('#academicTabs');

            const classSubjectStoreUrl = '{{ route('admin.academics.class-subjects.store') }}';
            const config = {
                'academic-year': {offcanvas: '#yearOffcanvas', formId: 'yearForm', store: '{{ route('admin.academics.academic-years.store') }}', table: tables.years},
                class: {offcanvas: '#classOffcanvas', formId: 'classForm', store: '{{ route('admin.academics.classes.store') }}', table: tables.classes},
                section: {offcanvas: '#sectionOffcanvas', formId: 'sectionForm', store: '{{ route('admin.academics.sections.store') }}', table: tables.sections},
                'class-section': {offcanvas: '#classSectionOffcanvas', formId: 'classSectionForm', store: '{{ route('admin.academics.class-sections.store') }}', table: tables.classSections},
                subject: {offcanvas: '#subjectOffcanvas', formId: 'subjectForm', store: '{{ route('admin.academics.subjects.store') }}', table: tables.subjects},
                'class-subject': {offcanvas: '#classSubjectOffcanvas', formId: 'classSubjectForm', store: classSubjectStoreUrl, table: tables.classSubjects}
            };

            $('.open-offcanvas').on('click', function () {
                const offcanvasId = $(this).data('offcanvas');
                const setup = Object.values(config).find(item => item.offcanvas === offcanvasId);
                const form = $(`#${setup.formId}`);
                
                form[0].reset();
                form.attr('action', setup.store);
                form.find('[name="_method"]').val('POST');
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback.dynamic').remove();
                
                const offcanvas = new bootstrap.Offcanvas(document.querySelector(offcanvasId));
                form.find('select').trigger('change.select2');

                offcanvas.show();
            });

            $('#yearForm, #classForm, #sectionForm, #classSectionForm, #subjectForm, #classSubjectForm').on('erp:success', function () {
                const setup = Object.values(config).find(item => item.formId === $(this).attr('id'));
                const offcanvasEl = document.querySelector(setup.offcanvas);
                const offcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl) || new bootstrap.Offcanvas(offcanvasEl);
                offcanvas.hide();
                Object.values(tables).forEach(table => table.ajax.reload(null, false));
            });

            $(document).on('click', '.edit-academic', function () {
                const type = $(this).data('type');
                const setup = config[type];
                const form = $(`#${setup.formId}`);
                
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
                    
                    const offcanvas = new bootstrap.Offcanvas(document.querySelector(setup.offcanvas));
                    form.find('select').trigger('change.select2');

                    offcanvas.show();
                });
            });

            $(document).on('click', '.delete-academic', function () {
                App.confirmDelete({
                    url: $(this).data('url'),
                    onSuccess: () => Object.values(tables).forEach(table => table.ajax.reload(null, false))
                });
            });
        })(); });
    </script>
@endpush
