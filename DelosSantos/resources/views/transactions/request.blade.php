@extends('layouts.puihaha')
@section('title', ($mode === 'renewal' ? 'Renew Membership' : 'Change Membership Level') . ' | Puihaha')
@section('content')
<div class="page-intro compact-intro"><div><span class="eyebrow">Member self-service</span>@if($mode === 'renewal')<h1>Renew your <em>membership.</em></h1>@else<h1>Change your <em>level.</em></h1>@endif<p>{{ $mode === 'renewal' ? 'Continue your current membership for another validity period.' : 'Choose a new level; the administrator will review the paid request before it takes effect.' }}</p></div></div>
<section class="panel form-panel transaction-request-card"><div class="panel-heading"><div><span class="eyebrow">Current membership</span><h2>{{ $member->level->name }} · {{ $member->category->name }}</h2></div><span class="status-badge status-{{ $member->status }}">{{ ucfirst($member->status) }}</span></div>
    <div class="payment-summary"><div><span>Member</span><strong>{{ $member->full_name }}</strong></div><div><span>Current level</span><strong>{{ $member->level->name }}</strong></div><div><span>Current fee</span><strong>₱{{ number_format((float) $member->level->fee, 2) }}</strong></div></div>
    <form method="POST" action="{{ $mode === 'renewal' ? route('member.renew.store') : route('member.level-change.store') }}">
        @csrf
        @if ($mode === 'level_change')
        <div class="form-field level-selection-field"><label for="membership_level_id">New membership level</label><select id="membership_level_id" name="membership_level_id" required data-level-benefits-select><option value="">Choose a level</option>@foreach ($levels as $level)<option value="{{ $level->id }}" data-level-name="{{ $level->name }}" data-level-fee="{{ number_format((float) $level->fee, 2) }}" data-level-validity="{{ $level->validity_months }}" data-level-benefits="{{ $level->benefits ?: 'Benefits will be confirmed by your administrator.' }}" @selected((int) ($selectedLevelId ?? old('membership_level_id')) === (int) $level->id)>{{ $level->name }} · ₱{{ number_format((float) $level->fee, 2) }} · {{ $level->validity_months }} month{{ $level->validity_months === 1 ? '' : 's' }}</option>@endforeach</select>@error('membership_level_id')<span class="field-error">{{ $message }}</span>@enderror</div>
        <div class="selected-level-benefits" data-selected-level-benefits @if(empty($selectedLevel)) hidden @endif><div><span class="eyebrow">Selected level</span><h3 data-selected-level-name>{{ $selectedLevel?->name }}</h3></div><div class="selected-level-meta"><span>Fee <strong data-selected-level-fee>{{ $selectedLevel ? '₱'.number_format((float) $selectedLevel->fee, 2) : '' }}</strong></span><span>Validity <strong data-selected-level-validity>{{ $selectedLevel?->validity_months }} months</strong></span></div><p data-selected-level-benefits-text>{{ $selectedLevel?->benefits ?: ($selectedLevel ? 'Benefits will be confirmed by your administrator.' : '') }}</p></div>
        @else
        <div class="payment-note">Renewal uses your current {{ $member->level->name }} level and its ₱{{ number_format((float) $member->level->fee, 2) }} full fee. If an active membership has remaining days, the renewed period begins after its current end date.</div>
        @endif
        <div class="form-actions"><a class="button button-ghost" href="{{ route('tasks.index') }}">Back to tasks</a><button class="button button-primary" type="submit">Create request <span>↗</span></button></div>
    </form>
</section>
@endsection
