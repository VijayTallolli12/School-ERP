<x-erp.table-action-menu>
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
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center edit-academic"
                    data-type="{{ $type }}"
                    data-url="{{ route('admin.academics.'.$routeBase.'.show', $model) }}"
                    data-update-url="{{ route('admin.academics.'.$routeBase.'.update', $model) }}">
                <i class="ti ti-pencil me-2"></i> Edit
            </button>
        </li>
    @endcan
    @can('academics.delete')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center text-danger delete-academic"
                    data-url="{{ route('admin.academics.'.$routeBase.'.destroy', $model) }}">
                <i class="ti ti-trash me-2"></i> Delete
            </button>
        </li>
    @endcan
</x-erp.table-action-menu>
