<x-erp.table-action-menu>
    <li>
        <a href="{{ route('admin.fees.collections.receipt.print', $row) }}" class="dropdown-item d-flex align-items-center" target="_blank" title="Print receipt">
            <i class="ti ti-printer me-2"></i> Print receipt
        </a>
    </li>
    <li>
        <a href="{{ route('admin.fees.collections.receipt.pdf', $row) }}" class="dropdown-item d-flex align-items-center" title="Download PDF">
            <i class="ti ti-file-type-pdf me-2"></i> Download PDF
        </a>
    </li>
    @can('fees.update')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center void-collection" data-url="{{ route('admin.fees.collections.void', $row) }}" title="Void payment">
                <i class="ti ti-x me-2"></i> Void payment
            </button>
        </li>
    @endcan
</x-erp.table-action-menu>
