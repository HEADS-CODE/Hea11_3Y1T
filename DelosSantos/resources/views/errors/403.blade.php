<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Access boundary | Puihaha</title>
        @include('components.brand-head')
        @vite(['resources/css/app.css'])
    </head>
    <body class="error-page">
        @php
            $activeUser = auth()->user();
            $portalUrl = $activeUser?->isAdmin() ? route('admin.dashboard') : route('tasks.index');
            $portalLabel = $activeUser?->isAdmin() ? 'Return to administrator dashboard' : 'Return to member tasks';
            $roleLabel = $activeUser?->isAdmin() ? 'administrator' : 'member';
        @endphp
        <main class="error-card" role="main">
            <x-brand-logo variant="symbol" size="52" />
            <span class="eyebrow">Puihaha access boundary</span>
            <p class="error-code">403</p>
            <h1>This page belongs to another portal.</h1>
            @if ($activeUser)
                <p>Your current session is signed in as a <strong>{{ $roleLabel }}</strong>. This protected page is reserved for the other role, so nothing was changed.</p>
                <a class="button button-primary" href="{{ $portalUrl }}">{{ $portalLabel }} <span>↗</span></a>
            @else
                <p>Your session is not signed in. Please use the member login page to continue.</p>
                <a class="button button-primary" href="{{ route('login.member') }}">Return to member login <span>↗</span></a>
            @endif
        </main>
    </body>
</html>
