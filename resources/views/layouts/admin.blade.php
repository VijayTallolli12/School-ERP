<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | {{ setting('school_name', 'School ERP') }}</title>
    @if($favicon = setting('favicon'))
        <link rel="icon" type="image/x-icon" href="{{ $favicon }}">
    @endif
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" crossorigin="anonymous"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --primary: {{ setting('school.primary_color', '#8C52FF') }};
            --primary-alternate: color-mix(in srgb, var(--primary), black 30%);
            --secondary: {{ setting('school.secondary_color', '#C5FF52') }};
            --text-color: {{ setting('school.text_color', '#1e293b') }};
            
            --bs-primary: var(--primary);
            --bs-primary-rgb: color-mix(in srgb, var(--primary), transparent 0%);
            
            --erp-primary: {{ setting('school.primary_color', '#7755CC') }};
            --erp-primary-hover: {{ setting('school.primary_hover_color', '#6848B8') }};
            --erp-primary-light: {{ setting('school.primary_light_color', '#F7F4FD') }};
        }
    </style>
    @stack('styles')
</head>
<body class="layout-fixed layout-navbar-fixed sidebar-expand-lg">
<div class="app-wrapper">
    @include('layouts.partials.navbar')
    @include('layouts.partials._announcement_banner')
    @include('layouts.partials.sidebar')

    <main class="app-main">
        <div class="app-content mt-3">
            <div class="container-fluid">
                @include('layouts.partials.flash')
                @yield('page-tabs')
                @yield('content')
            </div>
        </div>
    </main>

    <footer class="app-footer">
        <div class="float-end d-none d-sm-inline">{{ setting('footer_text', 'School ERP') }}</div>
        <strong>&copy; {{ now()->year }} {{ setting('school_name', 'School ERP') }}.</strong>
    </footer>
</div>
@include('modules.ai-assistant.modal')
@stack('modals')
@stack('scripts')
</body>
</html>
