<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountReady
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login.member');
        }

        if ($user->isBlocked()) {
            Auth::logout();
            $request->session()->invalidate();

            return redirect()->route($user->isAdmin() ? 'login.admin' : 'login.member')
                ->withErrors(['email' => 'The provided credentials are incorrect.']);
        }

        if ($user->needsEmailVerification()) {
            return redirect()->route('verification.notice');
        }

        if ($user->isAwaitingApproval()) {
            return redirect()->route('approval.pending');
        }

        if ($user->isInactiveMember()) {
            return redirect()->route('account.inactive');
        }

        if (! $user->isActive()) {
            Auth::logout();

            return redirect()->route($user->isAdmin() ? 'login.admin' : 'login.member')
                ->withErrors(['email' => 'The provided credentials are incorrect.']);
        }

        return $next($request);
    }
}
