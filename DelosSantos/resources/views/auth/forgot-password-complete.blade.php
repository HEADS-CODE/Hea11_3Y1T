<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Forgot Password | Puihaha</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body class="auth-page auth-{{ $role }}">
<main class="auth-shell">
    <section class="auth-scene"><span class="auth-kicker">Account recovery</span><h1>Find your way<br><em>back in.</em></h1><p>We will send a role-specific reset link to the email address on the account.</p><span class="auth-orbit" aria-hidden="true">✦</span></section>
    <section class="auth-card">
        <a class="auth-brand" href="{{ route($role === 'admin' ? 'login.admin' : 'login.member') }}"><span class="brand-mark">P</span><span><strong>Puihaha</strong><small>{{ ucfirst($role) }} recovery</small></span></a>
        <div class="auth-heading"><span class="eyebrow">{{ ucfirst($role) }} account</span><h2>Reset your <em>password.</em></h2><p>Enter the email used for this role.</p></div>
        @if (session('success'))
            <div class="auth-alert auth-alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="auth-alert auth-alert-error">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route($role === 'admin' ? 'password.email.admin' : 'password.email.member') }}" class="auth-form">
            @csrf
            <div class="auth-field"><label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus></div>
            <button class="auth-submit" type="submit">Send reset link <span>↗</span></button>
        </form>
        <div class="auth-switch"><a href="{{ route($role === 'admin' ? 'login.admin' : 'login.member') }}">Return to sign in</a></div>
    </section>
</main>
</body>
</html>
