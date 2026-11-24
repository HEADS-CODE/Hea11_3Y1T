@extends('layouts.puihaha')
@section('title', 'Dashboard | Puihaha')
@section('content')
<div class="page-intro"><div><span class="eyebrow">Administrator workspace</span><h1>System <em>dashboard.</em></h1><p>A concise view of members, approvals, and money collected.</p></div><a class="button button-primary" href="{{ route('admin.transactions.index') }}">Review transactions <span>↗</span></a></div>
<div class="metric-grid">
    <article class="metric-card metric-highlight"><span class="metric-label">Total members</span><strong>{{ $stats['total_members'] }}</strong><small>All registered profiles</small></article>
    <article class="metric-card"><span class="metric-label">Active members</span><strong>{{ $stats['active_members'] }}</strong><small class="metric-positive">● Current approved access</small></article>
    <article class="metric-card"><span class="metric-label">Pending transactions</span><strong>{{ $stats['pending_transactions'] }}</strong><small>Awaiting payment or review</small></article>
    <article class="metric-card"><span class="metric-label">Collected payments</span><strong>₱{{ number_format((float) $stats['collected_payments'], 2) }}</strong><small>Recorded paid amounts</small></article>
</div>
@include('components.weather-card', ['latitude' => $weatherLocation['latitude'], 'longitude' => $weatherLocation['longitude'], 'label' => $weatherLocation['label'], 'uses_default' => $weatherLocation['uses_default'], 'editUrl' => route('admin.profile.edit')])
<section class="panel table-panel dashboard-activity-panel"><div class="panel-heading"><div><span class="eyebrow">Administrator activity</span><h2>Recent activity</h2></div><div class="dashboard-subtabs" role="tablist" aria-label="Dashboard activity"><button class="dashboard-subtab is-active" type="button" role="tab" aria-selected="true" aria-controls="dashboard-transactions" data-dashboard-tab="transactions">Latest transactions</button><button class="dashboard-subtab" type="button" role="tab" aria-selected="false" aria-controls="dashboard-audit" data-dashboard-tab="audit">Audit trail</button></div></div>
<div id="dashboard-transactions" data-dashboard-panel="transactions" role="tabpanel">
    @if ($recentTransactions->isNotEmpty())
    <div class="table-scroll"><table class="members-table"><thead><tr><th>Reference</th><th>Member</th><th>Type</th><th>Amount</th><th>Status</th><th>Created</th></tr></thead><tbody>
        @foreach ($recentTransactions as $transaction)
        <tr><td><span class="membership-number">{{ $transaction->reference_number }}</span></td><td><span class="member-name">{{ $transaction->member_display_name }}</span><span class="member-email">{{ $transaction->member_email_snapshot ?: 'Historical email unavailable' }}</span></td><td>{{ ucwords(str_replace('_', ' ', $transaction->type)) }}</td><td>₱{{ number_format((float) $transaction->amount, 2) }}</td><td><span class="status-badge status-{{ $transaction->effectiveStatus() }}">{{ ucfirst($transaction->effectiveStatus()) }}</span></td><td>{{ $transaction->created_at->format('M d, Y') }}</td></tr>
        @endforeach
    </tbody></table></div>
    @else
    <div class="empty-state"><span class="empty-icon">✦</span><h3>No transactions yet</h3><p>New membership applications will appear here.</p></div>
    @endif
</div>
<div id="dashboard-audit" data-dashboard-panel="audit" role="tabpanel" hidden>@if($recentActivity->isNotEmpty())<div class="activity-list">@foreach($recentActivity as $log)<div class="activity-row"><span class="activity-dot" aria-hidden="true"></span><div><strong>{{ $log->action }}</strong><small>{{ $log->user?->name ?? 'System' }} · {{ $log->created_at?->diffForHumans() }}</small></div></div>@endforeach</div><div class="dashboard-panel-footer"><a class="button button-ghost" href="{{ route('admin.activity.index') }}">Open activity log</a></div>@else<div class="empty-state"><span class="empty-icon">✦</span><h3>No activity yet</h3><p>Recorded system actions will appear here.</p></div>@endif</div></section>
@endsection
