<x-erp.table-action-menu>
    @if ($t->transfer_type === 'tc' && $t->tc_no && auth()->user()->can('student_lifecycle.tc'))
        <li>
            <a href="{{ route('admin.lifecycle.tc.print', $t) }}" class="dropdown-item d-flex align-items-center" target="_blank" title="Print TC">
                <i class="ti ti-printer me-2"></i> Print TC
            </a>
        </li>
    @endif
</x-erp.table-action-menu>
