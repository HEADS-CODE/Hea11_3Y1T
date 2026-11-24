<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Member;
use App\Models\MembershipTransaction;
use App\Services\MembershipTransactionService;
use App\Notifications\PortalNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $transactions = MembershipTransaction::with(['member', 'level', 'payment'])->latest()->paginate(12)->withQueryString();

        return view('transactions.index-v2', compact('transactions'));
    }

    public function historyForMember(?Member $member = null): View
    {
        $member ??= auth()->user()->member;
        abort_unless($member, 404);
        abort_unless(auth()->user()->isAdmin() || auth()->user()->member_id === $member->id, 403);
        $transactions = $member->transactions()->with(['category', 'level', 'payment'])->latest()->get();
        $expiring = $transactions->first(fn ($transaction) => $transaction->effectiveStatus() === MembershipTransaction::STATUS_APPROVED
            && $transaction->ends_at?->between(today(), today()->addDays(14)));
        if ($expiring && auth()->user()->notifications()->whereDate('created_at', today())->where('data->title', 'Renewal reminder')->doesntExist()) {
            auth()->user()->notify(new PortalNotification('Renewal reminder', 'Your membership ends on '.$expiring->ends_at->format('M d, Y').'. Renew within the next 14 days to keep your learning journey continuous.', route('member.renew')));
        }

        return view('transactions.history', compact('member', 'transactions'));
    }

    public function showRenewal(): View
    {
        $member = auth()->user()->member;
        abort_unless($member, 404);
        $member->load(['category', 'level']);

        return view('transactions.request', ['member' => $member, 'mode' => 'renewal', 'levels' => collect()]);
    }

    public function renew(MembershipTransactionService $service): RedirectResponse
    {
        $member = auth()->user()->member;
        abort_unless($member, 404);

        if ($this->hasOpenRequest($member)) {
            return back()->with('error', 'You already have a transaction awaiting payment or administrator review.');
        }

        $member->load(['category', 'level']);
        $service->create([
            'member' => $member,
            'category' => $member->category,
            'level' => $member->level,
            'type' => MembershipTransaction::TYPE_RENEWAL,
            'created_by_user_id' => auth()->id(),
        ]);

        $transaction = $member->transactions()->latest()->first();

        return redirect()->route('member.payment', $transaction);
    }

    public function showLevelChange(Request $request): View
    {
        $member = auth()->user()->member;
        abort_unless($member, 404);
        $member->load(['category', 'level']);
        $levels = \App\Models\MembershipLevel::where('is_active', true)->orderBy('fee')->get();
        $selectedLevelId = $request->integer('level') ?: (int) old('membership_level_id');
        $selectedLevel = $levels->firstWhere('id', $selectedLevelId);

        return view('transactions.request', compact('member', 'levels', 'selectedLevelId', 'selectedLevel') + ['mode' => 'level_change']);
    }

    public function levelChange(Request $request, MembershipTransactionService $service): RedirectResponse
    {
        $member = auth()->user()->member;
        abort_unless($member, 404);

        $validated = $request->validate([
            'membership_level_id' => ['required', Rule::exists('membership_levels', 'id')->where(fn ($query) => $query->where('is_active', true))],
        ]);

        if ((int) $validated['membership_level_id'] === (int) $member->membership_level_id) {
            return back()->withErrors(['membership_level_id' => 'Please choose a different level for a level-change request. Make sure it is an existing level other than your current level.']);
        }

        if ($this->hasOpenRequest($member)) {
            return back()->with('error', 'You already have a transaction awaiting payment or administrator review.');
        }

        $member->load('category');
        $level = \App\Models\MembershipLevel::findOrFail($validated['membership_level_id']);
        $service->create([
            'member' => $member,
            'category' => $member->category,
            'level' => $level,
            'type' => MembershipTransaction::TYPE_LEVEL_CHANGE,
            'created_by_user_id' => auth()->id(),
        ]);

        $transaction = $member->transactions()->latest()->first();

        return redirect()->route('member.payment', $transaction);
    }

    public function approve(MembershipTransaction $transaction): RedirectResponse
    {
        abort_unless($transaction->member, 404);

        try {
            DB::transaction(function () use ($transaction): void {
                app(MembershipTransactionService::class)->approve($transaction, auth()->id());

                if ($transaction->type === MembershipTransaction::TYPE_LEVEL_CHANGE) {
                    $transaction->member->update([
                        'membership_category_id' => $transaction->membership_category_id,
                        'membership_level_id' => $transaction->membership_level_id,
                        'status' => 'active',
                    ]);
                }

                AuditLog::record('transaction.approved', ['reference' => $transaction->reference_number], $transaction, auth()->user());
                $transaction->member->user?->notify(new PortalNotification(
                    $transaction->type === MembershipTransaction::TYPE_LEVEL_CHANGE ? 'Level change approved' : 'Transaction approved',
                    $transaction->type === MembershipTransaction::TYPE_LEVEL_CHANGE ? 'Your new membership level is now active.' : 'Your membership transaction was approved.',
                    route('member.transactions'),
                ));
            });
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('success', 'Paid transaction approved.');
    }

    public function cancel(Request $request, MembershipTransaction $transaction): RedirectResponse
    {
        abort_unless($transaction->member, 404);

        if (in_array($transaction->effectiveStatus(), [MembershipTransaction::STATUS_APPROVED, 'expired'], true)) {
            return back()->with('error', 'Approved transactions cannot be cancelled from this screen.');
        }

        $validated = $request->validate(['cancel_reason' => ['required', 'string', 'max:500']]);
        $transaction->update([
            'status' => MembershipTransaction::STATUS_CANCELLED,
            'cancel_reason' => $validated['cancel_reason'],
            'notes' => $transaction->payment ? trim(implode(' ', array_filter([$transaction->notes, 'Needs refund review.']))) : $transaction->notes,
        ]);
        AuditLog::record('transaction.cancelled', ['reference' => $transaction->reference_number], $transaction, auth()->user());
        $transaction->member->user?->notify(new PortalNotification('Transaction cancelled', 'Your transaction '.$transaction->reference_number.' was cancelled.'.$transaction->cancel_reason, route('member.transactions')));

        return back()->with('success', 'Transaction cancelled.');
    }

    public function receipt(MembershipTransaction $transaction): View
    {
        abort_unless(auth()->user()->isAdmin() || auth()->user()->member_id === $transaction->member_id, 403);
        $transaction->load(['member', 'category', 'level', 'payment']);
        abort_unless($transaction->payment && $transaction->payment->status === 'paid', 404);

        return view('transactions.receipt', compact('transaction'));
    }

    private function hasOpenRequest(Member $member): bool
    {
        return $member->transactions()->whereIn('status', [
            MembershipTransaction::STATUS_PENDING,
            MembershipTransaction::STATUS_PAID,
        ])->exists();
    }
}
