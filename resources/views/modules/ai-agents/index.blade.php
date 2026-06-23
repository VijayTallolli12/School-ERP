@extends('layouts.admin')

@section('title', 'AI Agents')
@section('page-title', 'AI Agents')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Agent Dashboard</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.agents.history') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="ti ti-clock me-1"></i> Execution History
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    @foreach($agents as $name => $agent)
                        @include('modules.ai-agents.partials.agent-card', [
                            'name' => $name,
                            'agent' => $agent,
                            'stats' => $executions->get($name),
                        ])
                    @endforeach
                </div>

                @if(empty($agents))
                    <div class="text-center py-5 text-muted">
                        <i class="ti ti-robot-off fs-1 d-block mb-2"></i>
                        <p>No agents registered.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('modals')
<div class="modal fade" id="agentModal" tabindex="-1" aria-labelledby="agentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="agentModalLabel">
                    <i class="ti ti-robot ti-fw me-1"></i> <span id="agentModalTitle">Agent</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="agentStepConfig">
                    <p class="text-muted mb-3" id="agentConfigDescription"></p>
                    <div id="agentConfigFields"></div>
                    <button type="button" class="btn btn-primary w-100" id="agentPreviewBtn">
                        <i class="ti ti-search me-1"></i> Preview
                    </button>
                </div>

                <div id="agentStepLoading" class="d-none text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Processing...</p>
                </div>

                <div id="agentStepConfirm" class="d-none">
                    <div class="alert alert-info" id="agentPreviewSummary"></div>
                    <div id="agentPreviewDetail" class="mb-3"></div>
                    <div class="alert alert-warning mb-0" id="agentConfirmWarning"></div>
                </div>

                <div id="agentStepResult" class="d-none">
                    <div class="alert alert-success" id="agentResultSummary"></div>
                    <div id="agentResultDetail"></div>
                </div>

                <div id="agentStepError" class="d-none">
                    <div class="alert alert-danger">
                        <i class="ti ti-alert-circle me-1"></i>
                        <span id="agentErrorMessage">An error occurred.</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="agentModalBack" data-bs-dismiss="modal">
                    <i class="ti ti-arrow-left me-1"></i> Back
                </button>
                <button type="button" class="btn btn-danger d-none" id="agentRunBtn">
                    <i class="ti ti-player-play me-1"></i> Run Agent
                </button>
                <button type="button" class="btn btn-primary d-none" id="agentDoneBtn" data-bs-dismiss="modal">
                    <i class="ti ti-check me-1"></i> Done
                </button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
