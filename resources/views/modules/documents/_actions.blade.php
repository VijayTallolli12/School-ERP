<div class="d-flex gap-1 justify-content-end">
    @can('student_documents.view')
        <button type="button" class="btn btn-sm btn-outline-info view-document"
                data-id="{{ $doc->id }}" title="View Document">
            <i class="ti ti-eye"></i>
        </button>
        <a href="{{ route('admin.documents.download', $doc->id) }}"
           class="btn btn-sm btn-outline-secondary" title="Download" target="_blank">
            <i class="ti ti-download"></i>
        </a>
    @endcan
    @can('student_documents.update')
        <button type="button" class="btn btn-sm btn-outline-primary edit-document"
                data-id="{{ $doc->id }}" title="Edit Document">
            <i class="ti ti-edit"></i>
        </button>
    @endcan
    @can('student_documents.verify')
        <button type="button" class="btn btn-sm btn-outline-{{ $doc->is_verified ? 'warning' : 'success' }} toggle-verify"
                data-id="{{ $doc->id }}"
                title="{{ $doc->is_verified ? 'Unverify' : 'Verify' }}">
            <i class="ti ti-{{ $doc->is_verified ? 'shield-off' : 'shield-check' }}"></i>
        </button>
    @endcan
    @can('student_documents.delete')
        <button type="button" class="btn btn-sm btn-outline-danger delete-document"
                data-id="{{ $doc->id }}" title="Delete Document">
            <i class="ti ti-trash"></i>
        </button>
    @endcan
</div>
