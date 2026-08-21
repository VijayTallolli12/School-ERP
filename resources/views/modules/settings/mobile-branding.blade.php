@extends('layouts.admin')

@section('title', 'Mobile Branding')
@section('page-title', 'Mobile Branding')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Settings</a></li>
    <li class="breadcrumb-item active">Mobile Branding</li>
@endsection

@php
    $primaryColor = $branding['primary_color'] ?? '#2563eb';
    $secondaryColor = $branding['secondary_color'] ?? '#64748b';
    $logoUrl = $branding['school_logo'] ?? null;
    $schoolName = $branding['school_name'] ?? config('app.name', 'School ERP');
    $appName = $branding['app_name'] ?? config('app.name', 'School ERP');
@endphp

@section('content')
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="fw-semibold mb-0"><i class="ti ti-palette text-primary me-2"></i>Brand Colors</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.mobile.branding.update') }}" method="POST" class="ajax-form" id="mobileBrandingForm">
                        @csrf

                        <div class="alert alert-info d-flex align-items-start">
                            <i class="ti ti-info-circle me-2 mt-1"></i>
                            <div>
                                <strong>Runtime branding</strong> — These colors are served to the Parent, Student, Teacher, and Driver mobile apps through the branding API. The mobile app falls back to these defaults automatically when ERP branding is unavailable.
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Primary Color</label>
                                <div class="input-group">
                                    <span class="input-group-text p-1">
                                        <input type="color" class="form-control form-control-color border-0 p-0 m-0" id="primaryColorPicker" value="{{ $primaryColor }}" style="width:32px;height:32px;">
                                    </span>
                                    <input type="text" name="brand[primary_color]" class="form-control" id="primaryColorInput" value="{{ $primaryColor }}" placeholder="#2563eb" pattern="#[0-9a-fA-F]{6}" maxlength="7" required>
                                </div>
                                <div class="form-text">Used for headers, buttons, splash background, and accents in all mobile apps.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Secondary Color</label>
                                <div class="input-group">
                                    <span class="input-group-text p-1">
                                        <input type="color" class="form-control form-control-color border-0 p-0 m-0" id="secondaryColorPicker" value="{{ $secondaryColor }}" style="width:32px;height:32px;">
                                    </span>
                                    <input type="text" name="brand[secondary_color]" class="form-control" id="secondaryColorInput" value="{{ $secondaryColor }}" placeholder="#64748b" pattern="#[0-9a-fA-F]{6}" maxlength="7" required>
                                </div>
                                <div class="form-text">Used for secondary text, badges, and muted UI elements.</div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-primary">Save Brand Colors</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="fw-semibold mb-0"><i class="ti ti-api text-primary me-2"></i>Branding API</h5>
                </div>
                <div class="card-body">
                    <p class="text-secondary mb-2">The mobile apps load branding from this endpoint on first launch, login, and pull-to-refresh.</p>

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <code class="d-inline-block bg-body-tertiary border rounded px-2 py-1" id="brandingEndpoint">/api/v1/branding?school_id={{ $school->id }}</code>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="copyEndpointBtn">Copy</button>
                        <a href="{{ url('/api/v1/branding?school_id=' . $school->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="ti ti-external-link me-1"></i> Open
                        </a>
                    </div>

                    <h6 class="text-muted text-uppercase small fw-semibold mb-2">Supported Headers / Params</h6>
                    <ul class="text-secondary mb-0">
                        <li><code>X-School-Id</code> — school context header</li>
                        <li><code>school_id</code> — query parameter</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="fw-semibold mb-0"><i class="ti ti-device-mobile text-primary me-2"></i>Mobile App Preview</h5>
                </div>
                <div class="card-body d-flex justify-content-center">
                    <div class="phone-preview" style="width: 260px; border-radius: 28px; border: 8px solid #1f2937; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.15); background: #ffffff;">
                        <div class="phone-header" style="background: {{ $primaryColor }}; padding: 28px 16px; text-align: center;">
                            @if($logoUrl)
                                <img src="{{ $logoUrl }}" alt="Logo" style="width: 56px; height: 56px; object-fit: contain; margin-bottom: 8px;">
                            @else
                                <div style="width: 56px; height: 56px; border-radius: 50%; background: {{ $primaryColor }}22; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold; margin: 0 auto 8px;">
                                    {{ strtoupper(mb_substr($schoolName, 0, 1)) }}
                                </div>
                            @endif
                            <div style="color: #fff; font-weight: bold; font-size: 16px;">{{ $appName }}</div>
                            <div style="color: rgba(255,255,255,0.85); font-size: 12px;">{{ $schoolName }}</div>
                        </div>
                        <div style="padding: 16px; background: #f8fafc;">
                            <div style="background: #fff; border-radius: 10px; padding: 12px; margin-bottom: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
                                <div style="font-weight: 600; font-size: 13px; color: #0f172a;">Welcome Back</div>
                                <div style="height: 10px; background: #e2e8f0; border-radius: 4px; margin-top: 8px;"></div>
                                <div style="height: 10px; background: #e2e8f0; border-radius: 4px; margin-top: 6px; width: 80%;"></div>
                                <div style="height: 34px; background: {{ $primaryColor }}; border-radius: 8px; margin-top: 12px;"></div>
                            </div>
                            <div style="background: #fff; border-radius: 10px; padding: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
                                <div style="font-weight: 600; font-size: 13px; color: #0f172a;">Dashboard</div>
                                <div style="display: flex; gap: 8px; margin-top: 10px;">
                                    <div style="flex: 1; height: 44px; background: {{ $secondaryColor }}22; border-radius: 8px;"></div>
                                    <div style="flex: 1; height: 44px; background: {{ $secondaryColor }}22; border-radius: 8px;"></div>
                                    <div style="flex: 1; height: 44px; background: {{ $secondaryColor }}22; border-radius: 8px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    function bindColorPicker(colorPickerId, textInputId) {
        const $picker = $(colorPickerId);
        const $input = $(textInputId);

        $picker.on('input', function () {
            $input.val($(this).val()).removeClass('is-invalid');
        });

        $input.on('input', function () {
            const val = $(this).val().trim();
            if (/^#[0-9a-fA-F]{6}$/.test(val)) {
                $picker.val(val);
                $(this).removeClass('is-invalid');
            }
        });
    }

    bindColorPicker('#primaryColorPicker', '#primaryColorInput');
    bindColorPicker('#secondaryColorPicker', '#secondaryColorInput');

    $('#copyEndpointBtn').on('click', function () {
        const text = $('#brandingEndpoint').text().trim();
        navigator.clipboard.writeText(text).then(function () {
            App.toast('success', 'Endpoint copied to clipboard.');
        }).catch(function () {
            App.toast('error', 'Could not copy endpoint.');
        });
    });
});
</script>
@endpush
