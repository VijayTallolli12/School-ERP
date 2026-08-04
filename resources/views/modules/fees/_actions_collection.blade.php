<div class="btn-group btn-group-sm">
    <a href="{{ route('admin.fees.collections.receipt.print', $row) }}" class="btn btn-outline-secondary" target="_blank" title="Print receipt">
        <i class="ti ti-printer"></i>
    </a>
    <a href="{{ route('admin.fees.collections.receipt.pdf', $row) }}" class="btn btn-outline-secondary" title="Download PDF">
        <i class="ti ti-file-type-pdf"></i>
    </a>
    @can('fees.update')
        <button type="button" class="btn btn-outline-warning void-collection" data-url="{{ route('admin.fees.collections.void', $row) }}" title="Void payment">
            <i class="ti ti-x"></i>
        </button>
    @endcan
</div>
