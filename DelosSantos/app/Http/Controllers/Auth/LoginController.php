<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showMemberLogin(): View
    {
        return view('auth.login-v2', ['role' => User::ROLE_MEMBER]);
    }

    public function showAdminLogin(): View
    {
        return view('auth.login-v2', ['role' => User::ROLE_ADMIN]);
    }

    public function loginMember(Request $request): RedirectResponse
    {
        return $this->authenticate($request, User::ROLE_MEMBER);
    }

    public function loginAdmin(Request $request): RedirectResponse
    {
        return $this->authenticate($request, User::ROLE_ADMIN);
    }

    public function logout(Request $request): RedirectResponse
    {
        if ($request->user()) {
            AuditLog::record('auth.logout', ['role' => $request->user()->role], null, $request->user());
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.member')->with('success', 'You have been signed out.');
    }

    private function authenticate(Request $request, string $role): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
        $credentials['email'] = strtolower(trim($credentials['email']));
        $credentials['role'] = $role;
        $throttleKey = Str::lower($credentials['email']).'|'.$request->ip().'|'.$role;

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return back()->withErrors(['email' => 'The provided credentials are incorrect.'])->onlyInput('email');
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);

            return back()
                ->withErrors(['email' => 'The provided credentials are incorrect.'])
                ->onlyInput('email');
        }

        RateLimiter::clear($throttleKey);

        $request->session()->regenerate();
        $user = $request->user();
        AuditLog::record('auth.login.success', ['role' => $role], null, $user);

        if ($user->needsEmailVerification()) {
            return redirect()->route('verification.notice');
        }

        if ($user->isAwaitingApproval()) {
            return redirect()->route('approval.pending');
        }

        if ($user->isInactiveMember()) {
            return redirect()->route('account.inactive');
        }

        return redirect()->route($user->isAdmin() ? 'members.index' : 'tasks.index');
    }
}
