@props(['title' => 'WELCOME BACK', 'subtitle' => 'Sign In'])

@php
    $bgImage = setting('auth_background_image');
    $schoolName = setting('school_name', 'School ERP');
    $primaryColor = setting('school.primary_color', '#7755CC');
    $primaryHoverColor = setting('school.primary_hover_color', '#6848B8');
    $primaryLightColor = setting('school.primary_light_color', '#F7F4FD');
    $textColor = setting('school.text_color', '#17151C');
    $textMutedColor = setting('school.text_muted_color', '#5B5565');
    $borderColor = setting('school.border_color', '#DDD9E2');
    $authBgColor = setting('school.auth_background_color', '#F7F4FD');
@endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
<style>
:root {
    --auth-primary: {{ $primaryColor }};
    --auth-primary-hover: {{ $primaryHoverColor }};
    --auth-primary-light: {{ $primaryLightColor }};
    --auth-text: {{ $textColor }};
    --auth-text-muted: {{ $textMutedColor }};
    --auth-border: {{ $borderColor }};
    --auth-bg-color: {{ $authBgColor }};
}

.material-symbols-rounded {
  font-variation-settings:
  'FILL' 0,
  'wght' 300,
  'GRAD' 0,
  'opsz' 24;
}

.auth-v2 {
    font-family: 'Inter', sans-serif;
    background-color: #ffffff;
}

.auth-v2-left {
    flex: 0 0 100%;
}

@media (min-width: 992px) {
    .auth-v2-left {
        flex: 0 0 50% !important;
        max-width: 50%;
    }
}

.auth-v2-right {
    background-color: var(--auth-bg-color);
    border-radius: 24px;
    margin: 16px;
    overflow: hidden;
}

.auth-v2 .carousel-indicators {
    bottom: 2rem;
    margin-bottom: 0;
}

.auth-v2 .carousel-indicators [data-bs-target] {
    background-color: var(--auth-primary);
    opacity: 0.2;
    width: 24px;
    height: 6px;
    border: none;
    border-radius: 4px;
    margin-right: 4px;
    margin-left: 4px;
}

.auth-v2 .carousel-indicators .active {
    opacity: 1;
    background-color: var(--auth-primary);
}

.auth-v2 .form-control:focus {
    border-color: var(--auth-primary);
    box-shadow: 0 0 0 0.25rem rgba(119, 85, 204, 0.25);
}

.auth-v2 .form-check-input:checked {
    background-color: var(--auth-primary);
    border-color: var(--auth-primary);
}

/* Premium Cinematic Animations */
.auth-v2 .carousel-fade .carousel-item {
    transition: opacity 0.8s ease-in-out !important;
}

.auth-v2 .carousel-item p,
.auth-v2 .carousel-item h2,
.auth-v2 .carousel-item h3 {
    opacity: 0;
    transform: scale(1.05) translateY(10px);
    transition: all 0.8s cubic-bezier(0.215, 0.61, 0.355, 1);
    transform-origin: left center;
}

.auth-v2 .carousel-item.active p {
    opacity: 1;
    transform: scale(1) translateY(0);
    transition-delay: 0.2s;
}

.auth-v2 .carousel-item.active h2,
.auth-v2 .carousel-item.active h3 {
    opacity: 1;
    transform: scale(1) translateY(0);
    transition-delay: 0.4s;
}

.auth-v2-left::-webkit-scrollbar {
    width: 4px;
}
.auth-v2-left::-webkit-scrollbar-track {
    background: transparent;
}
.auth-v2-left::-webkit-scrollbar-thumb {
    background-color: rgba(0, 0, 0, 0.1);
    border-radius: 10px;
}
</style>
@endpush

