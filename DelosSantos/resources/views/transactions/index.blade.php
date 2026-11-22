@extends('layouts.puihaha')
@section('title', 'Transactions | Puihaha')
@section('content')
<div class="page-intro"><div><span class="eyebrow">Administrator workspace</span><h1>Membership <em>transactions.</em></h1><p>Track applications, renewals, level changes, payments, and approvals in one place.</p></div></div>
<section class="panel table-panel"><div class="panel-heading"><div><span class="eyebrow">Transaction register</span><h2>All transactions</h2></div><span class="result-count">{{ $transactions->total() }} records</span></div>
    @if ($transactions->count())
    <div class="table-scroll"><table class="members-table"><thead><tr><th>Reference</th><th>Member</th><th>Type</th><th>Level fee</th><th>Payment</th><th>Status</th><th>Actions</th></tr></thead><tbody>
    @foreach ($transactions as $transaction)
        <tr><td><span class="membership-number">{{ $transaction->reference_number }}</span><span class="member-email">{{ $transaction->created_at->format('M d, Y') }}</span></td><td><a class="member-name" href="{{ route('admin.members.transactions', $transaction->member) }}">{{ $transaction->member->full_name }}</a><span class="member-email">{{ $transaction->member->membership_number }}</span></td><td>{{ ucwords(str_replace('_', ' ', $transaction->type)) }}</td><td>₱{{ number_format((float) $transaction->amount, 2) }}<span class="member-email">{{ $transaction->level->name }}</span></td><td>@if($transaction->payment)<span class="status-badge status-paid">Paid</span><span class="member-email">{{ $transaction->payment->payment_reference }}</span>@else<span class="member-email">Not recorded</span>@endif</td><td><span class="status-badge status-{{ $transaction->effectiveStatus() }}">{{ ucfirst($transaction->effectiveStatus()) }}</span></td><td><div class="row-actions">
            @if ($transaction->status === \App\Models\MembershipTransaction::STATUS_PAID)<form method="POST" action="{{ route('admin.transactions.approve', $transaction) }}">@csrf<button type="submit">Approve</button></form>@endif
            @if (!$transaction->payment && $transaction->status !== \App\Models\MembershipTransaction::STATUS_CANCELLED)<a href="{{ route('admin.transactions.payment', $transaction) }}">Payment</a>@endif
            @if (in_array($transaction->status, [\App\Models\MembershipTransaction::STATUS_PENDING, \App\Models\MembershipTransaction::STATUS_PAID], true))<form method="POST" action="{{ route('admin.transactions.cancel', $transaction) }}" data-confirm="Cancel this transaction?">@csrf<button class="text-danger" type="submit">Cancel</button></form>@endif
            @if ($transaction->payment)<a href="{{ route('admin.transactions.receipt', $transaction) }}">Receipt</a>@endif
        </div></td></tr>
    @endforeach
    </tbody></table></div><div class="pagination-wrap">{{ $transactions->links() }}</div>
    @else
    <div class="empty-state"><span class="empty-icon">✦</span><h3>No transactions found</h3><p>Applications, renewals, and level changes will be listed here.</p></div>
    @endif
</section>
@endsection
