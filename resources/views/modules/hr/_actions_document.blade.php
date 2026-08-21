<x-erp.table-action-menu>
    @if ($doc->status !== 'verified')
        @can('hr.verify')
            <li>
                <button type="button" class="dropdown-item d-flex align-items-center verify-document" data-url="{{ route('admin.hr.documents.verify', $doc) }}" title="Verify">
                    <i class="ti ti-check me-2"></i> Verify
                </button>
            </li>
        @endcan
    @endif
    @can('hr.update')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center edit-document" data-url="{{ route('admin.hr.documents.show', $doc) }}" data-update-url="{{ route('admin.hr.documents.update', $doc) }}" title="Edit">
                <i class="ti ti-pencil me-2"></i> Edit
            </button>
        </li>
    @endcan
    @can('hr.delete')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center text-danger delete-document" data-url="{{ route('admin.hr.documents.destroy', $doc) }}" title="Delete">
                <i class="ti ti-trash me-2 text-danger"></i> Delete
            </button>
        </li>
    @endcan
</x-erp.table-action-menu>
