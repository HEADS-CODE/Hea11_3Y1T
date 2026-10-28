<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Puihaha E-Learning Platform')</title>
    <meta name="description" content="Puihaha membership management platform">
    <script>if (localStorage.getItem('puihaha-theme') === 'dark') document.documentElement.classList.add('dark-mode');</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="platform-page">
    <div class="platform-shell">
        <header class="platform-topbar">
            <a class="platform-brand" href="{{ route('members.index') }}"><span class="brand-mark">P</span><span><strong>Puihaha</strong><small>E-learning platform</small></span></a>
            <nav class="platform-nav" aria-label="Main navigation">
                <a class="{{ request()->routeIs('members.*') ? 'is-active' : '' }}" href="{{ route('members.index') }}">Members</a>
                <a class="{{ request()->routeIs('categories.*') ? 'is-active' : '' }}" href="{{ route('categories.index') }}">Categories</a>
                <a class="{{ request()->routeIs('levels.*') ? 'is-active' : '' }}" href="{{ route('levels.index') }}">Levels</a>
                <a href="{{ route('tasks.index') }}">Tasks <span class="nav-arrow">↗</span></a>
            </nav>
            <button class="theme-toggle" type="button" data-theme-toggle aria-pressed="false" title="Switch to dark mode"><span class="theme-toggle-icon" data-theme-icon aria-hidden="true">☾</span><span data-theme-label>Dark mode</span></button>
        </header>
        <main class="platform-content">
            @if (session('success'))<div class="platform-alert alert-success">{{ session('success') }}</div>@endif
            @if (session('error'))<div class="platform-alert alert-error">{{ session('error') }}</div>@endif
            @if ($errors->any())<div class="platform-alert alert-error">Please review the highlighted fields and try again.</div>@endif
            @yield('content')
        </main>
        <footer class="platform-footer"><span>Puihaha</span> Membership management · Built with Laravel</footer>
    </div>
</body>
</html>
