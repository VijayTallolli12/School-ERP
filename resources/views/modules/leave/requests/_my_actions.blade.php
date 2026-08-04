<div class="btn-group" role="group">
    <button type="button" class="btn btn-sm btn-outline-secondary view-leave-request"
            data-url="{{ route('admin.leave-requests.show', $lr) }}"
            title="View">
        <i class="ti ti-eye"></i>
    </button>
    @can('leave_management.delete')
        @if($lr->status === 'pending')
            <button type="button" class="btn btn-sm btn-outline-danger delete-leave-request"
                    data-url="{{ route('admin.leave-requests.destroy', $lr) }}"
                    title="Delete">
                <i class="ti ti-trash"></i>
            </button>
        @endif
    @endcan
</div>
