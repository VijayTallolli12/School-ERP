<div class="table-actions d-flex gap-1">
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
        <button type="button" class="btn btn-sm btn-outline-info view-route"
                data-url="{{ route('admin.transport.routes.detail', $model) }}"
                title="View Route Detail">
            <i class="ti ti-map-route"></i>
        </button>
    @endif
    @can('transport.update')
        <button type="button" class="btn btn-sm btn-outline-primary edit-transport"
                data-type="{{ $type }}"
                data-url="{{ route('admin.transport.'.$routeBase.'.show', $model) }}"
                data-update-url="{{ route('admin.transport.'.$routeBase.'.update', $model) }}">
            <i class="ti ti-pencil"></i>
        </button>
    @endcan
    @can('transport.delete')
        <button type="button" class="btn btn-sm btn-outline-danger delete-transport"
                data-url="{{ route('admin.transport.'.$routeBase.'.destroy', $model) }}">
            <i class="ti ti-trash"></i>
        </button>
    @endcan
</div>
