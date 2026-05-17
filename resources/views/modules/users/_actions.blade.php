<div class="btn-group" role="group">
    @can('users.update')
        <button type="button" class="btn btn-sm btn-outline-secondary edit-user" data-url="{{ route('admin.users.show', $user) }}" data-update-url="{{ route('admin.users.update', $user) }}" title="Edit">
            <i class="ti ti-pencil"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-warning reset-password-btn" data-url="{{ route('admin.users.reset-password', $user) }}" data-name="{{ $user->name }}" title="Reset Password">
            <i class="ti ti-key"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-info assign-role-btn" data-url="{{ route('admin.users.assign-role', $user) }}" data-name="{{ $user->name }}" data-current-role="{{ $user->roles->first()?->name }}" title="Assign Role">
            <i class="ti ti-user-cog"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-{{ $user->status === 'active' ? 'danger' : 'success' }} toggle-status-btn" data-url="{{ route('admin.users.toggle-status', $user) }}" title="{{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}">
            <i class="ti ti-{{ $user->status === 'active' ? 'ban' : 'check' }}"></i>
        </button>
    @endcan
    @can('users.delete')
        <button type="button" class="btn btn-sm btn-outline-danger delete-user" data-url="{{ route('admin.users.destroy', $user) }}" title="Delete">
            <i class="ti ti-trash"></i>
        </button>
    @endcan
</div>