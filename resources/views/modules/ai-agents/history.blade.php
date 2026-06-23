@extends('layouts.admin')

@section('title', 'Execution History')
@section('page-title', 'Agent Execution History')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Execution Log</h3>
        <div class="card-tools">
            <a href="{{ route('admin.agents.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="executionTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Agent</th>
                        <th>Executed By</th>
                        <th>Status</th>
                        <th>Started</th>
                        <th>Duration</th>
                        <th>Records</th>
                        <th>Summary</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$(async function () {
    const DataTable = await window.lazyDT();
    $('#executionTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("admin.agents.history.data") }}',
        columns: [
            { data: 'id', name: 'id' },
            { data: 'agent_name', name: 'agent_name' },
            { data: 'executor_name', name: 'executor.name', orderable: false },
            { data: 'status_badge', name: 'status', orderable: false, searchable: false },
            { data: 'started_at', name: 'started_at', render: function(d) { return d ? new Date(d).toLocaleString() : '-'; } },
            { data: 'duration', name: 'duration', orderable: false, searchable: false },
            { data: 'records_processed', name: 'records_processed' },
            { data: 'result_summary', name: 'result_summary', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        order: [[0, 'desc']],
        pageLength: 25,
    });
});
</script>
@endpush
