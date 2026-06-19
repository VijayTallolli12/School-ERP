<div class="table-actions d-flex gap-1">
    @php
        $routeBase = [
            'department' => 'departments',
            'designation' => 'designations',
            'salary-component' => 'salary-components',
            'pay-grade' => 'pay-grades',
            'salary-structure' => 'salary-structures',
        ][$type] ?? $type;
    @endphp
    @can('payroll.update')
        <button type="button" class="btn btn-sm btn-outline-primary edit-payroll"
                data-type="{{ $type }}"
                data-url="{{ route('admin.payroll.'.$routeBase.'.show', $model) }}"
                data-update-url="{{ route('admin.payroll.'.$routeBase.'.update', $model) }}">
            <i class="ti ti-pencil"></i>
        </button>
    @endcan
    @can('payroll.delete')
        <button type="button" class="btn btn-sm btn-outline-danger delete-payroll"
                data-url="{{ route('admin.payroll.'.$routeBase.'.destroy', $model) }}">
            <i class="ti ti-trash"></i>
        </button>
    @endcan
</div>
