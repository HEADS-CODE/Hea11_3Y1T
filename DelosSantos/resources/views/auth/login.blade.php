<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ucfirst($role) }} Login | Puihaha</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page auth-{{ $role }}">
    <main class="auth-shell">
        <section class="auth-scene">
            <span class="auth-kicker">Puihaha E-learning Platform</span>
            <h1>{{ $role === 'admin' ? 'Guide the learning journey.' : 'Make room for what matters.' }}</h1>
            <p>{{ $role === 'admin' ? 'A calm control space for members, levels, and the work behind every learning journey.' : 'Your personal learning space, ready when you are.' }}</p>
            <span class="auth-orbit" aria-hidden="true">✦</span>
        </section>
        <section class="auth-card">
            <a class="auth-brand" href="{{ route($role === 'admin' ? 'login.admin' : 'login.member') }}"><span class="brand-mark">P</span><span><strong>Puihaha</strong><small>{{ ucfirst($role) }} access</small></span></a>
            <div class="auth-heading"><span class="eyebrow">{{ ucfirst($role) }} portal</span><h2>Welcome <em>back.</em></h2><p>Sign in with your {{ $role }} account to continue.</p></div>
            @if (session('success'))<div class="auth-alert auth-alert-success">{{ session('success') }}</div>@endif
            @if ($errors->any())<div class="auth-alert auth-alert-error">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route($role === 'admin' ? 'login.admin.submit' : 'login.member.submit') }}" class="auth-form">
                @csrf
                <div class="auth-field"><label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus></div>
                <div class="auth-field"><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password" required></div>
                <label class="auth-remember"><input type="checkbox" name="remember" value="1"> <span>Remember me</span></label>
                <button class="auth-submit" type="submit">Enter {{ $role === 'admin' ? 'administration' : 'my learning space' }} <span>↗</span></button>
            </form>
            <div class="auth-switch"><a href="{{ route($role === 'admin' ? 'password.request.admin' : 'password.request.member') }}">Forgot password?</a><br>{{ $role === 'admin' ? 'Need an administrator account?' : 'Need a member account?' }} <a href="{{ route($role === 'admin' ? 'register.admin' : 'register.member') }}">Register</a><br>{{ $role === 'admin' ? 'Are you a member?' : 'Are you an administrator?' }} <a href="{{ route($role === 'admin' ? 'login.member' : 'login.admin') }}">Use {{ $role === 'admin' ? 'member' : 'administrator' }} login</a></div>
        </section>
    </main>
</body>
</html>
