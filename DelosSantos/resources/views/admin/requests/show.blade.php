@extends('layouts.puihaha')
@section('title', 'Review Request | Puihaha')
@section('content')
<div class="page-intro compact-intro"><div><span class="eyebrow">Administrator review</span><h1>Review <em>request.</em></h1><p>{{ $memberRequest->member->full_name }} · {{ config('membership.request_types.'.$memberRequest->request_type, ucwords(str_replace('_', ' ', $memberRequest->request_type))) }}</p></div><a class="button button-ghost" href="{{ route('admin.requests.index') }}">All requests</a></div>
<section class="panel form-panel"><div class="panel-heading"><div><span class="eyebrow">{{ ucfirst($memberRequest->status) }}</span><h2>Request details</h2></div><span class="status-pill status-{{ $memberRequest->status }}">{{ ucfirst($memberRequest->status) }}</span></div>
@php
    $previousValues = $memberRequest->previous_values ?? [];
    $proposedValues = $memberRequest->proposed_values ?? [];
    $comparisonKeys = array_values(array_unique(array_merge(array_keys($previousValues), array_keys($proposedValues))));
    $prettyField = fn (string $key): string => ucwords(str_replace(['_', '-'], ' ', $key));
    $prettyValue = function (mixed $value): string {
        if ($value === null || $value === '') return '—';
        if (is_bool($value)) return $value ? 'Yes' : 'No';
        if (is_array($value)) return implode(', ', array_map(fn ($item) => is_scalar($item) ? (string) $item : json_encode($item), $value));
        return (string) $value;
    };
@endphp
<section class="request-comparison" aria-label="Request value comparison">
    <div class="request-comparison-heading"><span>Field</span><span>Previous</span><span>Proposed</span></div>
    @foreach ($comparisonKeys as $key)
        @php $previous = $prettyValue($previousValues[$key] ?? null); $proposed = $prettyValue($proposedValues[$key] ?? null); $changed = $previous !== $proposed; @endphp
        <div class="request-comparison-row {{ $changed ? 'is-changed' : '' }}"><strong>{{ $prettyField($key) }}</strong><span>{{ $previous }}</span><span>{{ $proposed }} @if($changed)<em>Changed</em>@endif</span></div>
    @endforeach
</section>
@if($memberRequest->documents->isNotEmpty())<div class="request-documents"><h3>Private supporting documents</h3><p class="field-hint">Documents are served through this administrator-only route and are never exposed as public files.</p><ul>@foreach($memberRequest->documents as $document)<li><span>{{ $document->original_name }} · {{ number_format($document->size_bytes / 1024, 1) }} KB</span><a class="row-action-link" href="{{ route('admin.requests.documents.download', [$memberRequest, $document->id]) }}">View / download</a></li>@endforeach</ul></div>@endif
@if($memberRequest->isPending())<div class="request-review-actions"><form method="POST" action="{{ route('admin.requests.approve', $memberRequest) }}">@csrf<button class="button button-primary" type="submit">Approve request</button></form><form method="POST" action="{{ route('admin.requests.reject', $memberRequest) }}" class="reject-request-form">@csrf<input name="reviewer_notes" required placeholder="Reason or next step"><button class="button button-danger" type="submit">Reject request</button></form></div>@else<div class="payment-note">Reviewed {{ $memberRequest->reviewed_at?->format('M d, Y h:i A') }}@if($memberRequest->reviewer_notes) · {{ $memberRequest->reviewer_notes }}@endif</div>@endif
</section>
@endsection
