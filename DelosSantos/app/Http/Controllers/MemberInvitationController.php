<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcceptMemberInvitationRequest;
use App\Models\AuditLog;
use App\Models\MemberInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class MemberInvitationController extends Controller
{
    public function show(string $token): View|RedirectResponse
    {
        $invitation = MemberInvitation::findUsableByToken($token);

        if (! $invitation) {
            return redirect()->route('login.member')
                ->withErrors(['email' => 'This member invitation is invalid, expired, or has already been used.']);
        }

        return view('auth.accept-member-invitation', compact('invitation', 'token'));
    }

    public function accept(AcceptMemberInvitationRequest $request, string $token): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $token): void {
                $invitation = MemberInvitation::with(['member', 'user'])
                    ->where('token_hash', hash('sha256', $token))
                    ->lockForUpdate()
                    ->first();

                if (! $invitation || ! $invitation->isUsable()) {
                    throw ValidationException::withMessages([
                        'password' => 'This member invitation is invalid, expired, or has already been used.',
                    ]);
                }

                $user = $invitation->user;
                $user->forceFill([
                    'password' => Hash::make($request->validated('password')),
                    'email_verified_at' => now(),
                    'status' => 'pending',
                ])->save();

                $invitation->member?->forceFill(['status' => 'pending'])->save();
                $invitation->forceFill(['consumed_at' => now()])->save();

                AuditLog::record('member.invitation.accepted', [
                    'member_id' => $invitation->member_id,
                    'role' => $user->role,
                ], $user, $user);
            });
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return redirect()->route('login.member')
            ->with('success', 'Invitation accepted. Sign in, complete payment, and wait for administrator approval.');
    }
}
