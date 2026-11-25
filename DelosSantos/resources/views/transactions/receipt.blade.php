@php
    $isCancelled = $transaction->status === \App\Models\MembershipTransaction::STATUS_CANCELLED;
    $accountNumber = (string) ($transaction->payment?->payer_account_number ?? '');
    $redactedAccount = $accountNumber === ''
        ? '—'
        : (strlen($accountNumber) <= 5
            ? str_repeat('*', strlen($accountNumber))
            : substr($accountNumber, 0, 3).str_repeat('*', 6).substr($accountNumber, -2));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isCancelled ? 'Transaction Cancelled' : 'Receipt '.$transaction->reference_number }} | Puihaha</title>
    @include('components.brand-head')
    @vite(['resources/css/app.css'])
</head>
<body class="platform-page receipt-page">
    <a class="receipt-back no-print" href="{{ url()->previous() }}">← Go back</a>
    <main class="receipt-card">
        @if($isCancelled)
            <div class="receipt-heading">
                <div>
                    <x-brand-logo variant="full" size="132" class="receipt-logo" />
                    <span class="eyebrow">Puihaha E-learning Platform</span>
                    <h1>Transaction <em>cancelled.</em></h1>
                </div>
                <button class="button button-dark no-print" type="button" onclick="window.print()">Print</button>
            </div>
            <div class="receipt-reference">
                <span>Status</span>
                <strong>Transaction cancelled</strong>
            </div>
            <dl class="receipt-details">
                <div><dt>Transaction reference</dt><dd>{{ $transaction->reference_number }}</dd></div>
                <div><dt>Member name</dt><dd>{{ $transaction->member_display_name }}</dd></div>
                <div><dt>Member email</dt><dd>{{ $transaction->member_email_snapshot ?: '—' }}</dd></div>
                <div><dt>Membership no.</dt><dd>{{ $transaction->membership_number_display }}</dd></div>
                <div><dt>Category</dt><dd>{{ $transaction->category_display_name }}</dd></div>
                <div><dt>Level</dt><dd>{{ $transaction->level_display_name }}</dd></div>
                <div><dt>Transaction date</dt><dd>{{ $transaction->created_at?->format('M d, Y h:i A') ?? '—' }}</dd></div>
                <div><dt>Cancelled date</dt><dd>{{ ($transaction->cancelled_at ?? $transaction->updated_at)?->format('M d, Y h:i A') ?? '—' }}</dd></div>
            </dl>
        @else
            <div class="receipt-heading">
                <div>
                    <x-brand-logo variant="full" size="132" class="receipt-logo" />
                    <span class="eyebrow">Puihaha E-learning Platform</span>
                    <h1>Official <em>receipt.</em></h1>
                </div>
                <button class="button button-dark no-print" type="button" onclick="window.print()">Print</button>
            </div>
            <div class="receipt-reference">
                <span>Transaction reference</span>
                <strong>{{ $transaction->reference_number }}</strong>
            </div>
            <dl class="receipt-details">
                <div><dt>Member</dt><dd>{{ $transaction->member_display_name }}</dd></div>
                <div><dt>Member email</dt><dd>{{ $transaction->member_email_snapshot ?: '—' }}</dd></div>
                <div><dt>Membership no.</dt><dd>{{ $transaction->membership_number_display }}</dd></div>
                <div><dt>Category</dt><dd>{{ $transaction->category_display_name }}</dd></div>
                <div><dt>Level</dt><dd>{{ $transaction->level_display_name }}</dd></div>
                <div><dt>Payment reference</dt><dd>{{ $transaction->payment?->payment_reference ?? '—' }}</dd></div>
                <div><dt>Payment method</dt><dd>{{ ucwords(str_replace('_', ' ', $transaction->payment?->payment_method ?? '—')) }}</dd></div>
                <div><dt>Paid at</dt><dd>{{ $transaction->payment?->paid_at?->format('M d, Y h:i A') ?? '—' }}</dd></div>
                <div><dt>Payer account name</dt><dd>{{ $transaction->payment?->payer_name ?: '—' }}</dd></div>
                <div><dt>Payer account / contact</dt><dd>{{ $redactedAccount }}</dd></div>
                <div><dt>Payer address</dt><dd>{{ $transaction->payment?->payer_address ?: '—' }}</dd></div>
                <div><dt>Amount paid</dt><dd>₱{{ number_format((float) ($transaction->payment?->amount ?? 0), 2) }}</dd></div>
            </dl>
            <p class="receipt-note">Keep this receipt for your membership records. This receipt reflects one full payment for the transaction shown above. The payer account/contact number is intentionally redacted.</p>
        @endif
    </main>
</body>
</html>
