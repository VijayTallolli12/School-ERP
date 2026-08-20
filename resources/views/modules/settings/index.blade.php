@extends('layouts.admin')

@section('title', 'Settings')
@section('page-title', 'Settings')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Settings</li>
@endsection

@php
    $settings = $school->settings ?? [];
    $schoolSettings = data_get($settings, 'school', []);
    $academicSettings = data_get($settings, 'academic', []);
    $attendanceSettings = data_get($academicSettings, 'attendance', []);
    $emailSettings = data_get($settings, 'email', []);
    $paymentSettings = data_get($settings, 'payment', []);
    //$logoUrl = $school->logo_path ? Storage::url($school->logo_path) : null;
    $logoUrl = $school->logo_path ? asset('storage/'.$school->logo_path) : null;
    $faviconPath = data_get($schoolSettings, 'favicon_path');
    $faviconUrl = $faviconPath ? asset('storage/'.$faviconPath) : null;
    
    $authBgPath = data_get($schoolSettings, 'auth_background_path');
    $authBgUrl = $authBgPath ? asset('storage/'.$authBgPath) : null;
@endphp

@section('content')
    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="ajax-form" id="settingsForm">
        @csrf

        <div class="card">
            <div class="card-header p-0 border-bottom-0">
                <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="school-tab" data-bs-toggle="tab" data-bs-target="#school-pane" type="button" role="tab"><i class="ti ti-school me-1"></i>School</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="academic-tab" data-bs-toggle="tab" data-bs-target="#academic-pane" type="button" role="tab"><i class="ti ti-book me-1"></i>Academic</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="system-tab" data-bs-toggle="tab" data-bs-target="#system-pane" type="button" role="tab"><i class="ti ti-settings me-1"></i>System</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="email-tab" data-bs-toggle="tab" data-bs-target="#email-pane" type="button" role="tab"><i class="ti ti-mail me-1"></i>Email</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="payment-tab" data-bs-toggle="tab" data-bs-target="#payment-pane" type="button" role="tab"><i class="ti ti-credit-card me-1"></i>Payment</button>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content" id="settingsTabsContent">
                    <div class="tab-pane fade show active" id="school-pane" role="tabpanel" aria-labelledby="school-tab">
                        <div class="row g-3">
                            <div class="col-lg-8">
                                <div class="card mb-3">
                                    <div class="card-header"><h5 class="fw-semibold mb-0"><i class="ti ti-school text-primary me-2"></i>School Details</h5></div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">School Name</label>
                                                <input type="text" name="school[name]" class="form-control" value="{{ old('school.name', $school->name) }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Principal Name</label>
                                                <input type="text" name="school[principal_name]" class="form-control" value="{{ old('school.principal_name', data_get($schoolSettings, 'principal_name')) }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Phone</label>
                                                <input type="text" name="school[phone]" class="form-control" value="{{ old('school.phone', $school->phone) }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Email</label>
                                                <input type="email" name="school[email]" class="form-control" value="{{ old('school.email', $school->email) }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Website</label>
                                                <input type="url" name="school[website]" class="form-control" value="{{ old('school.website', data_get($schoolSettings, 'website')) }}" placeholder="https://example.com">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Address</label>
                                                <textarea name="school[address]" class="form-control" rows="3">{{ old('school.address', $school->address) }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card mb-3">
                                    <div class="card-header"><h5 class="fw-semibold mb-0"><i class="ti ti-palette text-primary me-2"></i>Brand Assets</h5></div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Logo</label>
                                            <div class="border rounded bg-body-tertiary d-flex align-items-center justify-content-center mb-2" style="height: 120px;">
                                                <img id="logoPreview" src="{{ $logoUrl ?: '' }}" alt="Logo preview" class="{{ $logoUrl ? '' : 'd-none' }}" style="max-height: 96px; max-width: 100%;">
                                                <span id="logoEmpty" class="text-secondary {{ $logoUrl ? 'd-none' : '' }}">No logo</span>
                                            </div>
                                            <input type="file" name="school[logo]" class="form-control image-preview-input" data-preview="#logoPreview" data-empty="#logoEmpty" accept="image/*">
                                            @if($logoUrl)
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" name="school[remove_logo]" value="1" id="removeLogo">
                                                <label class="form-check-label text-danger small" for="removeLogo">Remove existing logo</label>
                                            </div>
                                            @endif
                                        </div>
                                        <div>
                                            <label class="form-label">Favicon</label>
                                            <div class="border rounded bg-body-tertiary d-flex align-items-center justify-content-center mb-2" style="height: 72px;">
                                                <img id="faviconPreview" src="{{ $faviconUrl ?: '' }}" alt="Favicon preview" class="{{ $faviconUrl ? '' : 'd-none' }}" style="max-height: 40px; max-width: 40px;">
                                                <span id="faviconEmpty" class="text-secondary {{ $faviconUrl ? 'd-none' : '' }}">No favicon</span>
                                            </div>
                                            <input type="file" name="school[favicon]" class="form-control image-preview-input" data-preview="#faviconPreview" data-empty="#faviconEmpty" accept="image/*,.ico">
                                            @if($faviconUrl)
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" name="school[remove_favicon]" value="1" id="removeFavicon">
                                                <label class="form-check-label text-danger small" for="removeFavicon">Remove existing favicon</label>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>                            
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-12">
                                <div class="card mb-3">
                                    <div class="card-header"><h5 class="fw-semibold mb-0"><i class="ti ti-color-swatch text-primary me-2"></i>Theme & Branding</h5></div>
                                    <div class="card-body">
                                        <div class="mb-4">
                                            <label class="form-label">Auth Background Image</label>
                                            <div class="border rounded bg-body-tertiary d-flex align-items-center justify-content-center mb-2 overflow-hidden" style="height: 120px; position: relative;">
                                                <img id="authBgPreview" src="{{ $authBgUrl ?: '' }}" alt="Background preview" class="{{ $authBgUrl ? '' : 'd-none' }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                <span id="authBgEmpty" class="text-secondary {{ $authBgUrl ? 'd-none' : '' }}">No background image</span>
                                            </div>
                                            <input type="file" name="school[auth_background]" class="form-control image-preview-input" data-preview="#authBgPreview" data-empty="#authBgEmpty" accept="image/*">
                                            @if($authBgUrl)
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" name="school[remove_auth_background]" value="1" id="removeAuthBg">
                                                <label class="form-check-label text-danger small" for="removeAuthBg">Remove existing background image</label>
                                            </div>
                                            @endif
                                            <small class="text-muted d-block mt-1">Shown on the right side of the Login and Forgot Password screens.</small>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label">Auth Background Color / Gradient</label>
                                            <textarea name="school[auth_background_color]" class="form-control font-monospace" rows="4" placeholder="e.g. #8C52FF or linear-gradient(...)">{{ old('school.auth_background_color', data_get($schoolSettings, 'auth_background_color')) }}</textarea>
                                            <small class="text-muted d-block mt-1">Accepts hex codes, rgba, or valid CSS gradients (e.g. <code>linear-gradient(180deg, #1b1626 0%, #54102f 100%)</code>). This is used as the background if no image is uploaded.</small>
                                        </div>

                                        <hr>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Primary Color</label>
                                                <div class="d-flex align-items-center gap-2">
                                                    <input type="color" name="school[primary_color]" class="form-control form-control-color" style="width: 50px; padding: 0.375rem;" value="{{ old('school.primary_color', data_get($schoolSettings, 'primary_color', '#7755CC')) }}" title="Choose primary color" oninput="$(this).next().val(this.value.toUpperCase())">
                                                    <input type="text" class="form-control font-monospace text-uppercase" value="{{ old('school.primary_color', data_get($schoolSettings, 'primary_color', '#7755CC')) }}" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Primary Hover Color</label>
                                                <div class="d-flex align-items-center gap-2">
                                                    <input type="color" name="school[primary_hover_color]" class="form-control form-control-color" style="width: 50px; padding: 0.375rem;" value="{{ old('school.primary_hover_color', data_get($schoolSettings, 'primary_hover_color', '#6848B8')) }}" title="Choose primary hover color" oninput="$(this).next().val(this.value.toUpperCase())">
                                                    <input type="text" class="form-control font-monospace text-uppercase" value="{{ old('school.primary_hover_color', data_get($schoolSettings, 'primary_hover_color', '#6848B8')) }}" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Primary Light Color</label>
                                                <div class="d-flex align-items-center gap-2">
                                                    <input type="color" name="school[primary_light_color]" class="form-control form-control-color" style="width: 50px; padding: 0.375rem;" value="{{ old('school.primary_light_color', data_get($schoolSettings, 'primary_light_color', '#F7F4FD')) }}" title="Choose primary light color" oninput="$(this).next().val(this.value.toUpperCase())">
                                                    <input type="text" class="form-control font-monospace text-uppercase" value="{{ old('school.primary_light_color', data_get($schoolSettings, 'primary_light_color', '#F7F4FD')) }}" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Text Color (Dark)</label>
                                                <div class="d-flex align-items-center gap-2">
                                                    <input type="color" name="school[text_color]" class="form-control form-control-color" style="width: 50px; padding: 0.375rem;" value="{{ old('school.text_color', data_get($schoolSettings, 'text_color', '#17151C')) }}" title="Choose text color" oninput="$(this).next().val(this.value.toUpperCase())">
                                                    <input type="text" class="form-control font-monospace text-uppercase" value="{{ old('school.text_color', data_get($schoolSettings, 'text_color', '#17151C')) }}" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Text Color (Muted)</label>
                                                <div class="d-flex align-items-center gap-2">
                                                    <input type="color" name="school[text_muted_color]" class="form-control form-control-color" style="width: 50px; padding: 0.375rem;" value="{{ old('school.text_muted_color', data_get($schoolSettings, 'text_muted_color', '#5B5565')) }}" title="Choose muted text color" oninput="$(this).next().val(this.value.toUpperCase())">
                                                    <input type="text" class="form-control font-monospace text-uppercase" value="{{ old('school.text_muted_color', data_get($schoolSettings, 'text_muted_color', '#5B5565')) }}" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Border Color</label>
                                                <div class="d-flex align-items-center gap-2">
                                                    <input type="color" name="school[border_color]" class="form-control form-control-color" style="width: 50px; padding: 0.375rem;" value="{{ old('school.border_color', data_get($schoolSettings, 'border_color', '#DDD9E2')) }}" title="Choose border color" oninput="$(this).next().val(this.value.toUpperCase())">
                                                    <input type="text" class="form-control font-monospace text-uppercase" value="{{ old('school.border_color', data_get($schoolSettings, 'border_color', '#DDD9E2')) }}" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <small class="text-muted d-block mt-2">These colors apply to the authentication pages and other branded interfaces.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="academic-pane" role="tabpanel" aria-labelledby="academic-tab">
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header"><h5 class="fw-semibold mb-0"><i class="ti ti-book text-primary me-2"></i>Academic Settings</h5></div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Current Academic Year</label>
                                            <select name="academic[current_academic_year_id]" class="form-select">
                                                <option value="">Select academic year</option>
                                                @foreach($academicYears as $year)
                                                    <option value="{{ $year->id }}" @selected((string) old('academic.current_academic_year_id', data_get($academicSettings, 'current_academic_year_id', $academicYears->firstWhere('is_active', true)?->id)) === (string) $year->id)>{{ $year->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Grading System</label>
                                            <select name="academic[grading_system]" class="form-select" required>
                                                <option value="percentage" @selected(old('academic.grading_system', data_get($academicSettings, 'grading_system', 'percentage')) === 'percentage')>Percentage</option>
                                                <option value="grade_points" @selected(old('academic.grading_system', data_get($academicSettings, 'grading_system')) === 'grade_points')>Grade Points</option>
                                                <option value="letter_grades" @selected(old('academic.grading_system', data_get($academicSettings, 'grading_system')) === 'letter_grades')>Letter Grades</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header"><h5 class="fw-semibold mb-0"><i class="ti ti-user-check text-primary me-2"></i>Attendance Settings</h5></div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Default Attendance Status</label>
                                            <select name="academic[attendance][default_status]" class="form-select" required>
                                                <option value="present" @selected(old('academic.attendance.default_status', data_get($attendanceSettings, 'default_status', 'present')) === 'present')>Present</option>
                                                <option value="absent" @selected(old('academic.attendance.default_status', data_get($attendanceSettings, 'default_status')) === 'absent')>Absent</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Minimum Attendance %</label>
                                            <input type="number" name="academic[attendance][minimum_percentage]" class="form-control" min="0" max="100" step="0.01" value="{{ old('academic.attendance.minimum_percentage', data_get($attendanceSettings, 'minimum_percentage', 75)) }}" required>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input type="hidden" name="academic[attendance][allow_late_marking]" value="0">
                                            <input class="form-check-input" type="checkbox" name="academic[attendance][allow_late_marking]" value="1" id="allowLateMarking" @checked((bool) old('academic.attendance.allow_late_marking', data_get($attendanceSettings, 'allow_late_marking', false)))>
                                            <label class="form-check-label" for="allowLateMarking">Allow late attendance marking</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="system-pane" role="tabpanel" aria-labelledby="system-tab">
                        <div class="card">
                            <div class="card-header"><h5 class="fw-semibold mb-0"><i class="ti ti-settings text-primary me-2"></i>System Settings</h5></div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Timezone</label>
                                        <select name="system[timezone]" class="form-select" required>
                                            @foreach($timezones as $timezone)
                                                <option value="{{ $timezone }}" @selected(old('system.timezone', $school->timezone) === $timezone)>{{ $timezone }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Currency</label>
                                        <select name="system[currency]" class="form-select" required>
                                            @foreach($currencies as $currency)
                                                <option value="{{ $currency }}" @selected(old('system.currency', $school->currency) === $currency)>{{ $currency }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Date Format</label>
                                        <select name="system[date_format]" class="form-select" required>
                                            @foreach($dateFormats as $format => $sample)
                                                <option value="{{ $format }}" @selected(old('system.date_format', $school->date_format) === $format)>{{ $format }} ({{ $sample }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="email-pane" role="tabpanel" aria-labelledby="email-tab">
                        <div class="card">
                            <div class="card-header"><h5 class="fw-semibold mb-0"><i class="ti ti-mail text-primary me-2"></i>SMTP Settings</h5></div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">SMTP Host</label>
                                        <input type="text" name="email[smtp_host]" class="form-control" placeholder="mail.example.com" value="{{ old('email.smtp_host', data_get($emailSettings, 'smtp_host')) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">SMTP Port</label>
                                        <input type="number" name="email[smtp_port]" class="form-control" placeholder="587" min="1" max="65535" value="{{ old('email.smtp_port', data_get($emailSettings, 'smtp_port')) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Encryption</label>
                                        <select name="email[smtp_encryption]" class="form-select">
                                            <option value="">None</option>
                                            <option value="tls" @selected(old('email.smtp_encryption', data_get($emailSettings, 'smtp_encryption')) === 'tls')>TLS</option>
                                            <option value="ssl" @selected(old('email.smtp_encryption', data_get($emailSettings, 'smtp_encryption')) === 'ssl')>SSL</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Username</label>
                                        <input type="text" name="email[smtp_username]" class="form-control" placeholder="user@example.com" value="{{ old('email.smtp_username', data_get($emailSettings, 'smtp_username')) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Password</label>
                                        <input type="password" name="email[smtp_password]" class="form-control" placeholder="{{ data_get($emailSettings, 'smtp_password') ? 'Saved. Enter a new password to replace.' : '' }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="payment-pane" role="tabpanel" aria-labelledby="payment-tab">
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="fw-semibold mb-0"><i class="ti ti-credit-card text-primary me-2"></i>Razorpay</h5>
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="payment[razorpay][enabled]" value="0">
                                            <input class="form-check-input" type="checkbox" name="payment[razorpay][enabled]" value="1" id="razorpayEnabled" @checked((bool) old('payment.razorpay.enabled', data_get($paymentSettings, 'razorpay.enabled', false)))>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Key</label>
                                            <input type="text" name="payment[razorpay][key]" class="form-control" value="{{ old('payment.razorpay.key', data_get($paymentSettings, 'razorpay.key')) }}">
                                        </div>
                                        <div>
                                            <label class="form-label">Secret</label>
                                            <input type="password" name="payment[razorpay][secret]" class="form-control" placeholder="{{ data_get($paymentSettings, 'razorpay.secret') ? 'Saved. Enter a new secret to replace.' : '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="fw-semibold mb-0"><i class="ti ti-credit-card text-primary me-2"></i>Stripe</h5>
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="payment[stripe][enabled]" value="0">
                                            <input class="form-check-input" type="checkbox" name="payment[stripe][enabled]" value="1" id="stripeEnabled" @checked((bool) old('payment.stripe.enabled', data_get($paymentSettings, 'stripe.enabled', false)))>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Publishable Key</label>
                                            <input type="text" name="payment[stripe][key]" class="form-control" value="{{ old('payment.stripe.key', data_get($paymentSettings, 'stripe.key')) }}">
                                        </div>
                                        <div>
                                            <label class="form-label">Secret</label>
                                            <input type="password" name="payment[stripe][secret]" class="form-control" placeholder="{{ data_get($paymentSettings, 'stripe.secret') ? 'Saved. Enter a new secret to replace.' : '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end">
                <button type="submit" class="btn btn-primary py-2">
                    <i class="ti ti-device-floppy me-1"></i> Save Settings
                </button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
$(function () {
    $('.image-preview-input').on('change', function () {
        const input = this;
        const file = input.files && input.files[0];
        const preview = $($(input).data('preview'));
        const empty = $($(input).data('empty'));

        if (!file) {
            return;
        }

        const reader = new FileReader();
        reader.onload = function (event) {
            preview.attr('src', event.target.result).removeClass('d-none');
            empty.addClass('d-none');
        };
        reader.readAsDataURL(file);
    });

    initTabPersistence('#settingsTabs');
});
</script>
@endpush
