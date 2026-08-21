<x-erp.table-action-menu>
    @can('teachers.update')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center edit-teacher" data-url="{{ route('admin.teachers.show', $teacher) }}" data-update-url="{{ route('admin.teachers.update', $teacher) }}" title="Edit">
                <i class="ti ti-pencil me-2"></i> Edit
            </button>
        </li>
    @endcan
    @can('teachers.delete')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center text-danger delete-teacher" data-url="{{ route('admin.teachers.destroy', $teacher) }}" title="Delete">
                <i class="ti ti-trash me-2 text-danger"></i> Delete
            </button>
        </li>
    @endcan
</x-erp.table-action-menu>
