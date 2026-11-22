@extends('layouts.puihaha')
@section('title', 'Reports | Puihaha')
@section('content')
<div class="page-intro report-intro">
    <div><span class="eyebrow">Administrator workspace</span><h1>System <em>reports.</em></h1><p>Turn the membership record into a clear, printable view for review and follow-up.</p></div>
    <a class="button button-primary" href="{{ route('admin.reports.export', request()->query()) }}">Export CSV <span>↗</span></a>
</div>

<section class="panel report-panel">
    <form class="report-filters" method="GET" action="{{ route('admin.reports.index') }}">
        <div class="report-filter report-filter-wide"><label for="report-type">Report</label><select id="report-type" name="type"><option value="members" @selected($filters['type'] === 'members')>Members</option><option value="transactions" @selected($filters['type'] === 'transactions')>Transactions</option><option value="payments" @selected($filters['type'] === 'payments')>Payments</option><option value="expiring" @selected($filters['type'] === 'expiring')>Expiring memberships</option></select></div>
        <div class="report-filter report-filter-wide"><label for="report-search">Search</label><input id="report-search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name, email, reference, or membership no."></div>
        <div class="report-filter"><label for="report-status">Status</label><input id="report-status" name="status" value="{{ $filters['status'] ?? '' }}" placeholder="Any status"></div>
        <div class="report-filter"><label for="report-category">Category</label><select id="report-category" name="category_id"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(($filters['category_id'] ?? '') == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
        <div class="report-filter"><label for="report-level">Level</label><select id="report-level" name="level_id"><option value="">All levels</option>@foreach($levels as $level)<option value="{{ $level->id }}" @selected(($filters['level_id'] ?? '') == $level->id)>{{ $level->name }}</option>@endforeach</select></div>
        <div class="report-filter"><label for="report-from">From</label><input id="report-from" type="date" name="from" value="{{ $filters['from'] ?? '' }}"></div>
        <div class="report-filter"><label for="report-to">To</label><input id="report-to" type="date" name="to" value="{{ $filters['to'] ?? '' }}"></div>
        <div class="report-filter-actions"><button class="button button-dark" type="submit">Apply filters</button><a class="button button-ghost" href="{{ route('admin.reports.index') }}">Clear</a></div>
    </form>
</section>

<section class="panel table-panel report-results">
    <div class="panel-heading"><div><span class="eyebrow">{{ $type === 'expiring' ? 'Renewal watch' : 'Generated view' }}</span><h2>{{ $title }}</h2><p class="panel-intro">{{ $description }}</p></div><span class="report-count">{{ count($rows) }} result{{ count($rows) === 1 ? '' : 's' }}</span></div>
    @if ($rows)
        <div class="table-scroll"><table class="members-table report-table"><thead><tr>@foreach($columns as $label)<th>{{ $label }}</th>@endforeach</tr></thead><tbody>@foreach($rows as $row)<tr>@foreach($columns as $key => $label)<td>{{ $row[$key] ?? '—' }}</td>@endforeach</tr>@endforeach</tbody></table></div>
    @else
        <div class="empty-state"><span class="empty-icon">✦</span><h3>No matching records</h3><p>Try a wider date range or clear one of the filters.</p></div>
    @endif
</section>
@endsection
