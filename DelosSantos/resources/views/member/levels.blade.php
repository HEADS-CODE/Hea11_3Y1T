@extends('layouts.puihaha')
@section('title', 'Levels | Puihaha')
@section('content')
<div class="page-intro"><div><span class="eyebrow">Membership guide</span><h1>Membership <em>levels.</em></h1><p>Compare the available plans, fees, validity, and benefits.</p></div></div>
<section class="card-grid level-grid member-catalog-grid">@forelse($levels as $level)<article class="panel level-card"><div class="card-topline"><span class="level-ribbon">{{ $level->name }}</span><span class="status-badge status-active">{{ (int) $currentLevelId === (int) $level->id ? 'Current level' : 'Available' }}</span></div><div class="fee">₱{{ number_format($level->fee, 2) }} <small>/ {{ $level->validity_months }} mo.</small></div><p>{{ $level->benefits ?: 'Benefits will be confirmed by your administrator.' }}</p>@if((int) $currentLevelId === (int) $level->id)<span class="field-hint">You are currently enrolled in this level.</span>@else<a class="button button-primary" href="{{ route('member.level-change', ['level' => $level->id]) }}">Subscribe / Apply <span>↗</span></a>@endif</article>@empty<div class="panel empty-state"><h3>No levels are available yet.</h3></div>@endforelse</section>
@endsection
