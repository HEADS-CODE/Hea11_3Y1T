<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\MembershipTransactionService;
use App\Notifications\PortalNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function index(): View
    {
        $users = User::with('member')->where('status', User::STATUS_PENDING)->orderBy('created_at')->get();

        return view('admin.approvals', compact('users'));
    }

    public function approve(User $user): RedirectResponse
    {
        if ($user->status !== User::STATUS_PENDING) {
            return back()->with('error', 'This account is no longer awaiting approval.');
        }

        if (! $user->email_verified_at) {
            return back()->with('error', 'The account must confirm its email before approval.');
        }

        if ($user->isMember()) {
            $transaction = $user->member?->transactions()->latest()->first();

            if (! $transaction || $transaction->status !== 'paid') {
                return back()->with('error', 'A member must have a fully paid transaction before approval.');
            }

            app(MembershipTransactionService::class)->approve($transaction, auth()->id());
        }

        $user->forceFill(['status' => User::STATUS_ACTIVE, 'approved_by_user_id' => auth()->id(), 'approved_at' => now()])->save();

        if ($user->member && $user->member->status === 'pending') {
            $user->member->update(['status' => 'active']);
        }

        AuditLog::record('account.approved', ['role' => $user->role], $user, auth()->user());
        $user->notify(new PortalNotification('Account approved', 'Your Puihaha account is approved and ready to use.', $user->isAdmin() ? route('members.index') : route('tasks.index')));

        return back()->with('success', 'Account approved.');
    }

    public function reject(User $user): RedirectResponse
    {
        $user->forceFill(['status' => User::STATUS_REJECTED, 'approved_by_user_id' => auth()->id(), 'approved_at' => null])->save();

        if ($user->member) {
            $user->member->update(['status' => 'inactive']);
        }

        AuditLog::record('account.rejected', ['role' => $user->role], $user, auth()->user());
        $user->notify(new PortalNotification('Account approval update', 'Your account approval request was not approved. Please contact the administrator for next steps.', $user->isAdmin() ? route('login.admin') : route('login.member')));

        return back()->with('success', 'Account rejected.');
    }
}
