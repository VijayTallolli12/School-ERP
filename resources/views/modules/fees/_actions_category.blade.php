<x-erp.table-action-menu>
    @can('fees.update')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center edit-fee-category" data-url="{{ route('admin.fees.categories.show', $row) }}" data-update-url="{{ route('admin.fees.categories.update', $row) }}">
                <i class="ti ti-pencil me-2"></i> Edit
            </button>
        </li>
    @endcan
    @can('fees.delete')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center text-danger delete-fee-category" data-url="{{ route('admin.fees.categories.destroy', $row) }}">
                <i class="ti ti-trash me-2 text-danger"></i> Delete
            </button>
        </li>
    @endcan
</x-erp.table-action-menu>
