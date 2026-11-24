@extends('layouts.puihaha')
@section('title', 'Member Documents | Puihaha')
@section('content')
<div class="page-intro compact-intro"><div><span class="eyebrow">Member records · {{ $member->membership_number }}</span><h1>Member <em>documents.</em></h1><p>Review private supporting documents for {{ $member->full_name }}. Files remain accessible only to active administrators.</p></div><a class="button button-ghost" href="{{ route('members.show', $member) }}">← Go back</a></div>
<section class="panel documents-panel"><div class="panel-heading"><div><span class="eyebrow">Private records</span><h2>Supporting documents</h2></div><span class="result-count">{{ $member->documents->count() }} records</span></div>
@if($member->documents->isNotEmpty())
<div class="document-grid">@foreach($member->documents as $document)<article class="document-card"><div class="document-card-top"><span class="document-type">{{ ucwords(str_replace('_', ' ', $document->document_type)) }}</span><span class="status-badge status-{{ $document->request?->status ?? 'inactive' }}">{{ ucfirst($document->request?->status ?? 'Recorded') }}</span></div><h3>{{ $document->original_name }}</h3><p>{{ number_format($document->size_bytes / 1024, 1) }} KB · {{ strtoupper($document->extension) }} · Uploaded {{ $document->created_at?->format('M d, Y') }}</p><a class="button button-primary document-view-button" href="{{ route('members.documents.show', [$member, $document]) }}">View document</a></article>@endforeach</div>
@else
<div class="empty-state"><span class="empty-icon">✦</span><h3>No documents recorded</h3><p>This member has not submitted a supporting document through an approved request.</p><small class="document-policy-reminder">Administrator-only records. Files are private and are never shown to members.</small></div>
@endif
</section>
@endsection
