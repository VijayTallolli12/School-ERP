<div class="btn-group" role="group">
    <button type="button" class="btn btn-sm btn-outline-secondary view-leave-request"
            data-url="{{ route('admin.leave-requests.show', $lr) }}"
            title="View">
        <i class="ti ti-eye"></i>
    </button>
    @can('leave_management.approve')
        @if($lr->status === 'pending')
            <button type="button" class="btn btn-sm btn-outline-success approve-leave-request"
                    data-url="{{ route('admin.leave-requests.approve', $lr) }}"
                    title="Approve">
                <i class="ti ti-check"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger reject-leave-request"
                    data-url="{{ route('admin.leave-requests.reject', $lr) }}"
                    title="Reject">
                <i class="ti ti-x"></i>
            </button>
        @endif
    @endcan
    @can('leave_management.delete')
        <button type="button" class="btn btn-sm btn-outline-danger delete-leave-request"
                data-url="{{ route('admin.leave-requests.destroy', $lr) }}"
                title="Delete">
            <i class="ti ti-trash"></i>
        </button>
    @endcan
</div>