<div class="auth-v2 position-relative d-flex flex-column flex-lg-row vh-100 p-0 overflow-hidden">
    
    <!-- Left Authentication Panel -->
    <div class="auth-v2-left position-relative d-flex flex-column justify-content-center align-items-center px-4 bg-white h-100" style="z-index: 1;">
        
        <div class="w-100" style="max-width: 440px;">
            <div class="text-center mb-5">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4" 
                     style="width: 88px; height: 88px; background-color: var(--auth-primary-light); border: none; position: relative;">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-white" 
                         style="width: 64px; height: 64px; box-shadow: 0 4px 14px rgba(0,0,0,0.03);">
                        <span class="material-symbols-rounded" style="font-size: 32px; color: var(--auth-primary);">person</span>
                    </div>
                </div>

                <h1 class="fw-bold mb-2" style="color: var(--auth-text); font-size: 32px; letter-spacing: -0.5px;">
                    {{ $title }}
                </h1>
                <p class="mb-4" style="color: var(--auth-text-muted); font-size: 16px;">
                    {{ $subtitle }}
                </p>
            </div>
            
            <div class="text-start">
                {{ $slot }}
            </div>
        </div>
        
    </div>

    <!-- Right Promotional Panel -->
    <div class="position-relative d-none d-lg-flex flex-grow-1 auth-v2-right flex-column justify-content-center" 
         style="@if($bgImage) background: url('{{ $bgImage }}') center/contain no-repeat;background-color:#F3EEFC @else background-color: var(--auth-bg-color); @endif">
        
        <!-- Promotional Slider -->
        <div id="authPromotionalSlider" class="carousel slide carousel-fade h-100 w-100 d-flex flex-column justify-content-center align-items-center" data-bs-ride="carousel" data-bs-interval="3500">
            
            <div class="carousel-inner w-100 text-center h-100 d-flex align-items-center" style="z-index: 2;">
                <!-- Slide 1 -->
                <div class="carousel-item active w-100">
                    <h3 class="mb-1" style="color: var(--auth-text); font-size: 24px; font-weight: 400;">Manage Better. Teach Better.</h3>
                    <h3 class="mb-5" style="color: var(--auth-primary); font-size: 24px; font-weight: 400;">Grow Better.</h3>
                    
                    <div style="height: 300px;"></div> <!-- Spacer for orbital image area -->
                    
                    <p class="mt-5 mx-auto" style="color: var(--auth-text-muted); max-width: 500px; font-size: 16px;">
                        Everything you need to manage your school, connect your community, and keep learning on track.
                    </p>
                </div>
                <!-- Slide 2 -->
                <div class="carousel-item w-100">
                    <h3 class="mb-1" style="color: var(--auth-text); font-size: 24px; font-weight: 400;">Seamless Communication.</h3>
                    <h3 class="mb-5" style="color: var(--auth-primary); font-size: 24px; font-weight: 400;">Stay Connected.</h3>
                    
                    <div style="height: 300px;"></div> <!-- Spacer for orbital image area -->
                    
                    <p class="mt-5 mx-auto" style="color: var(--auth-text-muted); max-width: 500px; font-size: 16px;">
                        Bring your entire school community together with simple communication. Connect Teachers, Parents & Students seamlessly.
                    </p>
                </div>
                <!-- Slide 3 -->
                <div class="carousel-item w-100">
                    <h3 class="mb-1" style="color: var(--auth-text); font-size: 24px; font-weight: 400;">Data-Driven Insights.</h3>
                    <h3 class="mb-5" style="color: var(--auth-primary); font-size: 24px; font-weight: 400;">Make Better Decisions.</h3>
                    
                    <div style="height: 300px;"></div> <!-- Spacer for orbital image area -->
                    
                    <p class="mt-5 mx-auto" style="color: var(--auth-text-muted); max-width: 500px; font-size: 16px;">
                        Get meaningful insights into school activities and analytics. Make Better Decisions With Better Data.
                    </p>
                </div>
            </div>

            <!-- Carousel Indicators -->
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#authPromotionalSlider" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#authPromotionalSlider" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#authPromotionalSlider" data-bs-slide-to="2" aria-label="Slide 3"></button>
            </div>
        </div>
    </div>
</div>
