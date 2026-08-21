<x-erp.table-action-menu>
    @can('users.update')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center edit-user" data-url="{{ route('admin.users.show', $user) }}" data-update-url="{{ route('admin.users.update', $user) }}" title="Edit">
                <i class="ti ti-pencil me-2"></i> Edit
            </button>
        </li>
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center reset-password-btn" data-url="{{ route('admin.users.reset-password', $user) }}" data-name="{{ $user->name }}" title="Reset Password"><i class="ti ti-key me-2"></i> Reset Password</button>
        </li>
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center assign-role-btn" data-url="{{ route('admin.users.assign-role', $user) }}" data-name="{{ $user->name }}" data-current-role="{{ $user->roles->first()?->name }}" title="Assign Role"><i class="ti ti-shield me-2"></i> Assign Role</button>
        </li>
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center toggle-status-btn" data-url="{{ route('admin.users.toggle-status', $user) }}" title="{{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}">
                <i class="ti ti-{{ $user->status === 'active' ? 'power' : 'check' }} me-2"></i> {{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}
            </button>
        </li>
    @endcan
    @can('users.delete')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center text-danger delete-user" data-url="{{ route('admin.users.destroy', $user) }}" title="Delete">
                <i class="ti ti-trash me-2 text-danger"></i> Delete
            </button>
        </li>
    @endcan
</x-erp.table-action-menu>