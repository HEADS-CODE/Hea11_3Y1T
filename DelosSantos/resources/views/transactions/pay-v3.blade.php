@extends('layouts.puihaha')

@section('title', 'Complete Payment | Puihaha')

@section('content')
<div class="page-intro compact-intro">
    <div><span class="eyebrow">{{ auth()->user()->isAdmin() ? 'Front-desk payment' : 'Two-step checkout' }}</span><h1>Complete your <em>payment.</em></h1><p>Choose a payment method, then confirm who is paying and where the payer is located. This information is saved with this transaction only.</p></div>
</div>
<section class="panel form-panel transaction-payment-card">
    <div class="panel-heading"><div><span class="eyebrow">{{ $transaction->reference_number }}</span><h2>{{ $transaction->level->name }} membership</h2></div><span class="status-badge status-{{ $transaction->effectiveStatus() }}">{{ ucfirst($transaction->effectiveStatus()) }}</span></div>
    <div class="payment-summary"><div><span>Member</span><strong>{{ $transaction->member->full_name }}</strong></div><div><span>Category</span><strong>{{ $transaction->category->name }}</strong></div><div><span>Amount due</span><strong>₱{{ number_format((float) $transaction->level->fee, 2) }}</strong></div></div>

    @if($transaction->payment && $transaction->payment->status === 'paid')
        <div class="platform-alert alert-success">Payment confirmed. Please wait for an administrator to approve your request. <a href="{{ auth()->user()->isAdmin() ? route('admin.transactions.receipt', $transaction) : route('member.transactions.receipt', $transaction) }}">Print receipt</a></div>
    @elseif($transaction->payment_channel === \App\Models\MembershipTransaction::CHANNEL_FRONT_DESK && auth()->user()->isAdmin())
        <div class="payment-note"><strong>Front-desk payer details</strong><br>{{ $transaction->payment?->payer_name ?: 'No payer name was supplied.' }} · {{ $transaction->payment?->payer_account_number ? 'Account/contact number supplied' : 'No account/contact number supplied.' }}<br>{{ $transaction->payment?->payer_address ?: 'No payer address was supplied.' }}</div>
        <div class="payment-note">The member selected front-desk payment. Record only the exact cash amount of ₱{{ number_format((float) $transaction->level->fee, 2) }} after receiving it.</div>
        <form method="POST" action="{{ route('admin.transactions.payment.store', $transaction) }}">@csrf<div class="form-actions"><a class="button button-ghost" href="{{ route('admin.transactions.index') }}">Cancel</a><button class="button button-primary" type="submit">Record cash payment <span>↗</span></button></div></form>
    @elseif($transaction->payment_channel === \App\Models\MembershipTransaction::CHANNEL_FRONT_DESK)
        <div class="payment-note"><strong>Awaiting front-desk payment.</strong><br>Your payer details and reference are saved for this transaction. Visit during {{ config('membership.front_desk.opening_hours') }}. {{ config('membership.front_desk.what_to_bring') }}</div>
        <div class="form-actions"><a class="button button-primary" href="{{ route('member.transactions') }}">View transaction history</a></div>
    @else
        <form method="POST" action="{{ route('member.payment.store', $transaction) }}" data-payment-form data-payment-checkout>
            @csrf
            <section data-payment-step="method" @if($errors->hasAny(['payer_name', 'payer_account_number', 'payer_address', 'payer_latitude', 'payer_longitude'])) hidden @endif>
                <div class="checkout-step-heading"><span class="checkout-step-number">1</span><div><strong>Choose a payment method</strong><small>Select how this transaction will be paid.</small></div></div>
                <div class="payment-choice-grid"><label class="payment-choice"><input type="radio" name="payment_channel" value="card" required><span><strong>Card</strong><small>Simulated online checkout</small></span></label><label class="payment-choice"><input type="radio" name="payment_channel" value="bank_transfer"><span><strong>Bank transfer</strong><small>Simulated online checkout</small></span></label><label class="payment-choice"><input type="radio" name="payment_channel" value="gcash"><span><strong>GCash</strong><small>Simulated e-wallet checkout</small></span></label><label class="payment-choice"><input type="radio" name="payment_channel" value="maya"><span><strong>Maya</strong><small>Simulated e-wallet checkout</small></span></label><label class="payment-choice payment-choice-front"><input type="radio" name="payment_channel" value="front_desk"><span><strong>Cash at front desk</strong><small>Pay in person during opening hours</small></span></label></div>
                @error('payment_channel')<span class="field-error">{{ $message }}</span>@enderror
                <div class="payment-note" data-online-note>Demo checkout only. Do not enter card numbers, CVV, account passwords, or other sensitive payment credentials.</div>
                <div class="payment-note" data-frontdesk-note hidden><strong>Before you go:</strong> {{ config('membership.front_desk.opening_hours') }}. {{ config('membership.front_desk.what_to_bring') }}</div>
                <button class="button button-primary checkout-continue" type="button" data-payment-continue>Continue to payer details <span>→</span></button>
            </section>

            <section data-payment-step="payer" data-address-map @unless($errors->hasAny(['payer_name', 'payer_account_number', 'payer_address', 'payer_latitude', 'payer_longitude'])) hidden @endunless>
                <div class="checkout-step-heading"><span class="checkout-step-number">2</span><div><strong>Payer details</strong><small>These details describe the person or account paying, not the member profile.</small></div></div>
                <div class="payment-payer-grid">
                    <div class="field-group"><label for="payer_name">Account name <span>*</span></label><input id="payer_name" name="payer_name" value="{{ old('payer_name', $transaction->member->full_name) }}" required>@error('payer_name')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div class="field-group"><label for="payer_account_number">Account / contact number <span>*</span></label><input id="payer_account_number" name="payer_account_number" value="{{ old('payer_account_number', $transaction->member->phone) }}" required>@error('payer_account_number')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div class="field-group payment-payer-span-2"><label for="payer_address">Payer address <span>*</span></label><textarea id="payer_address" name="payer_address" rows="3" data-address-map-address data-address-part data-full-address required>{{ old('payer_address', $transaction->member->full_address) }}</textarea>@error('payer_address')<span class="field-error">{{ $message }}</span>@enderror</div>
                </div>
                <div class="payer-map"><input type="hidden" id="latitude" name="payer_latitude" value="{{ old('payer_latitude') }}"><input type="hidden" id="longitude" name="payer_longitude" value="{{ old('payer_longitude') }}"><div class="map-field-heading"><label>Location pin <span class="optional">Optional</span></label><button class="button button-ghost button-small" type="button" data-locate-map>Locate on map</button></div><div class="map-picker-canvas" data-map-canvas><span>Type the payer address, or place a pin when Maps is available.</span></div><p class="map-privacy">A pin is optional. If Maps is unavailable, the typed payer address remains usable.</p><p class="map-fallback" data-map-fallback hidden>Map pin is unavailable right now. You can continue using the typed address.</p></div>
                @error('payer_latitude')<span class="field-error">{{ $message }}</span>@enderror @error('payer_longitude')<span class="field-error">{{ $message }}</span>@enderror
                <div class="payment-note">The system records one full payment equal to the membership level fee. Payer details are kept as an immutable transaction snapshot.</div>
                <div class="form-actions"><button class="button button-ghost" type="button" data-payment-back>← Back</button><button class="button button-primary" type="submit">Confirm payment choice <span>↗</span></button></div>
            </section>
        </form>
    @endif
</section>
@endsection

@push('page_scripts')
    @include('components.map-scripts')
@endpush
