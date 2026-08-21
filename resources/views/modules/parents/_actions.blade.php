<x-erp.table-action-menu>
@can('parents.update')
    <li>
        <a href="javascript:void(0)" class="dropdown-item d-flex align-items-center edit-parent"
           data-url="{{ route('admin.parents.show', $parent) }}"
           data-update-url="{{ route('admin.parents.update', $parent) }}" title="Edit">
            <i class="ti ti-pencil me-2"></i> Edit
        </a>
    </li>
@endcan

@can('parents.delete')
    <li>
        <a href="javascript:void(0)" class="dropdown-item d-flex align-items-center text-danger delete-parent ms-1"
           data-url="{{ route('admin.parents.destroy', $parent) }}" title="Delete">
            <i class="ti ti-trash me-2 text-danger"></i> Delete
        </a>
    </li>
@endcan
</x-erp.table-action-menu>