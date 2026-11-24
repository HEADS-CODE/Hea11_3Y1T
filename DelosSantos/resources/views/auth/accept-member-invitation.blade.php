<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accept Member Invitation | Puihaha</title>
    @include('components.brand-head')
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="auth-page auth-member">
<main class="auth-shell">
    <section class="auth-scene">
        <x-brand-logo variant="full" size="132" class="auth-scene-logo" />
        <span class="auth-kicker">Your Puihaha invitation</span>
        <h1>Make room for<br><em>what matters.</em></h1>
        <p>Set your password and confirm your email to continue with your membership application.</p>
        <span class="auth-orbit" aria-hidden="true">✦</span>
    </section>
    <section class="auth-card">
        <a class="auth-brand" href="{{ route('login.member') }}">
            <x-brand-logo variant="symbol" size="44" />
            <span><strong>Puihaha</strong><small>Member invitation</small></span>
        </a>
        <div class="auth-heading">
            <span class="eyebrow">Invitation <strong>accepted</strong></span>
            <h2>Set your <em>password.</em></h2>
            <p>This secure invitation is for {{ $invitation->user->email }} and expires {{ $invitation->expires_at->format('M d, Y · g:i A') }}.</p>
        </div>
        @include('components.validation-summary')
        <form method="POST" action="{{ route('member.invitation.accept', ['token' => $token]) }}" class="auth-form" data-live-validation data-password-policy>
            @csrf
            <div class="auth-field">
                <label for="password">Password <span>*</span></label>
                <div class="password-wrap">
                    <input id="password" name="password" type="password" required aria-describedby="password-help" autocomplete="new-password">
                    <button type="button" class="password-toggle" data-password-toggle="password" aria-label="Show password" title="Show password"><svg class="password-toggle-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.4-5.5 9.5-5.5 9.5 5.5 9.5 5.5-3.4 5.5-9.5 5.5-9.5-5.5-9.5-9.5Z"></path><circle cx="12" cy="12" r="2.5"></circle><path class="eye-slash" d="m4 4 16 16"></path></svg></button>
                </div>
                <small id="password-help" class="field-hint">Use at least 8 characters with uppercase, lowercase, number, and symbol.</small>
                @error('password')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="auth-field">
                <label for="password_confirmation">Confirm password <span>*</span></label>
                <div class="password-wrap">
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
                    <button type="button" class="password-toggle" data-password-toggle="password_confirmation" aria-label="Show password" title="Show password"><svg class="password-toggle-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.4-5.5 9.5-5.5 9.5 5.5 9.5 5.5-3.4 5.5-9.5 5.5-9.5-5.5-9.5-5.5Z"></path><circle cx="12" cy="12" r="2.5"></circle><path class="eye-slash" d="m4 4 16 16"></path></svg></button>
                </div>
                @error('password_confirmation')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <p class="auth-state-note">Completing this invitation confirms the email address that received it. You will still need to complete payment before administrator approval.</p>
            <button class="auth-submit" type="submit">Set password and continue <span>↗</span></button>
        </form>
        <div class="auth-switch"><a href="{{ route('login.member') }}">Return to member login</a></div>
    </section>
</main>
@include('components.puihaha-footer')
@include('layouts._toasts')
</body>
</html>
