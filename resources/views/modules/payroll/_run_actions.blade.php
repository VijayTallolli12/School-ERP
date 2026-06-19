<div class="btn-group btn-group-sm">
    <button class="btn btn-outline-info view-run" data-url="{{ route('admin.payroll.runs.show', $run) }}" data-items-url="{{ route('admin.payroll.runs.items.data', $run->id) }}" title="View">
        <i class="ti ti-eye"></i>
    </button>
    @if($run->isDraft())
        @can('payroll.lock')
            <button class="btn btn-outline-success lock-run" data-url="{{ route('admin.payroll.runs.lock', $run) }}" title="Lock" data-period="{{ $run->month_name }} {{ $run->year }}">
                <i class="ti ti-lock"></i>
            </button>
        @endcan
        @can('payroll.delete')
            <button class="btn btn-outline-danger delete-payroll" data-url="{{ route('admin.payroll.runs.destroy', $run) }}" title="Delete">
                <i class="ti ti-trash"></i>
            </button>
        @endcan
    @endif
</div>
