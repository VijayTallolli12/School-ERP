<x-erp.table-action-menu>
    @can('hr.update')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center edit-employee" data-url="{{ route('admin.hr.show', $employee) }}" data-update-url="{{ route('admin.hr.update', $employee) }}" title="Edit">
                <i class="ti ti-pencil me-2"></i> Edit
            </button>
        </li>
    @endcan
    @can('hr.delete')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center text-danger delete-employee" data-url="{{ route('admin.hr.destroy', $employee) }}" title="Delete">
                <i class="ti ti-trash me-2 text-danger"></i> Delete
            </button>
        </li>
    @endcan
</x-erp.table-action-menu>
