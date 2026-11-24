<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\MembershipTransaction;
use App\Models\User;
use App\Services\MembershipTransactionService;
use App\Notifications\PortalNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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
        if ($user->status === User::STATUS_ACTIVE) {
            return back()->with('success', 'This account is already approved.');
        }

        if ($user->status !== User::STATUS_PENDING) {
            return back()->with('error', 'This account is no longer awaiting approval.');
        }

        if (! $user->email_verified_at) {
            return back()->with('error', 'The account must confirm its email before approval.');
        }

        try {
            DB::transaction(function () use ($user): void {
                if ($user->isMember()) {
                    $application = $user->member?->transactions()
                        ->with(['payment', 'level'])
                        ->where('type', MembershipTransaction::TYPE_APPLICATION)
                        ->latest('id')
                        ->first();

                    if (! $application || ! $this->hasFullPayment($application)) {
                        throw ValidationException::withMessages([
                            'transaction' => 'A member must have a fully paid application transaction equal to the selected level fee before approval.',
                        ]);
                    }

                    if ($application->status === MembershipTransaction::STATUS_PAID) {
                        app(MembershipTransactionService::class)->approve($application, auth()->id());
                    }
                }

                $user->forceFill([
                    'status' => User::STATUS_ACTIVE,
                    'approved_by_user_id' => auth()->id(),
                    'approved_at' => now(),
                ])->save();

                if ($user->member && $user->member->status === 'pending') {
                    $user->member->update(['status' => 'active']);
                }

                AuditLog::record('account.approved', ['role' => $user->role], $user, auth()->user());
            });
        } catch (ValidationException $exception) {
            return back()->with('error', $exception->validator->errors()->first());
        }

        $user->notify(new PortalNotification('Account approved', 'Your Puihaha account is approved and ready to use.', $user->isAdmin() ? route('members.index') : route('tasks.index')));

        return back()->with('success', 'Account approved.');
    }

    private function hasFullPayment(MembershipTransaction $transaction): bool
    {
        return in_array($transaction->status, [
                MembershipTransaction::STATUS_PAID,
                MembershipTransaction::STATUS_APPROVED,
            ], true)
            && $transaction->payment?->status === 'paid'
            && $transaction->level
            && number_format((float) $transaction->payment->amount, 2, '.', '')
                === number_format((float) $transaction->level->fee, 2, '.', '');
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
