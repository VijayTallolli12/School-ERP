<div class="btn-group" role="group">
    @if ($doc->status !== 'verified')
        @can('hr.verify')
            <button type="button" class="btn btn-sm btn-outline-success verify-document" data-url="{{ route('admin.hr.documents.verify', $doc) }}" title="Verify">
                <i class="ti ti-check"></i>
            </button>
        @endcan
    @endif
    @can('hr.update')
        <button type="button" class="btn btn-sm btn-outline-primary edit-document" data-url="{{ route('admin.hr.documents.show', $doc) }}" data-update-url="{{ route('admin.hr.documents.update', $doc) }}" title="Edit">
            <i class="ti ti-pencil"></i>
        </button>
    @endcan
    @can('hr.delete')
        <button type="button" class="btn btn-sm btn-outline-danger delete-document" data-url="{{ route('admin.hr.documents.destroy', $doc) }}" title="Delete">
            <i class="ti ti-trash"></i>
        </button>
    @endcan
</div>
