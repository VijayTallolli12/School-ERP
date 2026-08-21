<x-erp.table-action-menu>
    <li>
        <button type="button" class="dropdown-item d-flex align-items-center view-leave-request"
                data-url="{{ route('admin.leave-requests.show', $lr) }}"
                title="View">
            <i class="ti ti-eye me-2"></i> View
        </button>
    </li>
    @can('leave_management.approve')
        @if($lr->status === 'pending')
            <li>
                <button type="button" class="dropdown-item d-flex align-items-center approve-leave-request"
                        data-url="{{ route('admin.leave-requests.approve', $lr) }}"
                        title="Approve">
                    <i class="ti ti-check me-2"></i> Approve
                </button>
            </li>
            <li>
                <button type="button" class="dropdown-item d-flex align-items-center reject-leave-request"
                        data-url="{{ route('admin.leave-requests.reject', $lr) }}"
                        title="Reject">
                    <i class="ti ti-x me-2"></i> Reject
                </button>
            </li>
        @endif
    @endcan
    @can('leave_management.delete')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center text-danger delete-leave-request"
                    data-url="{{ route('admin.leave-requests.destroy', $lr) }}"
                    title="Delete">
                <i class="ti ti-trash me-2 text-danger"></i> Delete
            </button>
        </li>
    @endcan
</x-erp.table-action-menu>
