<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Rules\ProjectPassword;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function showForgot(string $role): View
    {
        abort_unless(in_array($role, [User::ROLE_MEMBER, User::ROLE_ADMIN], true), 404);

        return view('auth.forgot-password-v2', compact('role'));
    }

    public function sendResetLink(Request $request, string $role): RedirectResponse
    {
        abort_unless(in_array($role, [User::ROLE_MEMBER, User::ROLE_ADMIN], true), 404);

        $validated = $request->validate(['email' => ['required', 'email']]);
        $throttleKey = Str::lower($validated['email']).'|'.$request->ip().'|'.$role.'|reset';
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return back()->withErrors(['email' => 'Too many recovery attempts. Please try again later.']);
        }
        RateLimiter::hit($throttleKey, 60);
        $broker = $role === User::ROLE_ADMIN ? 'admins' : 'members';
        Password::broker($broker)->sendResetLink([
            'email' => strtolower(trim($validated['email'])),
            'role' => $role,
        ]);

        return back()->with('success', 'If that account exists for this role, a reset link has been sent.');
    }

    public function showReset(Request $request, string $role, string $token): View
    {
        abort_unless(in_array($role, [User::ROLE_MEMBER, User::ROLE_ADMIN], true), 404);

        return view('auth.reset-password-v2', ['role' => $role, 'token' => $token, 'email' => $request->string('email')->toString()]);
    }

    public function reset(Request $request, string $role): RedirectResponse
    {
        abort_unless(in_array($role, [User::ROLE_MEMBER, User::ROLE_ADMIN], true), 404);

        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ProjectPassword::rules(),
        ]);
        $throttleKey = Str::lower($validated['email']).'|'.$request->ip().'|'.$role.'|reset-submit';
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return back()->withErrors(['email' => 'Too many recovery attempts. Please try again later.']);
        }
        RateLimiter::hit($throttleKey, 60);
        $broker = $role === User::ROLE_ADMIN ? 'admins' : 'members';
        $status = Password::broker($broker)->reset([
            'email' => strtolower(trim($validated['email'])),
            'password' => $validated['password'],
            'password_confirmation' => $validated['password_confirmation'],
            'token' => $validated['token'],
            'role' => $role,
        ], function (User $user, string $password): void {
            $user->forceFill(['password' => Hash::make($password), 'remember_token' => null])->save();
            event(new PasswordReset($user));
            AuditLog::record('auth.password_reset', ['role' => $user->role], null, $user);
        });

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => 'This reset link is invalid or expired.']);
        }

        return redirect()->route($role === User::ROLE_ADMIN ? 'login.admin' : 'login.member')->with('success', 'Your password has been reset.');
    }
}
