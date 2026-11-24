<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('code') | Puihaha</title>
        @include('components.brand-head')
        @vite(['resources/css/app.css'])
    </head>
    <body class="error-page">
        @php
            $activeUser = auth()->user();
            $portalUrl = $activeUser?->isAdmin() ? route('admin.dashboard') : route('tasks.index');
            $portalLabel = $activeUser?->isAdmin() ? 'Return to administrator dashboard' : 'Return to member portal';
            $fallbackUrl = $activeUser ? $portalUrl : route('login.member');
            $fallbackLabel = $activeUser ? $portalLabel : 'Return to member login';
        @endphp

        <main class="error-card" role="main">
            <x-brand-logo variant="symbol" size="52" />
            <span class="eyebrow">@yield('eyebrow', 'Puihaha system notice')</span>
            <p class="error-code">@yield('code')</p>
            <h1>@yield('title')</h1>
            <p>@yield('message')</p>
            <a class="button button-primary" href="{{ $fallbackUrl }}">{{ $fallbackLabel }} <span>↗</span></a>
        </main>
    </body>
</html>
