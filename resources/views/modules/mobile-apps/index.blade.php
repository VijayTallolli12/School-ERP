@extends('layouts.admin')

@section('title', 'Mobile Apps')
@section('page-title', 'Mobile Apps')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Mobile Apps</li>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-12">
            <p class="text-muted mb-3" style="font-size:.95rem;">
                Download the School ERP mobile applications and learn how to use them.
            </p>
        </div>

        @foreach ($apps as $app)
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card h-100" style="border-radius:var(--erp-card-radius);box-shadow:var(--erp-card-shadow);">
                    <div class="card-body d-flex flex-column text-center">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mx-auto mb-3 bg-primary bg-opacity-10 text-primary" style="width:64px;height:64px;font-size:1.75rem;">
                            <i class="ti {{ $app['icon'] }}"></i>
                        </div>

                        <h5 class="fw-semibold mb-2">{{ $app['name'] }}</h5>
                        <p class="text-muted small flex-grow-1 mb-3" style="font-size:.85rem;line-height:1.55;">{{ $app['description'] }}</p>

                        <div class="d-grid gap-2">
                            @if (!empty($app['apk_url']))
                                <a href="{{ $app['apk_url'] }}" target="_blank" rel="noopener" class="btn btn-primary btn-sm" style="border-radius:var(--erp-btn-radius);">
                                    <i class="ti ti-download me-1"></i> Download APK
                                </a>
                            @else
                                <button type="button" class="btn btn-primary btn-sm disabled" disabled style="border-radius:var(--erp-btn-radius);">
                                    <i class="ti ti-download me-1"></i> Coming Soon
                                </button>
                            @endif

                            @if (!empty($app['video_url']))
                                <button type="button" class="btn btn-light btn-sm watch-tutorial" data-title="{{ $app['name'] }}" data-video="{{ $app['video_url'] }}" style="border:1px solid var(--erp-border-color);border-radius:var(--erp-btn-radius);">
                                    <i class="ti ti-video me-1"></i> Watch Tutorial
                                </button>
                            @else
                                <button type="button" class="btn btn-light btn-sm disabled" disabled style="border:1px solid var(--erp-border-color);border-radius:var(--erp-btn-radius);">
                                    <i class="ti ti-video me-1"></i> Tutorial Coming Soon
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card mt-4" style="border-radius:var(--erp-card-radius);box-shadow:var(--erp-card-shadow);">
        <div class="card-header">
            <h5 class="fw-semibold mb-0"><i class="ti ti-help text-primary me-2"></i>App Help &amp; Tutorials</h5>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">
                Learn how to get started with each mobile application. Open any tutorial below to watch the guided video.
            </p>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>App</th>
                            <th class="text-end">Tutorial</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($apps as $app)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary" style="width:36px;height:36px;font-size:1.1rem;flex-shrink:0;">
                                            <i class="ti {{ $app['icon'] }}"></i>
                                        </span>
                                        <span class="fw-medium">{{ $app['name'] }}</span>
                                    </div>
                                </td>
                                <td class="text-end">
                                    @if (!empty($app['video_url']))
                                        <button type="button" class="btn btn-outline-primary btn-sm watch-tutorial" data-title="{{ $app['name'] }}" data-video="{{ $app['video_url'] }}" style="border-radius:var(--erp-btn-radius);">
                                            <i class="ti ti-video me-1"></i> How to Use
                                        </button>
                                    @else
                                        <span class="badge bg-light text-secondary">Tutorial Coming Soon</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="tutorialModal" tabindex="-1" aria-labelledby="tutorialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width:min(840px, 94vw);">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tutorialModalLabel"><i class="ti ti-video me-1 text-primary"></i>How to Use</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6 class="fw-semibold mb-3" id="tutorialModalAppName"></h6>
                    <div class="ratio ratio-16x9" style="background:#000;border-radius:var(--erp-card-radius);overflow:hidden;">
                        <video id="tutorialVideo" controls playsinline style="width:100%;height:100%;object-fit:contain;"></video>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border:1px solid var(--erp-border-color);">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const tutorialModalEl = document.getElementById('tutorialModal');
        const tutorialVideo = document.getElementById('tutorialVideo');
        const tutorialModal = bootstrap.Modal.getOrCreateInstance(tutorialModalEl);

        document.querySelectorAll('.watch-tutorial').forEach((btn) => {
            btn.addEventListener('click', () => {
                document.getElementById('tutorialModalAppName').textContent = btn.dataset.title;
                tutorialVideo.src = btn.dataset.video;
                tutorialVideo.load();
                tutorialModal.show();
            });
        });

        tutorialModalEl.addEventListener('hidden.bs.modal', () => {
            tutorialVideo.pause();
            tutorialVideo.removeAttribute('src');
            tutorialVideo.load();
        });
    })();
</script>
@endpush
