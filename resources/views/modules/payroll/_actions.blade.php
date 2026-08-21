<x-erp.table-action-menu>
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
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center edit-payroll"
                    data-type="{{ $type }}"
                    data-url="{{ route('admin.payroll.'.$routeBase.'.show', $model) }}"
                    data-update-url="{{ route('admin.payroll.'.$routeBase.'.update', $model) }}">
                <i class="ti ti-pencil me-2"></i> Edit
            </button>
        </li>
    @endcan
    @can('payroll.delete')
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center text-danger delete-payroll"
                    data-url="{{ route('admin.payroll.'.$routeBase.'.destroy', $model) }}">
                <i class="ti ti-trash me-2 text-danger"></i> Delete
            </button>
        </li>
    @endcan
</x-erp.table-action-menu>
