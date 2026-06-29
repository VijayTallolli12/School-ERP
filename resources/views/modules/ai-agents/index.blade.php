@extends('layouts.admin')

@section('title', 'AI Workspace')
@section('page-title', 'AI Workspace')

@section('content')
{{-- Hero Section --}}
<div class="aiw-hero">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <div class="aiw-hero-badge mb-3"><i class="ti ti-sparkles"></i> AI Operations Center</div>
            <h1>AI Workspace</h1>
            <p>Intelligent agents to automate attendance, fee collection, library management, and payroll processing</p>
        </div>
        <button type="button" class="btn btn-light px-4" data-bs-toggle="modal" data-bs-target="#askErpModal" style="background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);color:#fff;">
            <i class="ti ti-message me-1"></i> Ask ERP
        </button>
    </div>
</div>

{{-- Premium Metrics --}}
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="aiw-metric d-flex align-items-center gap-3">
            <div class="aiw-metric-icon" style="background:rgba(37,99,235,.1);color:#2563eb;">
                <i class="ti ti-robot"></i>
            </div>
            <div>
                <div class="aiw-metric-value">{{ count($agents) }}</div>
                <div class="aiw-metric-label">Agents Available</div>
                <div class="aiw-metric-sub">{{ $executions->sum('total_records') > 0 ? number_format($executions->sum('total_records')) . ' records processed' : 'Ready to deploy' }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="aiw-metric d-flex align-items-center gap-3">
            <div class="aiw-metric-icon" style="background:rgba(14,165,233,.1);color:#0ea5e9;">
                <i class="ti ti-clock-play"></i>
            </div>
            <div>
                <div class="aiw-metric-value">{{ $executions->sum(function($s) { return $s->success_count + $s->failure_count; }) }}</div>
                <div class="aiw-metric-label">Executions Today</div>
                <div class="aiw-metric-sub">{{ $executions->sum('success_count') }} succeeded</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="aiw-metric d-flex align-items-center gap-3">
            <div class="aiw-metric-icon" style="background:rgba(22,163,74,.1);color:#16a34a;">
                <i class="ti ti-percentage"></i>
            </div>
            <div>
                @php
                    $total = $executions->sum(function($s) { return $s->success_count + $s->failure_count; });
                    $rate = $total > 0 ? round(($executions->sum('success_count') / $total) * 100) : 100;
                @endphp
                <div class="aiw-metric-value">{{ $rate }}%</div>
                <div class="aiw-metric-label">Success Rate</div>
                <div class="aiw-metric-sub">{{ $executions->sum('failure_count') }} failed</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="aiw-metric d-flex align-items-center gap-3">
            <div class="aiw-metric-icon" style="background:rgba(139,92,246,.1);color:#8b5cf6;">
                <i class="ti ti-bell-ringing"></i>
            </div>
            <div>
                <div class="aiw-metric-value">{{ number_format($executions->sum('total_records')) }}</div>
                <div class="aiw-metric-label">Records Processed</div>
                <div class="aiw-metric-sub">{{ $executions->count() }} agents active</div>
            </div>
        </div>
    </div>
</div>

{{-- Quick Ask --}}
<div class="aiw-quick-ask d-flex align-items-center gap-3 mb-4" role="button" data-bs-toggle="modal" data-bs-target="#askErpModal">
    <i class="ti ti-sparkles" style="color:#2563eb;font-size:1.2rem;"></i>
    <input type="text" placeholder="Ask AI about your school data — attendance, fees, library, payroll..." readonly>
    <span class="aiw-hero-badge" style="background:rgba(37,99,235,.1);color:#2563eb;border:none;flex-shrink:0;">
        <i class="ti ti-corner-down-left"></i> Ask
    </span>
</div>

{{-- AI Agents Section --}}
<div class="aiw-divider"><span>AI Agents</span></div>

@if(!empty($agents))
    <div class="row g-3 mb-4">
        @foreach($agents as $name => $agent)
            @include('modules.ai-agents.partials.agent-card', [
                'name' => $name,
                'agent' => $agent,
                'stats' => $executions->get($name),
            ])
        @endforeach
    </div>
@else
    <div class="aiw-empty">
        <div class="aiw-empty-icon"><i class="ti ti-robot-off"></i></div>
        <h5>No agents registered</h5>
        <p>Configure your first AI agent to automate school operations.</p>
    </div>
@endif

{{-- Execution History Link --}}
<div class="d-flex justify-content-center mb-4">
    <a href="{{ route('admin.agents.history') }}" class="btn btn-light px-4" style="border:1px solid var(--erp-border-color);">
        <i class="ti ti-clock me-1"></i> View Execution History
        <i class="ti ti-chevron-right ms-1" style="font-size:0.8rem;"></i>
    </a>
</div>

{{-- Recent Executions --}}
@php
    $recentExecs = \App\Modules\AiAgents\Models\AgentExecution::query()
        ->with('user')
        ->latest('started_at')
        ->take(5)
        ->get();
@endphp

@if($recentExecs->isNotEmpty())
    <div class="aiw-divider"><span>Recent Executions</span></div>
    <div class="aiw-recent mb-4">
        @foreach($recentExecs as $exe)
            <div class="aiw-recent-item">
                <span class="ri-dot" style="background:{{ $exe->status === 'completed' ? '#16a34a' : ($exe->status === 'failed' ? '#ef4444' : '#f59e0b') }}"></span>
                <span class="ri-name">{{ ucwords(str_replace('_', ' ', $exe->agent_name)) }}</span>
                <span class="ri-meta">{{ $exe->user?->name ?? 'System' }}</span>
                <span class="ri-meta">{{ $exe->records_processed ?? 0 }} records</span>
                <span class="ri-meta">{{ $exe->started_at?->diffForHumans() ?? '—' }}</span>
                <span class="aiw-agent-badge" style="background:{{ $exe->status === 'completed' ? 'rgba(22,163,74,.1)' : 'rgba(239,68,68,.1)' }};color:{{ $exe->status === 'completed' ? '#16a34a' : '#ef4444' }};">
                    <i class="ti ti-{{ $exe->status === 'completed' ? 'check' : 'x' }}"></i> {{ ucfirst($exe->status) }}
                </span>
            </div>
        @endforeach
    </div>
@endif
@endsection

@push('modals')
<div class="modal fade aiw-modal" id="agentModal" tabindex="-1" aria-labelledby="agentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="agentModalTitle">
                    <i class="ti ti-robot ti-fw me-1"></i> <span id="agentModalTitleText">Agent</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                {{-- Step Indicator --}}
                <div class="aiw-step-indicator" id="stepIndicator">
                    <span class="step-pip" data-step="config"></span>
                    <span class="step-pip" data-step="preview"></span>
                    <span class="step-pip" data-step="confirm"></span>
                    <span class="step-pip" data-step="executing"></span>
                    <span class="step-pip" data-step="result"></span>
                </div>

                {{-- Step: Configure --}}
                <div id="agentStepConfig">
                    <p class="text-muted mb-3" style="font-size:0.85rem;" id="agentConfigDescription"></p>
                    <div id="agentConfigFields"></div>
                    <button type="button" class="btn btn-primary w-100" id="agentPreviewBtn" style="border-radius:0.625rem;">
                        <i class="ti ti-search me-1"></i> Preview
                    </button>
                </div>

                {{-- Step: Loading --}}
                <div id="agentStepLoading" class="d-none text-center py-5">
                    <div class="spinner-border text-primary" role="status" style="width:2rem;height:2rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted" style="font-size:0.85rem;">Processing...</p>
                </div>

                {{-- Step: Confirm (Analysis + Preview) --}}
                <div id="agentStepConfirm" class="d-none">
                    <div class="aiw-timeline">
                        <div class="aiw-timeline-step completed">
                            <span class="step-dot"><i class="ti ti-check" style="font-size:0.5rem;"></i></span>
                            <div class="step-label">Analysis</div>
                            <div class="step-content" id="agentPreviewSummary"></div>
                        </div>
                        <div class="aiw-timeline-step active">
                            <span class="step-dot"></span>
                            <div class="step-label">Preview</div>
                            <div class="step-content" id="agentPreviewDetail"></div>
                        </div>
                    </div>
                    <div class="mt-3 p-3" style="background:#fffbeb;border:1px solid #fde68a;border-radius:0.625rem;font-size:0.82rem;color:#92400e;" id="agentConfirmWarning"></div>
                </div>

                {{-- Step: Result --}}
                <div id="agentStepResult" class="d-none">
                    <div class="aiw-timeline">
                        <div class="aiw-timeline-step completed">
                            <span class="step-dot"><i class="ti ti-check" style="font-size:0.5rem;"></i></span>
                            <div class="step-label">Analysis</div>
                        </div>
                        <div class="aiw-timeline-step completed">
                            <span class="step-dot"><i class="ti ti-check" style="font-size:0.5rem;"></i></span>
                            <div class="step-label">Preview</div>
                        </div>
                        <div class="aiw-timeline-step completed">
                            <span class="step-dot"><i class="ti ti-check" style="font-size:0.5rem;"></i></span>
                            <div class="step-label">Execution</div>
                        </div>
                        <div class="aiw-timeline-step active">
                            <span class="step-dot"></span>
                            <div class="step-label">Results</div>
                            <div class="step-content">
                                <div class="alert alert-success mb-3" id="agentResultSummary" style="border-radius:0.625rem;border:none;"></div>
                                <div id="agentResultDetail"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step: Error --}}
                <div id="agentStepError" class="d-none">
                    <div class="p-3" style="background:#fef2f2;border:1px solid #fecaca;border-radius:0.625rem;color:#991b1b;">
                        <div class="d-flex align-items-start gap-2">
                            <i class="ti ti-alert-circle mt-1"></i>
                            <div>
                                <strong>Error</strong>
                                <p class="mb-0 mt-1" style="font-size:0.85rem;" id="agentErrorMessage">An error occurred.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" id="agentModalBack" data-bs-dismiss="modal" style="border:1px solid var(--erp-border-color);">
                    <i class="ti ti-arrow-left me-1"></i> Back
                </button>
                <button type="button" class="btn btn-execute d-none" id="agentRunBtn">
                    <i class="ti ti-player-play me-1"></i> Execute Agent
                </button>
                <button type="button" class="btn btn-success d-none" id="agentDoneBtn" data-bs-dismiss="modal" style="border-radius:0.625rem;">
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

    function getQueryParam(name) {
        return new URLSearchParams(window.location.search).get(name) || null;
    }

    function getExtraParams() {
        const params = new URLSearchParams(window.location.search);
        const extra = {};
        params.forEach(function (value, key) {
            if (key !== 'preselect') extra[key] = value;
        });
        return extra;
    }

    const $title = $('#agentModalTitleText');
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
    const $steps = $('.step-pip');

    let currentAgent = null;
    let currentParams = {};
    let currentData = null;

    function setStep(step) {
        $steps.each(function () {
            const s = $(this).data('step');
            $(this).removeClass('active done');
            if (s === step) $(this).addClass('active');
        });
        // Mark completed steps
        const order = ['config','preview','confirm','executing','result'];
        const idx = order.indexOf(step);
        if (idx > 0) {
            $steps.each(function () {
                const s = $(this).data('step');
                const si = order.indexOf(s);
                if (si < idx) $(this).addClass('done');
            });
        }
    }

    function resetUI() {
        $config.removeClass('d-none');
        $loading.addClass('d-none');
        $confirm.addClass('d-none');
        $result.addClass('d-none');
        $error.addClass('d-none');
        $runBtn.addClass('d-none');
        $doneBtn.addClass('d-none');
        $backBtn.text('Cancel').attr('data-bs-dismiss', 'modal');
        setStep('config');
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
                html += '<div class="aiw-field">';
                html += '<label>' + escHtml(param.label) + '</label>';
                if (param.type === 'select') {
                    let opts = '';
                    param.options.forEach(function (opt) {
                        const selected = opt.value == param.default ? ' selected' : '';
                        opts += '<option value="' + opt.value + '"' + selected + '>' + escHtml(opt.label) + '</option>';
                    });
                    html += '<select class="agent-param" data-key="' + key + '">' + opts + '</select>';
                } else if (param.type === 'date') {
                    html += '<input type="date" class="agent-param" data-key="' + key + '" value="' + escHtml(param.default || '') + '">';
                }
                html += '</div>';
            });
        }
        $fields.html(html);

        const extraParams = $(this).data('extraParams') || {};
        if (Object.keys(extraParams).length > 0) {
            $('.agent-param').each(function () {
                const key = $(this).data('key');
                if (extraParams[key] !== undefined) {
                    $(this).val(extraParams[key]);
                }
            });
        }

        $modal.modal('show');
    });

    $previewBtn.on('click', function () {
        const params = {};
        $('.agent-param').each(function () {
            params[$(this).data('key')] = $(this).val();
        });
        currentParams = params;

        showStep($loading);
        setStep('preview');

        var previewBase = '{{ route("admin.agents.preview", ["agent" => "__agent__"]) }}';
        $.ajax({
            url: previewBase.replace('__agent__', currentAgent),
            method: 'POST',
            data: $.extend({}, params, { _token: '{{ csrf_token() }}' }),
            success: function (res) {
                currentData = res.data;
                showStep($confirm);
                setStep('confirm');

                const d = res.data;

                // Analysis summary
                let analysisHtml = '';
                if (d.total_students !== undefined) {
                    const pct = d.total_students > 0 ? Math.round((d.present_count / d.total_students) * 100) : 0;
                    analysisHtml = '<div class="aiw-stat-grid">' +
                        '<div class="sg-item"><div class="sg-value">' + d.total_students + '</div><div class="sg-label">Total</div></div>' +
                        '<div class="sg-item"><div class="sg-value" style="color:#16a34a;">' + d.present_count + '</div><div class="sg-label">Present</div></div>' +
                        '<div class="sg-item"><div class="sg-value" style="color:#ef4444;">' + d.absent_count + '</div><div class="sg-label">Absent</div></div>';
                    if (d.late_count !== undefined) {
                        analysisHtml += '<div class="sg-item"><div class="sg-value" style="color:#d97706;">' + d.late_count + '</div><div class="sg-label">Late</div></div>';
                    }
                    analysisHtml += '<div class="sg-item"><div class="sg-value">' + pct + '%</div><div class="sg-label">Attendance</div></div></div>';
                } else if (d.total_overdue_books !== undefined) {
                    analysisHtml = '<div class="aiw-stat-grid">' +
                        '<div class="sg-item"><div class="sg-value">' + d.total_overdue_books + '</div><div class="sg-label">Overdue</div></div>' +
                        '<div class="sg-item"><div class="sg-value">' + d.total_borrowers + '</div><div class="sg-label">Borrowers</div></div>' +
                        '<div class="sg-item"><div class="sg-value" style="color:#d97706;">₹' + Number(d.total_fine_amount).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</div><div class="sg-label">Total Fine</div></div></div>';
                } else if (d.ready !== undefined) {
                    const monthName = new Date(d.year, d.month - 1).toLocaleString('default', { month: 'long' });
                    if (!d.ready && d.errors && d.errors.length > 0) {
                        analysisHtml = '<div style="color:#ef4444;font-size:0.85rem;"><strong>Validation Failed</strong><ul class="mb-0 mt-1" style="list-style:none;padding:0;">';
                        d.errors.forEach(function (e) { analysisHtml += '<li style="padding:0.15rem 0;">✗ ' + escHtml(e) + '</li>'; });
                        analysisHtml += '</ul></div>';
                    } else if (d.ready) {
                        analysisHtml = '<div class="aiw-stat-grid">' +
                            '<div class="sg-item"><div class="sg-value">' + d.total_employees + '</div><div class="sg-label">Employees</div></div>' +
                            '<div class="sg-item"><div class="sg-value" style="color:#2563eb;">₹' + Number(d.estimated_gross).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</div><div class="sg-label">Est. Gross</div></div>' +
                            '<div class="sg-item"><div class="sg-value" style="color:#ef4444;">₹' + Number(d.estimated_deductions).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</div><div class="sg-label">Est. Deductions</div></div>' +
                            '<div class="sg-item"><div class="sg-value" style="color:#16a34a;">₹' + Number(d.estimated_net).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</div><div class="sg-label">Est. Net</div></div></div>';
                    }
                } else {
                    let items = '';
                    if (d.student_count !== undefined) items += '<div class="sg-item"><div class="sg-value">' + d.student_count + '</div><div class="sg-label">Students</div></div>';
                    if (d.total_outstanding !== undefined) items += '<div class="sg-item"><div class="sg-value" style="color:#d97706;">₹' + Number(d.total_outstanding).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</div><div class="sg-label">Outstanding</div></div>';
                    if (items) analysisHtml = '<div class="aiw-stat-grid">' + items + '</div>';
                }

                let summaryHtml = '<div class="mb-2" style="font-size:0.78rem;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;color:#94a3b8;">Analysis Summary</div>';
                summaryHtml += analysisHtml || '<p class="text-muted mb-0" style="font-size:0.85rem;">Preview data loaded successfully.</p>';
                $previewSummary.html(summaryHtml);

                // Preview detail
                if (d.class_breakdown && d.class_breakdown.length > 0) {
                    let tbl = '<div style="font-size:0.78rem;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;color:#94a3b8;margin-bottom:0.5rem;">Class-wise Breakdown</div>';
                    tbl += '<div style="overflow-x:auto;"><table style="width:100%;border-collapse:collapse;font-size:0.82rem;"><thead><tr style="border-bottom:2px solid #e2e8f0;">';
                    tbl += '<th style="padding:0.4rem 0.5rem;text-align:left;font-weight:600;color:#475569;">Class</th>';
                    tbl += '<th style="padding:0.4rem 0.5rem;text-align:center;font-weight:600;color:#475569;">Total</th>';
                    tbl += '<th style="padding:0.4rem 0.5rem;text-align:center;font-weight:600;color:#16a34a;">Present</th>';
                    tbl += '<th style="padding:0.4rem 0.5rem;text-align:center;font-weight:600;color:#ef4444;">Absent</th></tr></thead><tbody>';
                    d.class_breakdown.forEach(function (c) {
                        tbl += '<tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:0.4rem 0.5rem;">' + escHtml(c.class_name) + '</td><td style="padding:0.4rem 0.5rem;text-align:center;">' + c.total + '</td><td style="padding:0.4rem 0.5rem;text-align:center;color:#16a34a;">' + c.present + '</td><td style="padding:0.4rem 0.5rem;text-align:center;color:#ef4444;">' + c.absent + '</td></tr>';
                    });
                    tbl += '</tbody></table></div>';
                    $previewDetail.html(tbl);
                } else if (d.students && d.students.length > 0) {
                    let isFee = d.students[0].balance !== undefined;
                    let tbl = '<div style="overflow-x:auto;max-height:200px;overflow-y:auto;"><table style="width:100%;border-collapse:collapse;font-size:0.8rem;"><thead><tr style="border-bottom:2px solid #e2e8f0;">';
                    tbl += '<th style="padding:0.35rem 0.5rem;text-align:left;font-weight:600;color:#475569;">Student</th><th style="padding:0.35rem 0.5rem;text-align:left;font-weight:600;color:#475569;">Class</th>';
                    tbl += isFee ? '<th style="padding:0.35rem 0.5rem;text-align:right;font-weight:600;color:#475569;">Outstanding</th>' : '<th style="padding:0.35rem 0.5rem;text-align:center;font-weight:600;color:#475569;">Parents</th>';
                    tbl += '</tr></thead><tbody>';
                    d.students.forEach(function (s) {
                        tbl += '<tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:0.35rem 0.5rem;">' + escHtml(s.name) + '</td><td style="padding:0.35rem 0.5rem;">' + escHtml(s.class) + '</td>';
                        if (isFee) {
                            tbl += '<td style="padding:0.35rem 0.5rem;text-align:right;">₹' + Number(s.balance).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</td>';
                        } else {
                            tbl += '<td style="padding:0.35rem 0.5rem;text-align:center;">' + (s.parents ? s.parents.length : 0) + '</td>';
                        }
                        tbl += '</tr>';
                    });
                    tbl += '</tbody></table></div>';
                    $previewDetail.html(tbl);
                } else if (d.items && d.items.length > 0) {
                    let tbl = '<div style="overflow-x:auto;max-height:200px;overflow-y:auto;"><table style="width:100%;border-collapse:collapse;font-size:0.8rem;"><thead><tr style="border-bottom:2px solid #e2e8f0;">';
                    tbl += '<th style="padding:0.35rem 0.5rem;text-align:left;font-weight:600;color:#475569;">Book</th><th style="padding:0.35rem 0.5rem;text-align:left;font-weight:600;color:#475569;">Borrower</th><th style="padding:0.35rem 0.5rem;text-align:center;font-weight:600;color:#475569;">Days Overdue</th><th style="padding:0.35rem 0.5rem;text-align:right;font-weight:600;color:#475569;">Fine</th></tr></thead><tbody>';
                    d.items.forEach(function (item) {
                        tbl += '<tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:0.35rem 0.5rem;">' + escHtml(item.book_title) + '</td><td style="padding:0.35rem 0.5rem;">' + escHtml(item.borrower_name) + '</td><td style="padding:0.35rem 0.5rem;text-align:center;">' + item.days_overdue + '</td><td style="padding:0.35rem 0.5rem;text-align:right;">₹' + Number(item.fine_amount).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</td></tr>';
                    });
                    tbl += '</tbody></table></div>';
                    $previewDetail.html(tbl);
                } else {
                    $previewDetail.html('<p class="text-muted mb-0" style="font-size:0.85rem;">No detailed data available.</p>');
                }

                if (d.ready === false) {
                    $confirmWarning.html('<i class="ti ti-alert-circle me-1"></i> <strong>Cannot proceed.</strong> Fix the validation errors before executing.');
                    $runBtn.addClass('d-none');
                } else {
                    $confirmWarning.html('<i class="ti ti-info-circle me-1"></i> Review the analysis above, then execute the agent.');
                    $runBtn.removeClass('d-none');
                }
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
        setStep('executing');
        $runBtn.addClass('d-none');

        var executeBase = '{{ route("admin.agents.execute", ["agent" => "__agent__"]) }}';
        $.ajax({
            url: executeBase.replace('__agent__', currentAgent),
            method: 'POST',
            data: $.extend({}, currentParams, { _token: '{{ csrf_token() }}' }),
            success: function (res) {
                showStep($result);
                setStep('result');

                const d = res.data;
                let isPayroll = d.payroll_run_id !== undefined;
                let summaryHtml;

                if (isPayroll && d.success) {
                    summaryHtml = '<div class="aiw-stat-grid">' +
                        '<div class="sg-item"><div class="sg-value">' + d.total_employees + '</div><div class="sg-label">Employees</div></div>' +
                        '<div class="sg-item"><div class="sg-value" style="color:#2563eb;">₹' + Number(d.total_gross).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</div><div class="sg-label">Gross</div></div>' +
                        '<div class="sg-item"><div class="sg-value" style="color:#ef4444;">₹' + Number(d.total_deductions).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</div><div class="sg-label">Deductions</div></div>' +
                        '<div class="sg-item"><div class="sg-value" style="color:#16a34a;">₹' + Number(d.total_net).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</div><div class="sg-label">Net</div></div>' +
                        '<div class="sg-item"><div class="sg-value">' + d.payslips_generated + '</div><div class="sg-label">Payslips</div></div>' +
                        '<div class="sg-item"><div class="sg-value" style="font-size:0.9rem;">#' + d.payroll_run_id + '</div><div class="sg-label">Run ID</div></div></div>';
                } else if (isPayroll && !d.success) {
                    summaryHtml = '<div style="color:#ef4444;font-size:0.85rem;"><strong>Payroll Failed</strong>';
                    if (d.errors && d.errors.length > 0) {
                        summaryHtml += '<ul class="mb-0 mt-1" style="list-style:none;padding:0;">';
                        d.errors.forEach(function (e) { summaryHtml += '<li style="padding:0.15rem 0;">✗ ' + escHtml(e) + '</li>'; });
                        summaryHtml += '</ul>';
                    }
                    summaryHtml += '</div>';
                } else {
                    let items = '';
                    if (d.total_students !== undefined) {
                        items += '<div class="sg-item"><div class="sg-value">' + d.total_students + '</div><div class="sg-label">Total</div></div>' +
                            '<div class="sg-item"><div class="sg-value" style="color:#16a34a;">' + d.present_count + '</div><div class="sg-label">Present</div></div>' +
                            '<div class="sg-item"><div class="sg-value" style="color:#ef4444;">' + d.absent_count + '</div><div class="sg-label">Absent</div></div>';
                    } else if (d.total_overdue_books !== undefined) {
                        items += '<div class="sg-item"><div class="sg-value">' + d.total_overdue_books + '</div><div class="sg-label">Overdue</div></div>' +
                            '<div class="sg-item"><div class="sg-value">' + d.total_borrowers + '</div><div class="sg-label">Borrowers</div></div>' +
                            '<div class="sg-item"><div class="sg-value" style="color:#d97706;">₹' + Number(d.total_fine_amount).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</div><div class="sg-label">Fin Collected</div></div>';
                    } else {
                        if (d.student_count !== undefined) items += '<div class="sg-item"><div class="sg-value">' + d.student_count + '</div><div class="sg-label">Students</div></div>';
                        if (d.total_outstanding !== undefined) items += '<div class="sg-item"><div class="sg-value" style="color:#d97706;">₹' + Number(d.total_outstanding).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</div><div class="sg-label">Outstanding</div></div>';
                    }
                    if (d.notifications_created !== undefined) {
                        items += '<div class="sg-item"><div class="sg-value">' + d.notifications_created + '</div><div class="sg-label">Notifications</div></div>';
                    }
                    if (!items) {
                        items = '<div class="sg-item"><div class="sg-value">' + (d.records_processed || 0) + '</div><div class="sg-label">Records</div></div>';
                    }
                    summaryHtml = '<div class="aiw-stat-grid">' + items + '</div>';
                }

                $resultSummary.html(summaryHtml);
                $resultDetail.empty();

                if (d.class_breakdown && d.class_breakdown.length > 0) {
                    let tbl = '<div style="margin-top:0.75rem;"><div style="font-size:0.78rem;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;color:#94a3b8;margin-bottom:0.5rem;">Class Breakdown</div>';
                    tbl += '<div style="overflow-x:auto;"><table style="width:100%;border-collapse:collapse;font-size:0.82rem;"><thead><tr style="border-bottom:2px solid #e2e8f0;">';
                    tbl += '<th style="padding:0.35rem 0.5rem;text-align:left;font-weight:600;color:#475569;">Class</th><th style="padding:0.35rem 0.5rem;text-align:center;font-weight:600;color:#475569;">Total</th><th style="padding:0.35rem 0.5rem;text-align:center;font-weight:600;color:#16a34a;">Present</th><th style="padding:0.35rem 0.5rem;text-align:center;font-weight:600;color:#ef4444;">Absent</th></tr></thead><tbody>';
                    d.class_breakdown.forEach(function (c) {
                        tbl += '<tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:0.35rem 0.5rem;">' + escHtml(c.class_name) + '</td><td style="padding:0.35rem 0.5rem;text-align:center;">' + c.total + '</td><td style="padding:0.35rem 0.5rem;text-align:center;color:#16a34a;">' + c.present + '</td><td style="padding:0.35rem 0.5rem;text-align:center;color:#ef4444;">' + c.absent + '</td></tr>';
                    });
                    tbl += '</tbody></table></div></div>';
                    $resultDetail.html(tbl);
                }

                if (d.results && d.results.length > 0) {
                    let isFeeResult = d.results[0].reminder_status !== undefined;
                    let isLibraryResult = d.results[0].book_title !== undefined;
                    let tbl = '<div style="margin-top:0.75rem;"><div style="font-size:0.78rem;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;color:#94a3b8;margin-bottom:0.5rem;">Detailed Results</div>';
                    tbl += '<div style="overflow-x:auto;max-height:250px;overflow-y:auto;"><table style="width:100%;border-collapse:collapse;font-size:0.8rem;"><thead><tr style="border-bottom:2px solid #e2e8f0;">';
                    if (isLibraryResult) {
                        tbl += '<th style="padding:0.35rem 0.5rem;text-align:left;font-weight:600;color:#475569;">Book</th><th style="padding:0.35rem 0.5rem;text-align:left;font-weight:600;color:#475569;">Borrower</th><th style="padding:0.35rem 0.5rem;text-align:center;font-weight:600;color:#475569;">Days Overdue</th><th style="padding:0.35rem 0.5rem;text-align:right;font-weight:600;color:#475569;">Fine</th><th style="padding:0.35rem 0.5rem;text-align:center;font-weight:600;color:#475569;">Status</th>';
                    } else if (isFeeResult) {
                        tbl += '<th style="padding:0.35rem 0.5rem;text-align:left;font-weight:600;color:#475569;">Student</th><th style="padding:0.35rem 0.5rem;text-align:left;font-weight:600;color:#475569;">Class</th><th style="padding:0.35rem 0.5rem;text-align:right;font-weight:600;color:#475569;">Outstanding</th><th style="padding:0.35rem 0.5rem;text-align:center;font-weight:600;color:#475569;">Status</th>';
                    } else {
                        tbl += '<th style="padding:0.35rem 0.5rem;text-align:left;font-weight:600;color:#475569;">Student</th><th style="padding:0.35rem 0.5rem;text-align:left;font-weight:600;color:#475569;">Class</th><th style="padding:0.35rem 0.5rem;text-align:center;font-weight:600;color:#475569;">Status</th>';
                    }
                    tbl += '</tr></thead><tbody>';
                    d.results.forEach(function (r) {
                        let badge = (r.notification_status === 'sent' || r.reminder_status === 'sent')
                            ? '<span style="display:inline-flex;align-items:center;gap:0.2rem;padding:0.15rem 0.5rem;border-radius:999px;font-size:0.68rem;font-weight:600;background:rgba(22,163,74,.1);color:#16a34a;"><i class="ti ti-check"></i> Sent</span>'
                            : '<span style="display:inline-flex;align-items:center;gap:0.2rem;padding:0.15rem 0.5rem;border-radius:999px;font-size:0.68rem;font-weight:600;background:rgba(245,158,11,.1);color:#d97706;"><i class="ti ti-alert"></i> ' + escHtml(r.notification_status || r.reminder_status) + '</span>';
                        tbl += '<tr style="border-bottom:1px solid #f1f5f9;">';
                        if (isLibraryResult) {
                            tbl += '<td style="padding:0.35rem 0.5rem;">' + escHtml(r.book_title) + '</td><td style="padding:0.35rem 0.5rem;">' + escHtml(r.borrower_name) + '</td><td style="padding:0.35rem 0.5rem;text-align:center;">' + r.days_overdue + '</td><td style="padding:0.35rem 0.5rem;text-align:right;">₹' + Number(r.fine_amount).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</td><td style="padding:0.35rem 0.5rem;text-align:center;">' + badge + '</td>';
                        } else if (isFeeResult) {
                            badge = r.reminder_status === 'sent'
                                ? '<span style="display:inline-flex;align-items:center;gap:0.2rem;padding:0.15rem 0.5rem;border-radius:999px;font-size:0.68rem;font-weight:600;background:rgba(22,163,74,.1);color:#16a34a;"><i class="ti ti-check"></i> Sent</span>'
                                : '<span style="display:inline-flex;align-items:center;gap:0.2rem;padding:0.15rem 0.5rem;border-radius:999px;font-size:0.68rem;font-weight:600;background:rgba(245,158,11,.1);color:#d97706;"><i class="ti ti-alert"></i> ' + escHtml(r.reminder_status) + '</span>';
                            tbl += '<td style="padding:0.35rem 0.5rem;">' + escHtml(r.name) + '</td><td style="padding:0.35rem 0.5rem;">' + escHtml(r.class) + '</td><td style="padding:0.35rem 0.5rem;text-align:right;">₹' + Number(r.outstanding).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</td><td style="padding:0.35rem 0.5rem;text-align:center;">' + badge + '</td>';
                        } else {
                            tbl += '<td style="padding:0.35rem 0.5rem;">' + escHtml(r.name) + '</td><td style="padding:0.35rem 0.5rem;">' + escHtml(r.class) + '</td><td style="padding:0.35rem 0.5rem;text-align:center;">' + badge + '</td>';
                        }
                        tbl += '</tr>';
                    });
                    tbl += '</tbody></table></div></div>';
                    $resultDetail.append(tbl);
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

    // Preselect auto-open
    const preselect = getQueryParam('preselect');
    if (preselect) {
        const btn = $('.run-agent-btn[data-agent="' + preselect + '"]');
        if (btn.length) {
            const extraParams = getExtraParams();
            btn.data('extraParams', extraParams);
            btn.trigger('click');
        }
    }
});
</script>
@endpush
