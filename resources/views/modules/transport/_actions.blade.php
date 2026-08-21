<x-erp.table-action-menu>
    @php
        $routeBase = [
            'vehicle' => 'vehicles',
            'driver' => 'drivers',
            'route' => 'routes',
            'route-stop' => 'route-stops',
            'assignment' => 'assignments',
        ][$type] ?? $type;
    @endphp
    @if ($type === 'route')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center view-route"
                    data-url="{{ route('admin.transport.routes.detail', $model) }}"
                    title="View Route Detail">
                <i class="ti ti-map-route me-2"></i> View Route Detail
            </button>
        </li>
    @endif
    @can('transport.update')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center edit-transport"
                    data-type="{{ $type }}"
                    data-url="{{ route('admin.transport.'.$routeBase.'.show', $model) }}"
                    data-update-url="{{ route('admin.transport.'.$routeBase.'.update', $model) }}">
                <i class="ti ti-pencil me-2"></i> Edit
            </button>
        </li>
        @if ($type === 'driver' && $model->user_id)
            <li>
                <button type="button" class="dropdown-item d-flex align-items-center reset-driver-password"
                        data-url="{{ route('admin.transport.drivers.reset-password', $model) }}"
                        data-name="{{ $model->name }}"
                        title="Reset Driver Password"><i class="ti ti-key me-2"></i> Reset Driver Password</button>
            </li>
        @endif
    @endcan
    @can('transport.delete')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center text-danger delete-transport"
                    data-url="{{ route('admin.transport.'.$routeBase.'.destroy', $model) }}">
                <i class="ti ti-trash me-2 text-danger"></i> Delete
            </button>
        </li>
    @endcan
</x-erp.table-action-menu>
