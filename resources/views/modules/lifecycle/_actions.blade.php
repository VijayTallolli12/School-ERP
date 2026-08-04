<div class="table-actions d-flex gap-1">
    @if ($t->transfer_type === 'tc' && $t->tc_no && auth()->user()->can('student_lifecycle.tc'))
        <a href="{{ route('admin.lifecycle.tc.print', $t) }}" class="btn btn-sm btn-outline-secondary" target="_blank" title="Print TC">
            <i class="ti ti-printer"></i>
        </a>
    @endif
</div>
