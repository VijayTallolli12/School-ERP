<div class="btn-group btn-group-sm">
    <a href="{{ route('admin.fees.collections.receipt.print', $row) }}" class="btn btn-outline-secondary" target="_blank" title="Print receipt">
        <i class="ti ti-printer"></i>
    </a>
    <a href="{{ route('admin.fees.collections.receipt.pdf', $row) }}" class="btn btn-outline-secondary" title="Download PDF">
        <i class="ti ti-file-type-pdf"></i>
    </a>
    @can('fees.delete')
        <button type="button" class="btn btn-outline-danger delete-collection" data-url="{{ route('admin.fees.collections.destroy', $row) }}">
            <i class="ti ti-trash"></i>
        </button>
    @endcan
</div>
