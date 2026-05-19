<div class="table-actions">
    @php
        $routeBase = [
            'academic-year' => 'academic-years',
            'class' => 'classes',
            'section' => 'sections',
            'subject' => 'subjects',
            'class-section' => 'class-sections',
            'class-subject' => 'class-subjects',
        ][$type] ?? $type;
    @endphp
    @can('academics.update')
        <button type="button" class="btn btn-sm btn-outline-primary edit-academic"
                data-type="{{ $type }}"
                data-url="{{ route('admin.academics.'.$routeBase.'.show', $model) }}"
                data-update-url="{{ route('admin.academics.'.$routeBase.'.update', $model) }}">
            <i class="ti ti-pencil"></i>
        </button>
    @endcan
    @can('academics.delete')
        <button type="button" class="btn btn-sm btn-outline-danger delete-academic"
                data-url="{{ route('admin.academics.'.$routeBase.'.destroy', $model) }}">
            <i class="ti ti-trash"></i>
        </button>
    @endcan
</div>
