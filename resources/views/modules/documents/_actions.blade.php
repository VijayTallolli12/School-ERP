<x-erp.table-action-menu>
    @can('student_documents.view')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center view-document"
                    data-id="{{ $doc->id }}" title="View Document"><i class="ti ti-eye me-2"></i> View</button>
        </li>
        <li>
            <a href="{{ route('admin.documents.download', $doc->id) }}"
               class="dropdown-item d-flex align-items-center" title="Download" target="_blank">
                <i class="ti ti-download me-2"></i> Download
            </a>
        </li>
    @endcan
    @can('student_documents.update')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center edit-document"
                    data-id="{{ $doc->id }}" title="Edit Document"><i class="ti ti-pencil me-2"></i> Edit</button>
        </li>
    @endcan
    @can('student_documents.verify')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center toggle-verify"
                    data-id="{{ $doc->id }}"
                    title="{{ $doc->is_verified ? 'Unverify' : 'Verify' }}">
                <i class="ti ti-{{ $doc->is_verified ? 'x' : 'check' }} me-2"></i> {{ $doc->is_verified ? 'Unverify' : 'Verify' }}
            </button>
        </li>
    @endcan
    @can('student_documents.delete')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center text-danger delete-document"
                    data-id="{{ $doc->id }}" title="Delete Document"><i class="ti ti-trash me-2"></i> Delete</button>
        </li>
    @endcan
</x-erp.table-action-menu>
