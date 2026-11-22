@extends('layouts.puihaha')
@section('title', 'Record Payment | Puihaha')
@section('content')
<div class="page-intro compact-intro"><div><span class="eyebrow">{{ auth()->user()->isAdmin() ? 'Front-desk payment' : 'Membership application' }}</span><h1>Complete your <em>payment.</em></h1><p>One full payment is recorded for the selected membership level. Partial payments are not accepted.</p></div></div>
<section class="panel form-panel transaction-payment-card"><div class="panel-heading"><div><span class="eyebrow">{{ $transaction->reference_number }}</span><h2>{{ $transaction->level->name }} membership</h2></div><span class="status-badge status-{{ $transaction->effectiveStatus() }}">{{ ucfirst($transaction->effectiveStatus()) }}</span></div>
    <div class="payment-summary"><div><span>Member</span><strong>{{ $transaction->member->full_name }}</strong></div><div><span>Category</span><strong>{{ $transaction->category->name }}</strong></div><div><span>Amount due</span><strong>₱{{ number_format((float) $transaction->level->fee, 2) }}</strong></div></div>
    @if ($transaction->payment)
        <div class="platform-alert alert-success">Payment already recorded as {{ $transaction->payment->payment_reference }}.</div>
    @else
        <form method="POST" action="{{ auth()->user()->isAdmin() ? route('admin.transactions.payment.store', $transaction) : route('member.payment.store') }}">
            @csrf
            @if (auth()->user()->isAdmin())<div class="form-field"><label for="payment_method">Payment method</label><select id="payment_method" name="payment_method"><option value="cash_front_desk">Cash · front desk</option><option value="bank_transfer">Bank transfer</option><option value="card">Card</option></select></div>@else<div class="payment-note">This demonstration records a simulated online payment. In a production deployment, connect this action to a payment gateway before accepting real funds.</div>@endif
            <div class="form-actions"><a class="button button-ghost" href="{{ auth()->user()->isAdmin() ? route('admin.transactions.index') : route('approval.pending') }}">Cancel</a><button class="button button-primary" type="submit">Record full payment <span>↗</span></button></div>
        </form>
    @endif
</section>
@endsection
