@extends('layouts.puihaha')
@section('title', 'Activity Log | Puihaha')
@section('content')
@php
    $hiddenDetailKeys = ['member_id', 'task_id', 'request_id', 'user_id', 'reviewed_by_user_id'];
    $detailLabels = [
        'reference' => 'Reference',
        'transaction_reference' => 'Transaction reference',
        'role' => 'Role',
        'application_rejected' => 'Application rejected',
        'membership_number' => 'Membership number',
        'transaction_count_preserved' => 'Transactions preserved',
        'document_count_removed' => 'Supporting documents removed',
        'reason' => 'Reason',
        'method' => 'Payment method',
        'payment_method' => 'Payment method',
        'deadline' => 'Deadline',
        'requested_deadline' => 'Requested deadline',
        'new_deadline' => 'New deadline',
        'request_type' => 'Request type',
        'invitation_sent' => 'Invitation sent',
        'account_linked' => 'Account linked',
        'email_changed' => 'Email changed',
    ];
    $dateKeys = ['deadline', 'requested_deadline', 'new_deadline'];
    $formatDetailValue = static function (string $key, mixed $value) use ($dateKeys): string {
        if ($value === null || $value === '') {
            return '—';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (! is_scalar($value)) {
            return json_encode($value, JSON_UNESCAPED_SLASHES) ?: '—';
        }

        $text = (string) $value;

        if (in_array($key, $dateKeys, true)) {
            try {
                return \Illuminate\Support\Carbon::parse($text)->format('M d, Y h:i A');
            } catch (\Throwable) {
                // Keep the original value when a legacy audit entry is not a date.
            }
        }

        if (in_array($key, ['role', 'method', 'payment_method', 'request_type', 'reason'], true) || str_ends_with($key, '_status')) {
            return ucwords(str_replace(['_', '-'], ' ', $text));
        }

        return $text;
    };
@endphp
<div class="page-intro"><div><span class="eyebrow">Administrator workspace</span><h1>Activity <em>log.</em></h1><p>Append-only audit entries for account, membership, payment, and request actions.</p></div><button class="button button-ghost no-print" type="button" onclick="window.print()">Print log</button></div>
<form class="directory-toolbar admin-activity-filters no-print" method="GET" action="{{ route('admin.activity.index') }}"><div class="search-wrap"><span>⌕</span><input name="actor" value="{{ request('actor') }}" placeholder="Actor name or email"></div><select name="role"><option value="">All roles</option><option value="admin" @selected(request('role') === 'admin')>Admin</option><option value="member" @selected(request('role') === 'member')>Member</option></select><input name="action" value="{{ request('action') }}" placeholder="Action"><input type="date" name="date_from" value="{{ request('date_from') }}"><input type="date" name="date_to" value="{{ request('date_to') }}"><button class="button button-primary button-small" type="submit">Filter</button></form>
<div class="print-report-header"><h2>Puihaha activity log</h2><p>Generated {{ now()->format('M d, Y h:i A') }}</p><p>Filters: {{ collect(['Actor' => request('actor'), 'Role' => request('role'), 'Action' => request('action'), 'From' => request('date_from'), 'To' => request('date_to')])->filter()->map(fn ($value, $key) => $key.': '.$value)->implode(' · ') ?: 'None' }}</p></div>
<section class="panel table-panel admin-activity-panel"><div class="panel-heading"><div><span class="eyebrow">Immutable record</span><h2>Audit entries</h2></div><span class="result-count">{{ $logs->total() }} records</span></div>@if($logs->count())<div class="table-scroll"><table class="members-table"><colgroup><col class="activity-when-column"><col class="activity-actor-column"><col class="activity-role-column"><col class="activity-action-column"><col class="activity-details-column"></colgroup><thead><tr><th>When</th><th>Actor</th><th>Role</th><th>Action</th><th>Details</th></tr></thead><tbody>@foreach($logs as $log)@php $details = is_array($log->details) ? $log->details : []; $displayDetails = collect($details)->except($hiddenDetailKeys)->reject(fn ($value, $key) => $key === 'requested_deadline' && $value === null); $actionLabel = collect(explode('.', (string) $log->action))->map(fn ($part) => ucfirst(str_replace(['_', '-'], ' ', $part)))->implode(' '); @endphp<tr><td>{{ $log->created_at?->format('M d, Y h:i A') }}</td><td>{{ $log->user?->name ?? 'System' }}<span class="member-email">{{ $log->user?->email ?: 'Account no longer available' }}</span></td><td>{{ ucfirst($log->user?->role ?? 'system') }}</td><td><span class="membership-number">{{ $actionLabel }}</span></td><td>@if($displayDetails->isNotEmpty())<dl class="audit-details">@foreach($displayDetails as $key => $value)<div class="audit-detail-row"><dt>{{ $detailLabels[$key] ?? ucwords(str_replace(['_', '-'], ' ', $key)) }}</dt><dd>{{ $formatDetailValue($key, $value) }}</dd></div>@endforeach</dl>@else<span class="member-email">No additional details</span>@endif</td></tr>@endforeach</tbody></table></div><div class="pagination-wrap no-print">{{ $logs->links('pagination.puihaha') }}</div>@else<div class="empty-state"><span class="empty-icon">✦</span><h3>No activity matches</h3><p>Try clearing a filter or wait for the next recorded action.</p></div>@endif</section>
@endsection
