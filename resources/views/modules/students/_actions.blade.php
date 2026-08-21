<x-erp.table-action-menu>
    @can('students.update')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center edit-student"
                    data-url="{{ route('admin.students.show', $student) }}"
                    data-update-url="{{ route('admin.students.update', $student) }}">
                <i class="ti ti-pencil me-2"></i> Edit
            </button>
        </li>
    @endcan
    @can('students.delete')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center text-danger delete-student" data-url="{{ route('admin.students.destroy', $student) }}">
                <i class="ti ti-trash me-2 text-danger"></i> Delete
            </button>
        </li>
    @endcan
</x-erp.table-action-menu>
