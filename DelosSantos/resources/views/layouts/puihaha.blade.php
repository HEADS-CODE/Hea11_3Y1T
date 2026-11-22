<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Puihaha E-Learning Platform')</title>
    <meta name="description" content="Puihaha membership management platform">
    <script>if (localStorage.getItem('puihaha-theme') === 'dark') document.documentElement.classList.add('dark-mode');</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="platform-page">
    <div class="platform-shell">
        @auth @include('layouts._topbar') @endauth
        <main class="platform-content">
            @include('layouts._toasts')
            @include('components.validation-summary')
            @yield('content')
        </main>
        <footer class="platform-footer"><span>Puihaha</span> Membership management · Built with Laravel</footer>
    </div>
    @stack('page_scripts')
</body>
</html>
