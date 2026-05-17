<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'School ERP') | {{ setting('school_name', 'School ERP') }}</title>
    @if($favicon = setting('favicon'))
        <link rel="icon" type="image/x-icon" href="{{ $favicon }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page">
    <main class="auth-card">
        @yield('content')
    </main>
</body>
</html>
