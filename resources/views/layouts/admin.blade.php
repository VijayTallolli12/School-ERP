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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">
    @include('layouts.partials.navbar')
    @include('layouts.partials._announcement_banner')
    @include('layouts.partials.sidebar')

    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <h3 class="mb-0">@yield('page-title', 'Dashboard')</h3>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end mb-0">
                            @yield('breadcrumbs')
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="app-content">
            <div class="container-fluid">
                @include('layouts.partials.flash')
                @yield('content')
            </div>
        </div>
    </main>

    <footer class="app-footer">
        <div class="float-end d-none d-sm-inline">{{ setting('footer_text', 'School ERP') }}</div>
        <strong>&copy; {{ now()->year }} {{ setting('school_name', 'School ERP') }}.</strong>
    </footer>
</div>
@stack('modals')
@stack('scripts')
</body>
</html>
