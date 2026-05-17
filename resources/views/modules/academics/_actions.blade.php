<div class="table-actions">
    @php
        $routeBase = [
            'academic-year' => 'academic-years',
            'class' => 'classes',
            'section' => 'sections',
            'subject' => 'subjects',
            'class-section' => 'class-sections',
        ][$type] ?? $type;
    @endphp
    @if ($type !== 'class-subject')
        @can('academics.update')
            <button type="button" class="btn btn-sm btn-outline-primary edit-academic"
                    data-type="{{ $type }}"
                    data-url="{{ route('admin.academics.'.$routeBase.'.show', $model) }}"
                    data-update-url="{{ route('admin.academics.'.$routeBase.'.update', $model) }}">
                <i class="ti ti-pencil"></i>
            </button>
        @endcan
    @endif
    @can('academics.delete')
        <button type="button" class="btn btn-sm btn-outline-danger delete-academic"
                data-url="{{ $type === 'class-subject' ? route('admin.academics.class-subjects.destroy', $model) : route('admin.academics.'.$routeBase.'.destroy', $model) }}">
            <i class="ti ti-trash"></i>
        </button>
    @endcan
</div>
