<x-erp.table-action-menu>
@can('homework.update')
    <li>
        <button type="button" class="dropdown-item d-flex align-items-center edit-homework"
                data-url="{{ route('admin.homework.show', $hw) }}"
                data-update-url="{{ route('admin.homework.update', $hw) }}"
                title="Edit">
            <i class="ti ti-pencil me-2"></i> Edit
        </button>
    </li>
@endcan
@can('homework.delete')
    <li>
        <button type="button" class="dropdown-item d-flex align-items-center text-danger delete-homework"
                data-url="{{ route('admin.homework.destroy', $hw) }}"
                title="Delete">
            <i class="ti ti-trash me-2 text-danger"></i> Delete
        </button>
    </li>
@endcan
</x-erp.table-action-menu>
