<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\EmailVerificationToken;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class VerificationController extends Controller
{
    public function verify(User $user, string $token): RedirectResponse
    {
        $record = EmailVerificationToken::where('user_id', $user->id)->first();

        if (! $record || $record->consumed_at || $record->expires_at->isPast() || ! hash_equals($record->token_hash, hash('sha256', $token))) {
            return redirect()->route($user->isAdmin() ? 'login.admin' : 'login.member')->withErrors(['email' => 'This verification link is invalid or expired.']);
        }

        DB::transaction(function () use ($record, $user): void {
            $record->update(['consumed_at' => now()]);
            $user->forceFill(['email_verified_at' => now()])->save();
            AuditLog::record('auth.email_verified', ['role' => $user->role], null, $user);
        });

        return redirect()->route($user->isAdmin() ? 'login.admin' : 'login.member')->with('success', 'Email confirmed. You may now sign in.');
    }

    public function resend(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $key = Str::lower($user->email).'|'.$request->ip().'|verification-resend';
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()->withErrors(['email' => 'Too many verification requests. Please try again later.']);
        }
        RateLimiter::hit($key, 60);
        $token = EmailVerificationToken::issueFor($user);
        $user->notify(new VerifyEmailNotification($token));

        return back()->with('success', 'A new confirmation link has been sent.');
    }
}
