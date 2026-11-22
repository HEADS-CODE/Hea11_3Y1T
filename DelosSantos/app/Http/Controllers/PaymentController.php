<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\MembershipTransaction;
use App\Models\User;
use App\Services\MembershipTransactionService;
use App\Notifications\PortalNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function memberShow(MembershipTransaction $transaction): View
    {
        abort_unless(auth()->user()->member_id === $transaction->member_id, 403);
        $transaction->load(['level', 'category', 'member', 'payment']);

        return view('transactions.pay-v2', compact('transaction'));
    }

    public function memberPay(Request $request, MembershipTransaction $transaction, MembershipTransactionService $service): RedirectResponse
    {
        abort_unless(auth()->user()->member_id === $transaction->member_id, 403);
        $validated = $request->validate([
            'payment_channel' => ['required', Rule::in(array_merge(MembershipTransaction::ONLINE_CHANNELS, [MembershipTransaction::CHANNEL_FRONT_DESK]))],
        ]);

        if ($validated['payment_channel'] === MembershipTransaction::CHANNEL_FRONT_DESK) {
            $service->markFrontDeskRequested($transaction);
            User::where('role', User::ROLE_ADMIN)->where('status', User::STATUS_ACTIVE)->get()->each(fn (User $admin) => $admin->notify(new PortalNotification('Front-desk payment requested', $transaction->member->full_name.' is bringing payment for '.$transaction->reference_number.'.', route('admin.transactions.payment', $transaction))));
            return redirect()->route('member.payment', $transaction)->with('success', 'Your front-desk payment request was sent. Bring the listed requirements during opening hours.');
        }

        $payment = $service->recordFullPayment($transaction, $validated['payment_channel'], auth()->id());
        AuditLog::record('payment.recorded', ['reference' => $payment->payment_reference, 'method' => $payment->payment_method], $payment, auth()->user());
        User::where('role', User::ROLE_ADMIN)->where('status', User::STATUS_ACTIVE)->get()->each(fn (User $admin) => $admin->notify(new PortalNotification('Online payment received', $transaction->member->full_name.' submitted payment for '.$transaction->reference_number.'.', route('admin.transactions.index'))));

        return redirect()->route('member.transactions.receipt', $transaction)->with('success', 'Payment confirmed. Please wait for an administrator to approve your request.');
    }

    public function adminShow(MembershipTransaction $transaction): View
    {
        $transaction->load(['member', 'level']);

        abort_unless($transaction->payment_channel === MembershipTransaction::CHANNEL_FRONT_DESK || $transaction->payment, 422);
        return view('transactions.pay-v2', compact('transaction'));
    }

    public function adminRecord(MembershipTransaction $transaction, MembershipTransactionService $service): RedirectResponse
    {
        abort_unless($transaction->payment_channel === MembershipTransaction::CHANNEL_FRONT_DESK, 422, 'Only front-desk transactions can receive cash payment here.');
        $payment = $service->recordFullPayment($transaction, MembershipTransaction::CHANNEL_FRONT_DESK, auth()->id());
        AuditLog::record('payment.recorded', ['reference' => $payment->payment_reference, 'method' => $payment->payment_method], $payment, auth()->user());
        $transaction->member->user?->notify(new PortalNotification('Payment recorded', 'Your front-desk payment for '.$transaction->reference_number.' was recorded. It is awaiting approval.', route('member.transactions')));

        return back()->with('success', 'Full payment recorded.');
    }
}
