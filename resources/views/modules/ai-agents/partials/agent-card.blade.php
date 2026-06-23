<div class="col-md-6 col-lg-4">
    <div class="card h-100 border-{{ $agent['config']['color'] ?? 'primary' }}">
        <div class="card-body text-center d-flex flex-column">
            <div class="mb-3">
                <span class="avatar avatar-xl bg-{{ $agent['config']['color'] ?? 'primary' }} text-white rounded-3">
                    <i class="ti ti-{{ $agent['config']['icon'] ?? 'robot' }} fs-1"></i>
                </span>
            </div>
            <h5 class="card-title">{{ $agent['config']['label'] ?? ucwords(str_replace('_', ' ', $name)) }}</h5>
            <p class="card-text text-muted small flex-grow-1">{{ $agent['description'] }}</p>
            <div class="mt-auto">
                @if(!empty($agent['config']['tags']))
                    <div class="d-flex justify-content-center gap-2 mb-2">
                        @foreach($agent['config']['tags'] as $tag)
                            <span class="badge bg-info"><i class="ti ti-tag me-1"></i>{{ $tag }}</span>
                        @endforeach
                    </div>
                @endif

                @if($stats)
                    <div class="row text-center small mb-2 g-0 border rounded">
                        <div class="col-4 border-end py-1">
                            <div class="fw-bold">{{ $stats->total_records ?? 0 }}</div>
                            <div class="text-muted" style="font-size:10px;">Records</div>
                        </div>
                        <div class="col-4 border-end py-1">
                            <div class="fw-bold text-success">{{ $stats->success_count ?? 0 }}</div>
                            <div class="text-muted" style="font-size:10px;">Success</div>
                        </div>
                        <div class="col-4 py-1">
                            <div class="fw-bold text-danger">{{ $stats->failure_count ?? 0 }}</div>
                            <div class="text-muted" style="font-size:10px;">Failures</div>
                        </div>
                    </div>
                    @if($stats->last_run)
                        <div class="text-muted small mb-2">Last run: {{ \Illuminate\Support\Carbon::parse($stats->last_run)->diffForHumans() }}</div>
                    @endif
                @endif

                <button type="button" class="btn btn-{{ $agent['config']['color'] ?? 'primary' }} w-100 run-agent-btn"
                        data-agent="{{ $name }}"
                        data-label="{{ $agent['config']['label'] ?? ucwords(str_replace('_', ' ', $name)) }}"
                        data-description="{{ $agent['description'] }}"
                        data-config='@json($agent['config'])'>
                    <i class="ti ti-player-play me-1"></i> Run Agent
                </button>
            </div>
        </div>
    </div>
</div>