$(document).ready(function () {
    const $modal = $('#agentModal');
    const $title = $('#agentModalTitle');
    const $desc = $('#agentConfigDescription');
    const $fields = $('#agentConfigFields');
    const $config = $('#agentStepConfig');
    const $loading = $('#agentStepLoading');
    const $confirm = $('#agentStepConfirm');
    const $result = $('#agentStepResult');
    const $error = $('#agentStepError');
    const $errorMsg = $('#agentErrorMessage');
    const $previewBtn = $('#agentPreviewBtn');
    const $runBtn = $('#agentRunBtn');
    const $doneBtn = $('#agentDoneBtn');
    const $backBtn = $('#agentModalBack');
    const $previewSummary = $('#agentPreviewSummary');
    const $previewDetail = $('#agentPreviewDetail');
    const $confirmWarning = $('#agentConfirmWarning');
    const $resultSummary = $('#agentResultSummary');
    const $resultDetail = $('#agentResultDetail');

    let currentAgent = null;
    let currentParams = {};
    let currentData = null;

    function resetUI() {
        $config.removeClass('d-none');
        $loading.addClass('d-none');
        $confirm.addClass('d-none');
        $result.addClass('d-none');
        $error.addClass('d-none');
        $runBtn.addClass('d-none');
        $doneBtn.addClass('d-none');
        $backBtn.text('Cancel').attr('data-bs-dismiss', 'modal');
    }

    function showStep(el) {
        $config.addClass('d-none');
        $loading.addClass('d-none');
        $confirm.addClass('d-none');
        $result.addClass('d-none');
        $error.addClass('d-none');
        el.removeClass('d-none');
    }

    function escHtml(str) {
        const div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    $('.run-agent-btn').on('click', function () {
        const agentName = $(this).data('agent');
        const agentLabel = $(this).data('label');
        const agentDesc = $(this).data('description');
        const configJson = $(this).data('config');

        currentAgent = agentName;
        currentParams = {};
        currentData = null;
        resetUI();

        $title.text(agentLabel);
        $desc.text(agentDesc);

        const config = typeof configJson === 'string' ? JSON.parse(configJson) : configJson;
        let html = '';
        if (config.params) {
            Object.keys(config.params).forEach(function (key) {
                const param = config.params[key];
                if (param.type === 'select') {
                    let opts = '';
                    param.options.forEach(function (opt) {
                        const selected = opt.value == param.default ? ' selected' : '';
                        opts += '<option value="' + opt.value + '"' + selected + '>' + escHtml(opt.label) + '</option>';
                    });
                    html += '<div class="mb-3">' +
                        '<label class="form-label">' + escHtml(param.label) + '</label>' +
                        '<select class="form-select agent-param" data-key="' + key + '">' + opts + '</select>' +
                        '</div>';
                }
            });
        }
        $fields.html(html);

        $modal.modal('show');
    });

    $previewBtn.on('click', function () {
        const params = {};
        $('.agent-param').each(function () {
            params[$(this).data('key')] = $(this).val();
        });
        currentParams = params;

        showStep($loading);

        var previewBase = '{{ route("admin.agents.preview", ["agent" => "__agent__"]) }}';
        $.ajax({
            url: previewBase.replace('__agent__', currentAgent),
            method: 'POST',
            data: $.extend({}, params, { _token: '{{ csrf_token() }}' }),
            success: function (res) {
                currentData = res.data;
                showStep($confirm);

                const d = res.data;
                let summaryHtml = '<h6 class="alert-heading"><i class="ti ti-users me-1"></i> Preview Summary</h6>';
                const items = [];

                if (d.student_count !== undefined) items.push(
                    '<div class="col-4"><div class="fs-2 fw-bold">' + d.student_count + '</div><div class="text-muted small">Students found</div></div>'
                );
                if (d.total_outstanding !== undefined) items.push(
                    '<div class="col-4"><div class="fs-2 fw-bold">₹' + Number(d.total_outstanding).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</div><div class="text-muted small">Total outstanding</div></div>'
                );
                if (items.length > 0) {
                    summaryHtml += '<div class="row mt-2">' + items.join('') + '</div>';
                }
                $previewSummary.html(summaryHtml);

                if (d.students && d.students.length > 0) {
                    let tableHtml = '<div class="table-responsive" style="max-height:250px;overflow-y:auto;">' +
                        '<table class="table table-sm table-bordered"><thead class="table-light"><tr><th>#</th><th>Student</th><th>Class</th><th class="text-end">Outstanding</th></tr></thead><tbody>';
                    d.students.forEach(function (s, i) {
                        tableHtml += '<tr><td>' + (i + 1) + '</td><td>' + escHtml(s.name) + '</td><td>' + escHtml(s.class) + '</td><td class="text-end">₹' + Number(s.balance).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</td></tr>';
                    });
                    tableHtml += '</tbody></table></div>';
                    $previewDetail.html(tableHtml);
                } else {
                    $previewDetail.html('<p class="text-muted mb-0">No detailed data available.</p>');
                }

                $confirmWarning.html('<i class="ti ti-alert-triangle me-1"></i> <strong>Proceed?</strong> Review the preview above before running.');
                $runBtn.removeClass('d-none');
                $backBtn.text('Cancel');
            },
            error: function (xhr) {
                showStep($error);
                $errorMsg.text(xhr.responseJSON?.message || 'Failed to fetch preview.');
            }
        });
    });

    $runBtn.on('click', function () {
        if (!currentData) return;
        showStep($loading);
        $runBtn.addClass('d-none');

        var executeBase = '{{ route("admin.agents.execute", ["agent" => "__agent__"]) }}';
        $.ajax({
            url: executeBase.replace('__agent__', currentAgent),
            method: 'POST',
            data: $.extend({}, currentParams, { _token: '{{ csrf_token() }}' }),
            success: function (res) {
                showStep($result);

                const d = res.data;
                let summaryHtml = '<h6 class="alert-heading"><i class="ti ti-circle-check me-1"></i> Agent Executed Successfully</h6><div class="row mt-2">';

                if (d.student_count !== undefined) {
                    summaryHtml += '<div class="col-4 text-center"><div class="fs-2 fw-bold">' + d.student_count + '</div><div class="text-muted small">Students</div></div>';
                }
                if (d.total_outstanding !== undefined) {
                    summaryHtml += '<div class="col-4 text-center"><div class="fs-2 fw-bold">₹' + Number(d.total_outstanding).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</div><div class="text-muted small">Outstanding</div></div>';
                }
                if (d.notifications_created !== undefined) {
                    summaryHtml += '<div class="col-4 text-center"><div class="fs-2 fw-bold">' + d.notifications_created + '</div><div class="text-muted small">Notifications</div></div>';
                }
                summaryHtml += '</div>';
                $resultSummary.html(summaryHtml);

                if (d.results && d.results.length > 0) {
                    let tableHtml = '<div class="table-responsive" style="max-height:300px;overflow-y:auto;">' +
                        '<table class="table table-sm table-bordered"><thead class="table-light"><tr><th>Student</th><th>Class</th><th class="text-end">Outstanding</th><th>Status</th></tr></thead><tbody>';
                    d.results.forEach(function (r) {
                        const badge = r.reminder_status === 'sent'
                            ? '<span class="badge bg-success"><i class="ti ti-check me-1"></i>Sent</span>'
                            : '<span class="badge bg-warning"><i class="ti ti-alert me-1"></i>' + escHtml(r.reminder_status) + '</span>';
                        tableHtml += '<tr><td>' + escHtml(r.name) + '</td><td>' + escHtml(r.class) + '</td><td class="text-end">₹' + Number(r.outstanding).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</td><td>' + badge + '</td></tr>';
                    });
                    tableHtml += '</tbody></table></div>';
                    $resultDetail.html(tableHtml);
                } else {
                    $resultDetail.empty();
                }

                $doneBtn.removeClass('d-none');
                $backBtn.text('Close');
            },
            error: function (xhr) {
                showStep($error);
                $errorMsg.text(xhr.responseJSON?.message || 'Execution failed.');
                $runBtn.removeClass('d-none');
            }
        });
    });
});
</script>
@endpush
