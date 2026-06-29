<div class="col-md-6 col-lg-3">
    <div class="aiw-agent-card">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="aiw-agent-icon" style="background:rgba({{ $agent['config']['color'] === 'success' ? '22,163,74' : ($agent['config']['color'] === 'warning' ? '217,119,6' : ($agent['config']['color'] === 'danger' ? '220,38,38' : '37,99,235')) }},.1);color:{{ $agent['config']['color'] === 'success' ? '#16a34a' : ($agent['config']['color'] === 'warning' ? '#d97706' : ($agent['config']['color'] === 'danger' ? '#dc2626' : '#2563eb')) }};">
                <i class="ti ti-{{ $agent['config']['icon'] ?? 'robot' }}"></i>
            </div>
            <div class="flex-grow-1 min-w-0">
                <div class="aiw-agent-name">{{ $agent['config']['label'] ?? ucwords(str_replace('_', ' ', $name)) }}</div>
                <div class="aiw-agent-desc mt-1">{{ $agent['description'] }}</div>
            </div>
        </div>

        @if($stats)
            <div class="d-flex gap-3 mb-3">
                <div class="aiw-agent-stat"><strong>{{ $stats->total_records ?? 0 }}</strong> records</div>
                <div class="aiw-agent-stat">
                    <span style="color:#16a34a;">●</span>
                    <strong style="color:#16a34a;">{{ $stats->success_count ?? 0 }}</strong> ok
                </div>
                @if(($stats->failure_count ?? 0) > 0)
                    <div class="aiw-agent-stat">
                        <span style="color:#ef4444;">●</span>
                        <strong style="color:#ef4444;">{{ $stats->failure_count }}</strong> fail
                    </div>
                @endif
                @if($stats->last_run)
                    <div class="aiw-agent-stat" style="color:#94a3b8;">{{ \Illuminate\Support\Carbon::parse($stats->last_run)->diffForHumans() }}</div>
                @endif
            </div>
        @else
            <div class="mb-3" style="font-size:0.75rem;color:#94a3b8;">No executions yet</div>
        @endif

        <button type="button" class="btn btn-light w-100 run-agent-btn" style="border:1px solid var(--erp-border-color);font-weight:600;border-radius:0.625rem;"
                data-agent="{{ $name }}"
                data-label="{{ $agent['config']['label'] ?? ucwords(str_replace('_', ' ', $name)) }}"
                data-description="{{ $agent['description'] }}"
                data-config='@json($agent['config'])'>
            <i class="ti ti-player-play me-1" style="color:{{ $agent['config']['color'] === 'success' ? '#16a34a' : ($agent['config']['color'] === 'warning' ? '#d97706' : ($agent['config']['color'] === 'danger' ? '#dc2626' : '#2563eb')) }};"></i>
            Run Agent
        </button>
    </div>
</div>
